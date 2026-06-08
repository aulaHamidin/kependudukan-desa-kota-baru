<?php

declare(strict_types=1);

namespace App\Services\Event;

use App\Models\Event;
use App\Models\EventDatang;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventVoidService
{
    /**
     * Mapping event type → penduduk status yang dihasilkan event tersebut
     * Digunakan saat infer status sebelum event di-void
     */
    private const EVENT_TO_STATUS_MAP = [
        'DATANG'    => 'AKTIF',
        'KELAHIRAN' => 'AKTIF',
        'KEMATIAN'  => 'MENINGGAL',
        'PINDAH'    => 'PINDAH',
    ];

    public function voidEvent(User $actor, Event $event, string $voidReason): Event
    {
        if ($event->event_type_code === 'KEMATIAN' && $event->kk_id) {
            $this->validateKematianVoid($event);
        }

        if ($event->event_type_code === 'PINDAH' && $event->kk_id) {
            $this->validatePindahVoid($event);
        }

        return DB::transaction(function () use ($actor, $event, $voidReason) {
            $event = Event::lockForUpdate()->findOrFail($event->id);

            $this->validateVoidRules($event);

            $event->update([
                'status_data' => 'VOID',
                'void_reason' => $voidReason,
                'void_at'     => now(),
                'voided_by'   => $actor->id,
            ]);

            match ($event->event_type_code) {
                'KELAHIRAN' => $this->rollbackKelahiran($event),
                'KEMATIAN'  => $this->rollbackKematian($event),
                'PINDAH'    => $this->rollbackPindah($event),
                'DATANG'    => $this->rollbackDatang($event),
                default     => throw new DomainException(
                    "Event type '{$event->event_type_code}' tidak mendukung void."
                ),
            };

            Log::info('Event voided', [
                'event_id'    => $event->id,
                'event_type'  => $event->event_type_code,
                'voided_by'   => $actor->id,
                'void_reason' => $voidReason,
            ]);

            return $event->fresh(['penduduk', 'rt.rw.desa']);
        });
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    private function validateVoidRules(Event $event): void
    {
        if ($event->status_data !== 'VERIFIED') {
            throw new DomainException('Hanya event berstatus VERIFIED yang dapat di-void.');
        }

        $hasNewerVerifiedEvent = Event::where('penduduk_id', $event->penduduk_id)
            ->where('id', '>', $event->id)
            ->where('status_data', 'VERIFIED')
            ->exists();

        if ($hasNewerVerifiedEvent) {
            throw new DomainException(
                'Event tidak dapat di-void karena terdapat event terverifikasi yang lebih baru. ' .
                    'Void event terbaru terlebih dahulu.'
            );
        }

        if ($event->event_type_code === 'KEMATIAN' && $event->kk_id) {
            $this->validateKematianVoid($event);
        }
    }

    private function validateKematianVoid(Event $event): void
    {
        $penggantiMembership = KkMember::where('kartu_keluarga_id', $event->kk_id)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->where('penduduk_id', '!=', $event->penduduk_id)
            ->first();

        if (!$penggantiMembership) {
            return;
        }

        $penggantiHasNewEvent = Event::where('penduduk_id', $penggantiMembership->penduduk_id)
            ->where('id', '>', $event->id)
            ->where('status_data', 'VERIFIED')
            ->exists();

        if ($penggantiHasNewEvent) {
            throw new DomainException(
                'Event kematian tidak dapat di-void karena pengganti kepala keluarga ' .
                    'sudah memiliki event terverifikasi yang lebih baru.'
            );
        }
    }

    private function validatePindahVoid(Event $event): void
    {
        $event->load('eventPindah');

        if (!$event->eventPindah) {
            return;
        }

        $kk = KartuKeluarga::find($event->kk_id);
        if ($kk && $kk->status_kk === 'NON_AKTIF') {
            throw new DomainException(
                'Event pindah tidak dapat di-void karena KK asal sudah tidak aktif. ' .
                    'Diperlukan intervensi manual untuk restore.'
            );
        }

        if ($event->eventPindah->was_kepala) {
            $penggantiMembership = KkMember::where('kartu_keluarga_id', $event->kk_id)
                ->where('is_kepala_keluarga', true)
                ->where('status', 'AKTIF')
                ->where('penduduk_id', '!=', $event->penduduk_id)
                ->first();

            if ($penggantiMembership) {
                $penggantiHasNewEvent = Event::where('penduduk_id', $penggantiMembership->penduduk_id)
                    ->where('id', '>', $event->id)
                    ->where('status_data', 'VERIFIED')
                    ->exists();

                if ($penggantiHasNewEvent) {
                    throw new DomainException(
                        'Event pindah tidak dapat di-void karena pengganti kepala keluarga ' .
                            'sudah memiliki event terverifikasi yang lebih baru. ' .
                            'Rollback akan menyebabkan inkonsistensi kepemimpinan KK.'
                    );
                }
            }
        }
    }

    // =========================================================================
    // PER-EVENT ROLLBACK
    // =========================================================================

    private function rollbackKelahiran(Event $event): void
    {
        // Lock the penduduk record to prevent concurrent modifications
        $penduduk = Penduduk::lockForUpdate()->find($event->penduduk_id);

        // If penduduk not found, log warning and exit early
        if (!$penduduk) {
            Log::warning('Rollback Kelahiran: Penduduk not found', ['event_id' => $event->id]);
            return;
        }

        // Get the KK ID from the event
        $kkId = $event->kk_id;

        // Remove KK membership if the event was associated with a KK
        if ($kkId) {
            $this->removeKkMembership($penduduk, $kkId, $event);
        }

        // Soft delete the penduduk record (birth events create new penduduk)
        $penduduk->delete();

        // Log the successful rollback operation
        Log::info('Rollback Kelahiran: Penduduk soft-deleted', [
            'event_id'    => $event->id,
            'penduduk_id' => $penduduk->id,
            'nik'         => $penduduk->nik,
        ]);

        // Deactivate KK if no active members remain after removing this penduduk
        if ($kkId) {
            $this->deactivateKkIfNoActiveMembers($kkId);
        }
    }

    private function rollbackKematian(Event $event): void
    {
        $penduduk = Penduduk::lockForUpdate()->find($event->penduduk_id);

        if (!$penduduk) {
            Log::warning('Rollback Kematian: Penduduk not found', ['event_id' => $event->id]);
            return;
        }

        // Load detail kematian untuk pakai was_kepala & pengganti_id
        $event->load('eventKematian');
        $detail = $event->eventKematian;

        // 1. Rollback pengganti kepala DULU (sebelum restore almarhum sebagai kepala)
        // M6: Guard null — jika detail null (data korup/migrasi lama), skip rollback
        // pengganti karena inferensi kepala aktif tidak akurat dan bisa unset kepala
        // yang tidak berkaitan.
        if ($event->kk_id && $detail) {
            $this->rollbackPenggantiKepala($event, $detail);
        } elseif ($event->kk_id && !$detail) {
            Log::warning('Rollback Kematian: event_kematian detail not found, skipping rollbackPenggantiKepala', [
                'event_id' => $event->id,
                'kk_id'    => $event->kk_id,
            ]);
        }

        // 2. Revert penduduk status ke sebelum meninggal
        $previousStatus = $this->inferPreviousStatus($penduduk, $event);

        $penduduk->update([
            'status_kependudukan_code' => $previousStatus['status'],
            'current_event_id'         => $previousStatus['event_id'],
            'tanggal_status'           => $previousStatus['tanggal_status'],
        ]);

        // 3. Restore KK membership dengan was_kepala sebagai source of truth
        if ($event->kk_id) {
            $this->restoreKkMembership($penduduk, $event->kk_id, $event, $detail?->was_kepala);
            $this->reactivateKkIfNeeded($event->kk_id);
        }

        Log::info('Rollback Kematian: Penduduk restored', [
            'event_id'        => $event->id,
            'penduduk_id'     => $penduduk->id,
            'previous_status' => $previousStatus['status'],
        ]);
    }

    private function rollbackPindah(Event $event): void
    {
        $penduduk = Penduduk::lockForUpdate()->find($event->penduduk_id);

        if (!$penduduk) {
            Log::warning('Rollback Pindah: Penduduk not found', ['event_id' => $event->id]);
            return;
        }

        $event->load('eventPindah');
        $wasKepala   = $event->eventPindah?->was_kepala ?? false;
        $penggantiId = $event->eventPindah?->pengganti_id;

        // Rollback pengganti kepala secara presisi — gunakan pengganti_id yang
        // disimpan saat create, bukan inferensi dari is_kepala_keluarga aktif.
        if ($event->kk_id && $wasKepala && $penggantiId) {
            $penggantiMembership = KkMember::lockForUpdate()
                ->where('kartu_keluarga_id', $event->kk_id)
                ->where('penduduk_id', $penggantiId)
                ->where('status', 'AKTIF')
                ->first();

            if ($penggantiMembership) {
                $penggantiMembership->update(['is_kepala_keluarga' => false]);

                Log::info('Rollback Pindah: Pengganti kepala reverted', [
                    'event_id'              => $event->id,
                    'pengganti_penduduk_id' => $penggantiId,
                ]);
            }
        }

        $previousStatus = $this->inferPreviousStatus($penduduk, $event);

        $penduduk->update([
            'status_kependudukan_code' => $previousStatus['status'],
            'current_event_id'         => $previousStatus['event_id'],
            'tanggal_status'           => $previousStatus['tanggal_status'],
        ]);

        if ($event->kk_id) {
            $membership = KkMember::where('penduduk_id', $penduduk->id)
                ->where('kartu_keluarga_id', $event->kk_id)
                ->where('event_keluar_id', $event->id)
                ->first();

            if ($membership) {
                $membership->update([
                    'status'             => 'AKTIF',
                    'tanggal_keluar'     => null,
                    'event_keluar_id'    => null,
                    'alasan_keluar'      => null,
                    'is_kepala_keluarga' => $wasKepala,
                ]);
            }

            $this->reactivateKkIfNeeded($event->kk_id);
        }

        Log::info('Rollback Pindah: Penduduk restored', [
            'event_id'        => $event->id,
            'penduduk_id'     => $penduduk->id,
            'was_kepala'      => $wasKepala,
            'previous_status' => $previousStatus['status'],
        ]);
    }

    private function rollbackDatang(Event $event): void
    {
        $penduduk = Penduduk::lockForUpdate()->find($event->penduduk_id);

        if (!$penduduk) {
            Log::warning('Rollback Datang: Penduduk not found', ['event_id' => $event->id]);
            return;
        }

        $eventDatang     = EventDatang::where('event_id', $event->id)->first();
        $jenisKedatangan = $eventDatang?->jenis_kedatangan;
        $kkId            = $event->kk_id;

        if ($kkId) {
            $this->removeKkMembership($penduduk, $kkId, $event);
        }

        if ($jenisKedatangan === 'PENDATANG_BARU') {
            // D6: Cek restored_from_id sebelum soft-delete.
            // Jika penduduk adalah hasil restore (restored_from_id not null),
            // jangan soft-delete — cukup revert ke state sebelumnya.
            if ($eventDatang?->restored_from_id) {
                $previousStatus = $this->inferPreviousStatus($penduduk, $event);
                $penduduk->update([
                    'status_kependudukan_code' => $previousStatus['status'],
                    'current_event_id'         => $previousStatus['event_id'],
                    'tanggal_status'           => $previousStatus['tanggal_status'],
                ]);

                Log::info('Rollback Datang: Restored penduduk reverted (not soft-deleted)', [
                    'event_id'         => $event->id,
                    'penduduk_id'      => $penduduk->id,
                    'restored_from_id' => $eventDatang->restored_from_id,
                ]);
            } else {
                $penduduk->delete();

                Log::info('Rollback Datang: Penduduk soft-deleted', [
                    'event_id'    => $event->id,
                    'penduduk_id' => $penduduk->id,
                ]);
            }
        } else {
            $previousStatus = $this->inferPreviousStatus($penduduk, $event);

            if ($previousStatus['event_id'] === null) {
                $penduduk->delete();

                Log::info('Rollback Datang (Kembali - First Event): Penduduk soft-deleted', [
                    'event_id'    => $event->id,
                    'penduduk_id' => $penduduk->id,
                ]);
            } else {
                $penduduk->update([
                    'status_kependudukan_code' => $previousStatus['status'],
                    'current_event_id'         => $previousStatus['event_id'],
                    'tanggal_status'           => $previousStatus['tanggal_status'],
                ]);

                Log::info('Rollback Datang (Kembali): Status reverted', [
                    'event_id'        => $event->id,
                    'penduduk_id'     => $penduduk->id,
                    'previous_status' => $previousStatus['status'],
                ]);
            }
        }

        if ($kkId) {
            $this->deactivateKkIfNoActiveMembers($kkId);
        }
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Infer status penduduk sebelum event terjadi.
     *
     * FIX: tanggal_status tidak boleh null (kolom NOT NULL di DB).
     * Jika tidak ada event sebelumnya, fallback ke event_date event yang sedang
     * di-void — itu tanggal paling logis karena saat itulah status terakhir berubah.
     *
     * @return array{status: string, event_id: int|null, tanggal_status: string}
     */
    private function inferPreviousStatus(Penduduk $penduduk, Event $currentEvent): array
    {
        $previousEvent = Event::where('penduduk_id', $penduduk->id)
            ->where('id', '<', $currentEvent->id)
            ->where('status_data', 'VERIFIED')
            ->orderBy('id', 'desc')
            ->first();

        if (!$previousEvent) {
            // Tidak ada event sebelumnya → status awal AKTIF.
            // tanggal_status fallback ke event_date current event (NOT NULL constraint).
            return [
                'status'         => 'AKTIF',
                'event_id'       => null,
                'tanggal_status' => $currentEvent->event_date->toDateString(),
            ];
        }

        $status = self::EVENT_TO_STATUS_MAP[$previousEvent->event_type_code] ?? 'AKTIF';

        return [
            'status'         => $status,
            'event_id'       => $previousEvent->id,
            'tanggal_status' => $previousEvent->event_date->toDateString(),
        ];
    }

    /**
     * Rollback pengganti kepala keluarga saat void kematian.
     *
     * FIX: Sekarang menggunakan pengganti_id dari event_kematian (presisi),
     * bukan inferensi dari kepala aktif di KK (bisa salah jika ada perubahan lain).
     */
    private function rollbackPenggantiKepala(Event $event, ?object $detail): void
    {
        // Prioritas: pakai pengganti_id yang tersimpan di event_kematian (presisi)
        if ($detail?->pengganti_id) {
            $penggantiMembership = KkMember::lockForUpdate()
                ->where('kartu_keluarga_id', $event->kk_id)
                ->where('penduduk_id', $detail->pengganti_id)
                ->where('status', 'AKTIF')
                ->first();

            if ($penggantiMembership) {
                $penggantiMembership->update(['is_kepala_keluarga' => false]);

                Log::info('Rollback PenggantiKepala (presisi): Reverted', [
                    'event_id'              => $event->id,
                    'kk_id'                 => $event->kk_id,
                    'pengganti_penduduk_id' => $detail->pengganti_id,
                ]);
            }

            return;
        }

        // Fallback: jika pengganti_id tidak ada (event lama sebelum migration),
        // gunakan inferensi kepala aktif selain almarhum
        $penggantiMembership = KkMember::lockForUpdate()
            ->where('kartu_keluarga_id', $event->kk_id)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->where('penduduk_id', '!=', $event->penduduk_id)
            ->first();

        if (!$penggantiMembership) {
            return;
        }

        $penggantiMembership->update(['is_kepala_keluarga' => false]);

        Log::info('Rollback PenggantiKepala (inferensi): Reverted', [
            'event_id'              => $event->id,
            'kk_id'                 => $event->kk_id,
            'pengganti_penduduk_id' => $penggantiMembership->penduduk_id,
        ]);
    }

    private function removeKkMembership(Penduduk $penduduk, int $kkId, Event $event): void
    {
        $membership = KkMember::where('penduduk_id', $penduduk->id)
            ->where('kartu_keluarga_id', $kkId)
            ->where('status', 'AKTIF')
            ->first();

        // K1: Log warning jika membership tidak ditemukan agar mudah dideteksi
        if (!$membership) {
            Log::warning('removeKkMembership: membership aktif tidak ditemukan', [
                'penduduk_id' => $penduduk->id,
                'kk_id'       => $kkId,
                'event_id'    => $event->id,
            ]);
            return;
        }

        $membership->update([
            'status'              => 'KELUAR',
            'tanggal_keluar'      => now()->toDateString(),
            'event_keluar_id'     => $event->id,
            'alasan_keluar'       => 'Void event: ' . $event->event_type_code,
            'is_kepala_keluarga'  => false, // CE9: Reset is_kepala saat keluar
        ]);
    }

    /**
     * Restore KK membership yang di-close oleh event ini.
     *
     * FIX: Terima parameter $wasKepala eksplisit dari event_kematian.was_kepala
     * agar restore is_kepala_keluarga presisi — bukan inferensi dari kondisi KK saat ini.
     */
    private function restoreKkMembership(Penduduk $penduduk, int $kkId, Event $event, ?bool $wasKepala = null): void
    {
        $membership = KkMember::where('penduduk_id', $penduduk->id)
            ->where('kartu_keluarga_id', $kkId)
            ->where('event_keluar_id', $event->id)
            ->first();

        if (!$membership) {
            Log::warning('restoreKkMembership: Membership not found', [
                'penduduk_id' => $penduduk->id,
                'kk_id'       => $kkId,
                'event_id'    => $event->id,
            ]);
            return;
        }

        // Jika wasKepala diberikan eksplisit (dari event_kematian.was_kepala), pakai itu.
        // Fallback: inferensi dari kondisi KK — jika tidak ada kepala lain, dia kepala.
        $shouldBeKepala = $wasKepala ?? !KkMember::where('kartu_keluarga_id', $kkId)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->where('penduduk_id', '!=', $penduduk->id)
            ->exists();

        $membership->update([
            'status'             => 'AKTIF',
            'tanggal_keluar'     => null,
            'event_keluar_id'    => null,
            'alasan_keluar'      => null,
            'is_kepala_keluarga' => $shouldBeKepala,
        ]);

        Log::info('restoreKkMembership: Membership restored', [
            'penduduk_id' => $penduduk->id,
            'kk_id'       => $kkId,
            'event_id'    => $event->id,
            'is_kepala'   => $shouldBeKepala,
        ]);
    }

    private function reactivateKkIfNeeded(int $kkId): void
    {
        $activeMembers = KkMember::where('kartu_keluarga_id', $kkId)
            ->where('status', 'AKTIF')
            ->count();

        if ($activeMembers > 0) {
            KartuKeluarga::where('id', $kkId)
                ->where('status_kk', 'NON_AKTIF')
                ->update(['status_kk' => 'AKTIF']);
        }
    }

    private function deactivateKkIfNoActiveMembers(int $kkId): void
    {
        // CE8: Lock KK row sebelum check member — cegah race condition
        // di mana dua request concurrent sama-sama membaca "ada member"
        // sebelum salah satu selesai delete.
        $kk = KartuKeluarga::lockForUpdate()->find($kkId);

        if (!$kk) return;

        $hasRemainingActiveMembers = KkMember::where('kartu_keluarga_id', $kkId)
            ->where('status', 'AKTIF')
            ->exists();

        if (!$hasRemainingActiveMembers) {
            $kk->update(['status_kk' => 'NON_AKTIF']);

            Log::info('Void Event: KK deactivated (no active members)', ['kk_id' => $kkId]);
        }
    }
}