<?php

declare(strict_types=1);

namespace App\Actions\Event;

use App\DTOs\Event\PindahDTO;
use App\Models\Event;
use App\Models\EventPindah;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Repositories\KkMemberRepository;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateEventPindahAction
{
    public function __construct(
        private KkMemberRepository $kkMemberRepo
    ) {}

    /**
     * Execute event pindah creation
     *
     * Flow:
     * 1. Resolve territory (rw_id, desa_id) dari RT
     * 2. Create Event parent
     * 3. Create EventPindah detail
     * 4. Update penduduk status → PINDAH
     * 5. Close KK membership
     * 6. Set pengganti kepala jika applicable (dengan locking)
     */
    public function execute(PindahDTO $dto): Event
    {
        return DB::transaction(function () use ($dto) {
            // Resolve territory dari RT (same pattern as Kelahiran & Datang)
            $rt = Rt::with('rw')->findOrFail($dto->rtId);

            // Lock penduduk yang pindah
            $penduduk = Penduduk::lockForUpdate()->findOrFail($dto->pendudukId);

            // Validate: penduduk harus AKTIF
            if ($penduduk->status_kependudukan_code !== 'AKTIF') {
                throw new DomainException(
                    'Penduduk tidak dapat pindah karena status bukan AKTIF.'
                );
            }

            // Validate: tidak ada active event lain
            $hasActiveEvent = Event::where('penduduk_id', $penduduk->id)
                ->where('status_data', 'DRAFT')
                ->exists();

            if ($hasActiveEvent) {
                throw new DomainException(
                    'Penduduk masih memiliki event DRAFT yang belum diverifikasi.'
                );
            }

            // 1. Create Event parent
            $event = Event::create([
                'event_type_code' => 'PINDAH',
                'penduduk_id'     => $penduduk->id,
                'event_date'      => $dto->eventDate->format('Y-m-d'),
                'keterangan'      => $dto->keterangan,
                'rt_id'           => $dto->rtId,
                'rw_id'           => $rt->rw_id,
                'desa_id'         => $rt->rw->desa_id,
                'kk_id'           => $dto->kkId,
                'status_data'     => 'DRAFT',
                'created_by'      => $dto->createdBy,
            ]);

            // 2. Check if penduduk adalah kepala keluarga untuk set was_kepala di EventPindah
            $wasKepala = false;
            if ($dto->kkId) {
                $membership = KkMember::where('penduduk_id', $penduduk->id)
                    ->where('kartu_keluarga_id', $dto->kkId)
                    ->where('status', 'AKTIF')
                    ->first();

                $wasKepala = $membership?->is_kepala_keluarga ?? false;
            }

            // 3. Create EventPindah detail — simpan was_kepala & pengganti_id
            //    agar delete DRAFT dan void bisa rollback dengan presisi
            EventPindah::create([
                'event_id'          => $event->id,
                'alamat_tujuan'     => $dto->alamatTujuan,
                'rt_tujuan'         => $dto->rtTujuan,
                'rw_tujuan'         => $dto->rwTujuan,
                'desa_tujuan'       => $dto->desaTujuan,
                'kecamatan_tujuan'  => $dto->kecamatanTujuan,
                'kabupaten_tujuan'  => $dto->kabupatenTujuan,
                'provinsi_tujuan'   => $dto->provinsiTujuan,
                'kode_pos_tujuan'   => $dto->kodePosTujuan,
                'alasan_pindah'     => $dto->alasanPindah,
                'keterangan_alasan' => $dto->keteranganAlasan,
                'jenis_kepindahan'  => $dto->jenisKepindahan,
                'tanggal_pindah'    => $dto->eventDate->format('Y-m-d'),
                'was_kepala'        => $wasKepala,
                'pengganti_id'      => $dto->penggantiKepalaId,
            ]);

            // 4. Update penduduk status → PINDAH
            $penduduk->update([
                'status_kependudukan_code' => 'PINDAH',
                'current_event_id'         => $event->id,
                'tanggal_status'           => $dto->eventDate->format('Y-m-d'),
                'updated_by'               => $dto->createdBy,
            ]);

            // 5. Close KK membership
            if ($dto->kkId) {
                $this->closeKkMembership($penduduk, $dto, $event);

                // ← BELUM ADA ini, perlu ditambahkan
                $hasRemaining = KkMember::where('kartu_keluarga_id', $dto->kkId)
                    ->where('status', 'AKTIF')
                    ->exists();

                if (!$hasRemaining) {
                    \App\Models\KartuKeluarga::where('id', $dto->kkId)
                        ->update(['status_kk' => 'NON_AKTIF']);
                }
                
                // 6. Set pengganti kepala jika penduduk adalah kepala keluarga
                if ($dto->penggantiKepalaId) {
                    $this->setPenggantiKepala($dto, $event);
                }
            }

            Log::info('Event Pindah created', [
                'event_id'    => $event->id,
                'penduduk_id' => $penduduk->id,
                'kk_id'       => $dto->kkId,
            ]);

            return $event->fresh(['eventPindah', 'penduduk', 'rt.rw.desa', 'kartuKeluarga']);
        });
    }

    /**
     * Close KK membership untuk penduduk yang pindah
     */
    private function closeKkMembership(Penduduk $penduduk, PindahDTO $dto, Event $event): void
    {
        $membership = KkMember::where('penduduk_id', $penduduk->id)
            ->where('kartu_keluarga_id', $dto->kkId)
            ->where('status', 'AKTIF')
            ->lockForUpdate()
            ->first();

        if (!$membership) {
            Log::warning('KK membership not found for pindah', [
                'penduduk_id' => $penduduk->id,
                'kk_id'       => $dto->kkId,
            ]);
            return;
        }

        $membership->update([
            'status'             => 'KELUAR',
            'tanggal_keluar'     => $dto->eventDate->format('Y-m-d'),
            'event_keluar_id'    => $event->id,
            'alasan_keluar'      => 'Pindah: ' . ($dto->alasanPindah ?? 'LAINNYA'),
            'is_kepala_keluarga' => false,
        ]);
    }

    /**
     * Set pengganti kepala keluarga dengan pessimistic locking
     *
     * CRITICAL: Lock KK dan member baru untuk prevent race condition
     */
    private function setPenggantiKepala(PindahDTO $dto, Event $event): void
    {
        // Lock KK untuk prevent concurrent kepala assignment
        $hasKepala = $this->kkMemberRepo->hasKepalaKeluargaWithLock($dto->kkId);

        if ($hasKepala) {
            // Masih ada kepala lain (race condition) - skip
            Log::warning('KK already has kepala during pengganti assignment', [
                'kk_id'             => $dto->kkId,
                'pengganti_id'      => $dto->penggantiKepalaId,
            ]);
            return;
        }

        // Lock pengganti member
        $penggantiMembership = KkMember::where('penduduk_id', $dto->penggantiKepalaId)
            ->where('kartu_keluarga_id', $dto->kkId)
            ->where('status', 'AKTIF')
            ->lockForUpdate()
            ->first();

        if (!$penggantiMembership) {
            throw new DomainException(
                'Pengganti kepala keluarga tidak ditemukan sebagai anggota aktif KK.'
            );
        }

        $penggantiMembership->update([
            'is_kepala_keluarga' => true,
        ]);

        Log::info('Pengganti kepala set', [
            'kk_id'          => $dto->kkId,
            'pengganti_id'   => $dto->penggantiKepalaId,
            'event_id'       => $event->id,
        ]);
    }
}