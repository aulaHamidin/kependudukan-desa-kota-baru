<?php

declare(strict_types=1);

namespace App\Services\Event;

use App\Actions\Event\CreateEventPindahAction;
use App\DTOs\Event\PindahDTO;
use App\Models\Event;
use App\Models\KkMember;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PindahService
{
    public function __construct(
        private CreateEventPindahAction $createAction,
        private EventVoidService $voidService
    ) {}

    /**
     * Create event pindah
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function createEventPindah(User $actor, array $payload): Event
    {
        $this->authorizeCreate($actor, $payload['rt_id'] ?? null);

        // P7: Check pendudukHasPendingEvent TIDAK dilakukan di sini.
        // Check dilakukan di dalam transaction setelah lockForUpdate pada penduduk
        // di CreateEventPindahAction::execute — itu satu-satunya check yang
        // benar-benar atomic dan aman dari race condition.

        $dto = PindahDTO::fromRequest(array_merge($payload, [
            'created_by' => $actor->id,
        ]));

        return $this->createAction->execute($dto);
    }

    /**
     * Update event pindah - hanya DRAFT
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function updateEventPindah(User $actor, Event $event, array $payload): Event
    {
        if (!$actor->can('update', $event)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk mengubah event ini.');
        }

        if ($event->status_data !== 'DRAFT') {
            throw new DomainException('Hanya event berstatus DRAFT yang dapat diubah.');
        }

        return DB::transaction(function () use ($event, $payload) {
            $event = Event::lockForUpdate()->findOrFail($event->id);

            $event->update([
                'event_date'  => $payload['event_date'] ?? $event->event_date,
                'keterangan'  => $payload['keterangan'] ?? $event->keterangan,
            ]);

            if ($event->eventPindah) {
                // P5+CE4: Gunakan array_key_exists untuk nullable fields
                // agar bisa di-clear (kirim null). Operator ?? tidak bisa membedakan
                // antara "key tidak dikirim" vs "sengaja null".
                $detail  = $event->eventPindah;
                $resolve = fn(string $key, mixed $fallback) =>
                    array_key_exists($key, $payload) ? $payload[$key] : $fallback;

                $detail->update([
                    'alamat_tujuan'     => $payload['alamat_tujuan'] ?? $detail->alamat_tujuan, // required
                    'rt_tujuan'         => $resolve('rt_tujuan',         $detail->rt_tujuan),
                    'rw_tujuan'         => $resolve('rw_tujuan',         $detail->rw_tujuan),
                    'desa_tujuan'       => $resolve('desa_tujuan',       $detail->desa_tujuan),
                    'kecamatan_tujuan'  => $resolve('kecamatan_tujuan',  $detail->kecamatan_tujuan),
                    'kabupaten_tujuan'  => $resolve('kabupaten_tujuan',  $detail->kabupaten_tujuan),
                    'provinsi_tujuan'   => $resolve('provinsi_tujuan',   $detail->provinsi_tujuan),
                    'kode_pos_tujuan'   => $resolve('kode_pos_tujuan',   $detail->kode_pos_tujuan),
                    'alasan_pindah'     => $resolve('alasan_pindah',     $detail->alasan_pindah),
                    'keterangan_alasan' => $resolve('keterangan_alasan', $detail->keterangan_alasan),
                ]);
            }

            return $event->fresh(['eventPindah', 'penduduk', 'rt.rw.desa']);
        });
    }

    /**
     * Delete event pindah - hanya DRAFT
     * Rollback: revert penduduk status, restore KK membership
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function deleteEventPindah(User $actor, Event $event): bool
    {
        if (!$actor->can('delete', $event)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk menghapus event ini.');
        }

        if ($event->status_data !== 'DRAFT') {
            throw new DomainException(
                'Hanya event berstatus DRAFT yang dapat dihapus. ' .
                    'Gunakan fitur void untuk event yang sudah diverifikasi.'
            );
        }

        return DB::transaction(function () use ($event) {
            $event = Event::lockForUpdate()->findOrFail($event->id);

            $penduduk = $event->penduduk;
            $detail   = $event->eventPindah;
            $wasKepala = $detail?->was_kepala ?? false;

            if ($penduduk) {
                // P4: Restore tanggal_status ke nilai sebelum pindah.
                // Ambil dari membership yang ditutup event ini, atau fallback ke event_date.
                $membership = KkMember::where('penduduk_id', $penduduk->id)
                    ->where('event_keluar_id', $event->id)
                    ->first();

                $previousDate = $membership?->tanggal_keluar
                    ?? $event->event_date;

                // Revert penduduk status ke AKTIF
                $penduduk->update([
                    'status_kependudukan_code' => 'AKTIF',
                    'current_event_id'         => null,
                    'tanggal_status'           => $previousDate,
                ]);

                // P3: Restore KK membership yang ditutup oleh event ini
                // — termasuk is_kepala_keluarga dari snapshot was_kepala
                KkMember::where('penduduk_id', $penduduk->id)
                    ->where('event_keluar_id', $event->id)
                    ->update([
                        'status'             => 'AKTIF',
                        'tanggal_keluar'     => null,
                        'event_keluar_id'    => null,
                        'alasan_keluar'      => null,
                        'is_kepala_keluarga' => $wasKepala,
                    ]);

                // Restore status KK jika NON_AKTIF karena tidak ada anggota
                if ($event->kk_id) {
                    $hasActiveMembers = KkMember::where('kartu_keluarga_id', $event->kk_id)
                        ->where('status', 'AKTIF')
                        ->exists();

                    if ($hasActiveMembers) {
                        \App\Models\KartuKeluarga::where('id', $event->kk_id)
                            ->where('status_kk', 'NON_AKTIF')
                            ->update(['status_kk' => 'AKTIF']);
                    }

                    // Rollback pengganti kepala jika ada
                    $this->rollbackPenggantiKepalaOnDelete($event, $penduduk->id);
                }
            }

            $event->eventPindah?->delete();

            return (bool) $event->delete();
        });
    }

    /**
     * Void event pindah - hanya VERIFIED
     * Delegate ke shared EventVoidService
     */
    public function voidEvent(User $actor, Event $event, string $voidReason): Event
    {
        return $this->voidService->voidEvent($actor, $event, $voidReason);
    }

    /**
     * Get paginated event pindah dengan filters
     * Territory scope auto-applied via HasTerritory global scope
     */
    public function paginateWithFilters(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Event::where('event_type_code', 'PINDAH')
            ->with(['penduduk', 'rt.rw.desa', 'eventPindah', 'createdBy', 'verifiedBy']);

        if (!empty($filters['status_data'])) {
            $query->where('status_data', $filters['status_data']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('event_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('event_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['rt_id'])) {
            $query->where('rt_id', $filters['rt_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereHas('penduduk', function ($pendudukQuery) use ($search) {
                    $pendudukQuery->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            });
        }

        return $query
            ->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get statistik event pindah (territory-aware)
     *
     * @return array{total: int, draft: int, verified: int, void: int, pending_approval: int}
     */
    public function getStats(User $user): array
    {
        $query = Event::where('event_type_code', 'PINDAH');

        return [
            'total'            => $query->count(),
            'draft'            => (clone $query)->where('status_data', 'DRAFT')->count(),
            'verified'         => (clone $query)->where('status_data', 'VERIFIED')->count(),
            'void'             => (clone $query)->where('status_data', 'VOID')->count(),
            'pending_approval' => (clone $query)
                ->where('status_data', 'DRAFT')
                ->whereNull('verified_at')
                ->count(),
        ];
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * @throws AuthorizationException
     * @throws DomainException
     */
    private function authorizeCreate(User $actor, ?int $rtId): void
    {
        if (!$rtId) {
            throw new DomainException('RT wajib dipilih.');
        }

        if (!$actor->can('createInRt', [Event::class, $rtId])) {
            throw new AuthorizationException(
                'Anda tidak memiliki izin untuk membuat event di RT ini.'
            );
        }
    }

    /**
     * Rollback pengganti kepala saat delete DRAFT event pindah
     * Kembalikan kepala lama, hapus kepala baru (pengganti)
     */
    private function rollbackPenggantiKepalaOnDelete(Event $event, int $pendudukId): void
    {
        $penggantiId = $event->eventPindah?->pengganti_id;

        // Rollback pengganti kepala secara presisi menggunakan pengganti_id yang
        // disimpan saat create — bukan inferensi dari is_kepala_keluarga aktif.
        if ($penggantiId) {
            $penggantiMembership = KkMember::where('kartu_keluarga_id', $event->kk_id)
                ->where('penduduk_id', $penggantiId)
                ->where('status', 'AKTIF')
                ->lockForUpdate()
                ->first();

            if ($penggantiMembership) {
                $penggantiMembership->update(['is_kepala_keluarga' => false]);
            }
        }

        // Restore kepala lama (penduduk yang pindah, sekarang status sudah AKTIF kembali)
        KkMember::where('kartu_keluarga_id', $event->kk_id)
            ->where('penduduk_id', $pendudukId)
            ->where('status', 'AKTIF')
            ->update(['is_kepala_keluarga' => true]);
    }
}
