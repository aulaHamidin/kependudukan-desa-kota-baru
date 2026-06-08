<?php

declare(strict_types=1);

namespace App\Services\Event;

use App\Actions\Event\CreateEventKelahiranAction;
use App\DTOs\Event\KelahiranDTO;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelahiranService
{
    public function __construct(
        private CreateEventKelahiranAction $createAction,
        private EventVoidService $voidService
    ) {}

    /**
     * Create event kelahiran
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function createEventKelahiran(User $actor, array $payload): Event
    {
        $this->authorizeCreate($actor, $payload['rt_id'] ?? null);

        // REMOVED: Check penduduk has pending event
        // Tidak relevan untuk kelahiran karena bayi = penduduk BARU (belum ada di DB)

        $dto = KelahiranDTO::fromRequest(array_merge($payload, [
            'created_by' => $actor->id,
        ]));

        return $this->createAction->execute($dto);
    }

    /**
     * Update event kelahiran - hanya DRAFT yang bisa diubah
     *
     * UPDATED:
     * - RT tidak boleh diubah (locked)
     * - KK boleh diubah (dengan sync membership)
     * - Tambah sync agama_id
     * - Tambah sync status_kelahiran
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function updateEventKelahiran(User $actor, Event $event, array $payload): Event
    {
        if (!$actor->can('update', $event)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk mengubah event ini.');
        }

        if ($event->status_data !== 'DRAFT') {
            throw new DomainException(
                'Hanya event berstatus DRAFT yang dapat diubah.'
            );
        }

        // Audit trail: AuditLogObserver uses auth()->user() when logging updates
        return DB::transaction(function () use ($event, $payload) {
            $event = Event::lockForUpdate()->findOrFail($event->id);

            // Track KK change untuk membership update
            $oldKkId = $event->eventKelahiran->kk_tujuan_id;
            $newKkId = $payload['kk_tujuan_id'] ?? $oldKkId;
            $kkChanged = $oldKkId != $newKkId;

            // Update event parent (RT TIDAK BOLEH DIUBAH)
            $event->update([
                'event_date' => array_key_exists('event_date', $payload) ? $payload['event_date'] : $event->event_date,
                'keterangan' => array_key_exists('keterangan', $payload) ? $payload['keterangan'] : $event->keterangan,
                // rt_id TIDAK diupdate (locked)
                'kk_id' => $newKkId, // Update kk_id di event parent juga
            ]);

            // Update event kelahiran detail
            if ($event->eventKelahiran) {
                // CRITICAL: Gunakan array_key_exists() bukan ?? untuk field nullable.
                // `null ?? oldValue` mengembalikan oldValue, mencegah field di-clear ke null.
                // Contoh: switch dari penduduk→manual harus bisa set ayah_id = null.
                $ek = $event->eventKelahiran;
                $event->eventKelahiran->update([
                    'nama_bayi'          => array_key_exists('nama_bayi', $payload) ? $payload['nama_bayi'] : $ek->nama_bayi,
                    'jenis_kelamin'      => array_key_exists('jenis_kelamin', $payload) ? $payload['jenis_kelamin'] : $ek->jenis_kelamin,
                    'status_kelahiran'   => array_key_exists('status_kelahiran', $payload) ? $payload['status_kelahiran'] : $ek->status_kelahiran,
                    'ayah_id'            => array_key_exists('ayah_id', $payload) ? $payload['ayah_id'] : $ek->ayah_id,
                    'ibu_id'             => array_key_exists('ibu_id', $payload) ? $payload['ibu_id'] : $ek->ibu_id,
                    'nama_ayah'          => array_key_exists('nama_ayah', $payload) ? $payload['nama_ayah'] : $ek->nama_ayah,
                    'nama_ibu'           => array_key_exists('nama_ibu', $payload) ? $payload['nama_ibu'] : $ek->nama_ibu,
                    'tempat_lahir'       => array_key_exists('tempat_lahir', $payload) ? $payload['tempat_lahir'] : $ek->tempat_lahir,
                    'jam_lahir'          => array_key_exists('jam_lahir', $payload) ? $payload['jam_lahir'] : $ek->jam_lahir,
                    'anak_ke'            => array_key_exists('anak_ke', $payload) ? $payload['anak_ke'] : $ek->anak_ke,
                    'berat_badan_kg'     => array_key_exists('berat_badan_kg', $payload) ? $payload['berat_badan_kg'] : $ek->berat_badan_kg,
                    'panjang_badan_cm'   => array_key_exists('panjang_badan_cm', $payload) ? $payload['panjang_badan_cm'] : $ek->panjang_badan_cm,
                    'penolong_kelahiran' => array_key_exists('penolong_kelahiran', $payload) ? $payload['penolong_kelahiran'] : $ek->penolong_kelahiran,
                    'nama_penolong'      => array_key_exists('nama_penolong', $payload) ? $payload['nama_penolong'] : $ek->nama_penolong,
                    'kk_tujuan_id'       => $newKkId,
                ]);
            }

            // Handle KK membership change (jika KK berubah)
            if ($kkChanged && $event->penduduk) {
                $this->updateKkMembership($event->penduduk, $oldKkId, $newKkId, $event->event_date);
            }

            // Sync data bayi (penduduk) dengan perubahan
            if ($event->penduduk) {
                $p = $event->penduduk;
                $event->penduduk->update([
                    'nama_lengkap'  => array_key_exists('nama_bayi', $payload) ? $payload['nama_bayi'] : $p->nama_lengkap,
                    'jenis_kelamin' => array_key_exists('jenis_kelamin', $payload) ? $payload['jenis_kelamin'] : $p->jenis_kelamin,
                    'tempat_lahir'  => array_key_exists('tempat_lahir', $payload) ? $payload['tempat_lahir'] : $p->tempat_lahir,
                    'tgl_lahir'     => array_key_exists('event_date', $payload) ? $payload['event_date'] : $p->tgl_lahir,
                    'ayah_id'       => array_key_exists('ayah_id', $payload) ? $payload['ayah_id'] : $p->ayah_id,
                    'ibu_id'        => array_key_exists('ibu_id', $payload) ? $payload['ibu_id'] : $p->ibu_id,
                    'agama_id'      => array_key_exists('agama_id', $payload) ? $payload['agama_id'] : $p->agama_id,
                    // Jika ayah_id diisi, nama_ayah harus null (ambil dari DB relasi)
                    'nama_ayah'     => !empty($payload['ayah_id'])
                        ? null
                        : (array_key_exists('nama_ayah', $payload) ? $payload['nama_ayah'] : $p->nama_ayah),
                    'nama_ibu'      => !empty($payload['ibu_id'])
                        ? null
                        : (array_key_exists('nama_ibu', $payload) ? $payload['nama_ibu'] : $p->nama_ibu),
                    // Update status kependudukan berdasarkan status_kelahiran (cukup status, tanpa tanggal_meninggal)
                    'status_kependudukan_code' => isset($payload['status_kelahiran'])
                        ? ($payload['status_kelahiran'] === 'HIDUP' ? 'AKTIF' : 'MENINGGAL')
                        : $p->status_kependudukan_code,
                ]);

                // K5: Jika status_kelahiran berubah ke MATI, tutup KK membership bayi
                if (
                    isset($payload['status_kelahiran'])
                    && $payload['status_kelahiran'] === 'MATI'
                    && $event->eventKelahiran->status_kelahiran->value === 'HIDUP'
                ) {
                    $babyMembership = KkMember::where('penduduk_id', $event->penduduk->id)
                        ->where('status', 'AKTIF')
                        ->whereNull('tanggal_keluar')
                        ->first();

                    if ($babyMembership) {
                        $babyMembership->update([
                            'status'             => 'KELUAR',
                            'tanggal_keluar'     => $event->event_date,
                            'event_keluar_id'    => $event->id,
                            'alasan_keluar'      => 'Lahir mati (stillbirth)',
                            'is_kepala_keluarga' => false,
                        ]);

                        // Deactivate KK jika tidak ada member aktif tersisa
                        $kkIdToCheck = $babyMembership->kartu_keluarga_id;
                        $hasActiveMembers = KkMember::where('kartu_keluarga_id', $kkIdToCheck)
                            ->where('status', 'AKTIF')
                            ->exists();

                        if (!$hasActiveMembers) {
                            KartuKeluarga::where('id', $kkIdToCheck)
                                ->update(['status_kk' => 'NON_AKTIF']);
                        }
                    }
                }

                // K5: Jika status_kelahiran berubah dari MATI ke HIDUP, restore membership
                if (
                    isset($payload['status_kelahiran'])
                    && $payload['status_kelahiran'] === 'HIDUP'
                    && $event->eventKelahiran->status_kelahiran->value === 'MATI'
                ) {
                    $closedMembership = KkMember::where('penduduk_id', $event->penduduk->id)
                        ->where('event_keluar_id', $event->id)
                        ->first();

                    if ($closedMembership) {
                        $closedMembership->update([
                            'status'          => 'AKTIF',
                            'tanggal_keluar'  => null,
                            'event_keluar_id' => null,
                            'alasan_keluar'   => null,
                        ]);

                        // Reactivate KK jika sebelumnya NON_AKTIF
                        KartuKeluarga::where('id', $closedMembership->kartu_keluarga_id)
                            ->where('status_kk', 'NON_AKTIF')
                            ->update(['status_kk' => 'AKTIF']);
                    }
                }
            }

            return $event->fresh(['eventKelahiran', 'penduduk', 'rt.rw.desa']);
        });
    }

    /**
     * Update KK membership saat KK berubah pada edit
     * 
     * Logic:
     * 1. Remove dari KK lama
     * 2. Add ke KK baru
     * 3. Validasi KK baru harus di RT yang sama (sudah dihandle di Request)
     * 
     * @param \App\Models\Penduduk $penduduk
     * @param int|null $oldKkId
     * @param int|null $newKkId
     * @param mixed $eventDate
     * @return void
     */
    private function updateKkMembership($penduduk, $oldKkId, $newKkId, $eventDate): void
    {
        $eventDateString = is_string($eventDate) ? $eventDate : $eventDate->format('Y-m-d');

        // 1. Remove dari KK lama
        if ($oldKkId) {
            $updated = KkMember::where('kartu_keluarga_id', $oldKkId)
                ->where('penduduk_id', $penduduk->id)
                ->where('status', 'AKTIF')
                ->update([
                    'status'         => 'KELUAR',
                    'tanggal_keluar' => $eventDateString,
                    'alasan_keluar'  => 'Koreksi data kelahiran - KK tujuan diubah.',
                ]);

            if ($updated > 0) {
                Log::info('Update Event Kelahiran: Bayi removed from old KK', [
                    'penduduk_id' => $penduduk->id,
                    'old_kk_id'   => $oldKkId,
                    'new_kk_id'   => $newKkId,
                ]);
            }
        }

        // 2. Add ke KK baru
        if ($newKkId) {
            // Check if already member (defensive)
            $existingMember = KkMember::where('kartu_keluarga_id', $newKkId)
                ->where('penduduk_id', $penduduk->id)
                ->where('status', 'AKTIF')
                ->exists();

            if (!$existingMember) {
                KkMember::create([
                    'kartu_keluarga_id'      => $newKkId,
                    'penduduk_id'            => $penduduk->id,
                    'hubungan_keluarga_code' => 'ANAK', // Bayi selalu ANAK
                    'is_kepala_keluarga'     => false,
                    'tanggal_masuk'          => $eventDateString,
                    'status'                 => 'AKTIF',
                    'kk_asal_id'             => $oldKkId,
                    'created_by'             => auth()->id(),
                ]);

                Log::info('Update Event Kelahiran: Bayi added to new KK', [
                    'penduduk_id' => $penduduk->id,
                    'new_kk_id'   => $newKkId,
                    'old_kk_id'   => $oldKkId,
                ]);
            } else {
                Log::warning('Update Event Kelahiran: Already member of new KK', [
                    'penduduk_id' => $penduduk->id,
                    'new_kk_id'   => $newKkId,
                ]);
            }
        }
    }

    /**
     * Delete event kelahiran - hanya DRAFT
     *
     * SEMANTIK: destroy = hapus DRAFT yang belum verified
     * Berbeda dengan void = batalkan VERIFIED
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function deleteEventKelahiran(User $actor, Event $event): bool
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

            // Hapus KK membership bayi
            if ($event->penduduk) {
                $kkMembership = $event->penduduk->kkMembers()
                    ->where('status', 'AKTIF')
                    ->first();

                $kkIdToCheck = $kkMembership?->kartu_keluarga_id;

                $event->penduduk->kkMembers()
                    ->where('status', 'AKTIF')
                    ->update([
                        'status'             => 'KELUAR',
                        'tanggal_keluar'     => now()->toDateString(),
                        'alasan_keluar'      => 'Event kelahiran DRAFT dihapus.',
                        'is_kepala_keluarga' => false,
                    ]);

                // Soft delete bayi
                $event->penduduk->delete();

                // K7: Deactivate KK jika tidak ada member aktif setelah bayi dihapus
                if ($kkIdToCheck) {
                    $hasActiveMembers = KkMember::where('kartu_keluarga_id', $kkIdToCheck)
                        ->where('status', 'AKTIF')
                        ->exists();

                    if (!$hasActiveMembers) {
                        KartuKeluarga::where('id', $kkIdToCheck)
                            ->update(['status_kk' => 'NON_AKTIF']);
                    }
                }
            }

            // Hapus event detail
            $event->eventKelahiran?->delete();

            return (bool) $event->delete();
        });
    }

    /**
     * Void event kelahiran - hanya VERIFIED
     * Delegate ke shared EventVoidService
     */
    public function voidEvent(User $actor, Event $event, string $voidReason): Event
    {
        return $this->voidService->voidEvent($actor, $event, $voidReason);
    }

    /**
     * Get paginated event kelahiran dengan filters
     * Territory scope auto-applied via HasTerritory global scope
     */
    public function paginateWithFilters(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Event::where('event_type_code', 'KELAHIRAN')
            ->with(['penduduk', 'rt.rw.desa', 'eventKelahiran', 'createdBy', 'verifiedBy']);

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
                // Cari berdasarkan penduduk (jika sudah terhubung)
                $q->whereHas('penduduk', function ($pendudukQuery) use ($search) {
                    $pendudukQuery->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                })
                // Atau nama bayi pada detail kelahiran
                ->orWhereHas('eventKelahiran', function ($kelahiranQuery) use ($search) {
                    $kelahiranQuery->where('nama_bayi', 'like', "%{$search}%")
                        ->orWhere('nama_ayah', 'like', "%{$search}%")
                        ->orWhere('nama_ibu', 'like', "%{$search}%");
                });
            });
        }

        return $query
            ->orderBy('event_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get statistik event kelahiran (territory-aware via HasTerritory scope)
     *
     * @return array{total: int, draft: int, verified: int, void: int, pending_approval: int}
     */
    public function getStats(User $user): array
    {
        $query = Event::where('event_type_code', 'KELAHIRAN');

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
}
