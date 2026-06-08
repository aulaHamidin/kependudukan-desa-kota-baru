<?php

declare(strict_types=1);

namespace App\Services\Event;

use App\Actions\Event\CreateEventDatangAction;
use App\DTOs\Event\DatangDTO;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\User;
use App\Repositories\PendudukRepository;
use App\Repositories\EventRepository;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DomainException;

class DatangService
{
    private $createAction;
    private $pendudukRepo;
    private $eventRepo;
    private $voidService;

    public function __construct(
        CreateEventDatangAction $createAction,
        PendudukRepository $pendudukRepo,
        EventRepository $eventRepo,
        EventVoidService $voidService
    ) {
        $this->createAction = $createAction;
        $this->pendudukRepo = $pendudukRepo;
        $this->eventRepo = $eventRepo;
        $this->voidService = $voidService;
    }

    /**
     * Void event datang
     * Delegate ke shared EventVoidService
     */
    public function voidEvent(User $actor, Event $event, string $voidReason): Event
    {
        return $this->voidService->voidEvent($actor, $event, $voidReason);
    }

    /**
     * Delete event datang - hanya DRAFT
     *
     * SEMANTIK: destroy = hapus DRAFT yang belum verified
     * Berbeda dengan void = batalkan VERIFIED
     *
     * Rollback side effects: soft-delete penduduk, remove KK membership.
     * Mirrors rollbackDatang() logic from EventVoidService.
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function deleteEventDatang(User $actor, Event $event): bool
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

            $penduduk    = $event->penduduk;
            $eventDatang = $event->eventDatang;
            $jenisKedatangan = $eventDatang?->jenis_kedatangan;
            $kkId = $event->kk_id;

            // Rollback KK membership jika ada
            if ($penduduk && $kkId) {
                $penduduk->kkMembers()
                    ->where('kartu_keluarga_id', $kkId)
                    ->where('status', 'AKTIF')
                    ->update([
                        'status'         => 'KELUAR',
                        'tanggal_keluar' => now()->toDateString(),
                        'alasan_keluar'  => 'Event datang DRAFT dihapus.',
                    ]);
            }

            // D4+CE6: Rollback penduduk berdasarkan jenis_kedatangan
            if ($penduduk) {
                if ($jenisKedatangan === 'PENDATANG_BARU') {
                    // Pendatang baru → soft-delete (perilaku asli)
                    $penduduk->delete();

                    Log::info('Delete Event Datang: Penduduk soft-deleted (PENDATANG_BARU)', [
                        'event_id'    => $event->id,
                        'penduduk_id' => $penduduk->id,
                    ]);
                } else {
                    // KEMBALI / PINDAH_MASUK → revert status ke PINDAH, jangan soft-delete
                    // Penduduk ini sudah ada sebelumnya, hanya statusnya yang diubah ke AKTIF
                    $penduduk->update([
                        'status_kependudukan_code' => 'PINDAH',
                        'current_event_id'         => null,
                        'tanggal_status'           => $event->event_date,
                    ]);

                    Log::info('Delete Event Datang: Status reverted ke PINDAH (KEMBALI)', [
                        'event_id'    => $event->id,
                        'penduduk_id' => $penduduk->id,
                        'jenis'       => $jenisKedatangan,
                    ]);
                }
            }

            // Deactivate KK jika tidak ada member aktif
            if ($kkId) {
                $hasActiveMembers = KkMember::where('kartu_keluarga_id', $kkId)
                    ->where('status', 'AKTIF')
                    ->exists();

                if (!$hasActiveMembers) {
                    KartuKeluarga::where('id', $kkId)
                        ->update(['status_kk' => 'NON_AKTIF']);
                }
            }

            // Hapus event detail
            if ($eventDatang) {
                $eventDatang->delete();
            }

            return (bool) $event->delete();
        });
    }

    /**
     * Create event datang with authorization, validation, and restore pattern
     * 
     * @param User $actor
     * @param array $payload
     * @return Event
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function createEventDatang(User $actor, array $payload): Event
    {
        // 1. Authorization check via Policy
        $this->authorizeCreate($actor, $payload['rt_id'] ?? null);

        // 2. Business validation
        $this->validateBusinessRules($actor, $payload);

        // 3. RESTORE PATTERN: Check for deleted penduduk with same NIK
        if (!empty($payload['nik'])) {
            $this->handleNikRestore($payload);
        }

        // 4. Create DTO
        $dto = DatangDTO::fromRequest(array_merge($payload, [
            'created_by' => $actor->id,
        ]));

        // 5. Execute action (already wrapped in transaction in Action)
        return $this->createAction->execute($dto);
    }

    /**
     * Update existing event datang
     * 
     * CRITICAL: Wrapped in transaction to prevent race condition
     * Updates Event, EventDatang, and Penduduk data
     * 
     * IMPORTANT: When creator changes RT, also update Event territory (rt_id, rw_id, desa_id)
     * to maintain consistency between Event and Penduduk territory.
     * 
     * @param User $actor
     * @param Event $event
     * @param array $payload
     * @return Event
     * @throws AuthorizationException
     */
    public function updateEventDatang(User $actor, Event $event, array $payload): Event
    {
        // Authorization handled by Controller via Policy

        return DB::transaction(function () use ($event, $payload, $actor) {
            // Lock event to prevent concurrent updates
            $event = Event::lockForUpdate()->findOrFail($event->id);

            // D5+CE5: Territory (RT) tidak boleh diubah sama sekali.
            // Jika salah pilih RT → hapus event, buat ulang.
            if (isset($payload['rt_id']) && (int) $payload['rt_id'] !== (int) $event->rt_id) {
                throw new \Illuminate\Validation\ValidationException(
                    \Illuminate\Support\Facades\Validator::make([], []),
                    new \Illuminate\Http\JsonResponse([
                        'message' => 'RT tidak dapat diubah. Hapus event dan buat ulang jika RT salah.',
                        'errors'  => ['rt_id' => ['RT tidak dapat diubah. Hapus event dan buat ulang jika RT salah.']],
                    ], 422)
                );
            }

            // Prepare kk_id update - allow NULL to clear KK
            $kkId = array_key_exists('kk_tujuan_id', $payload) 
                ? ($payload['kk_tujuan_id'] ?: null) 
                : $event->kk_id;

            // Track KK change for membership update
            $oldKkId = $event->kk_id;
            $kkChanged = $oldKkId != $kkId;

            // Update event tanpa territory change
            $event->update([
                'event_date' => $payload['event_date'] ?? $event->event_date,
                'keterangan' => $payload['keterangan'] ?? $event->keterangan,
                'kk_id' => $kkId,
            ]);

            // Handle KK membership changes
            if ($kkChanged && $event->penduduk) {
                $this->updateKkMembership($event->penduduk, $oldKkId, $kkId, $event->event_date);
            }

            // Update event datang details if exists
            if ($event->eventDatang) {
                // CE4: Gunakan array_key_exists untuk nullable fields
                $detail  = $event->eventDatang;
                $resolve = fn(string $key, mixed $fallback) =>
                    array_key_exists($key, $payload) ? $payload[$key] : $fallback;

                $eventDatangUpdate = [
                    'alamat_asal'      => $payload['alamat_asal'] ?? $detail->alamat_asal, // required
                    'desa_asal'        => $resolve('desa_asal',        $detail->desa_asal),
                    'kecamatan_asal'   => $resolve('kecamatan_asal',   $detail->kecamatan_asal),
                    'kabupaten_asal'   => $resolve('kabupaten_asal',   $detail->kabupaten_asal),
                    'provinsi_asal'    => $resolve('provinsi_asal',    $detail->provinsi_asal),
                    'alasan_datang'    => $resolve('alasan_datang',    $detail->alasan_datang),
                    'keterangan_alasan' => $resolve('keterangan_alasan', $detail->keterangan_alasan),
                    'jenis_kedatangan' => $payload['jenis_kedatangan'] ?? $detail->jenis_kedatangan, // required
                    'kk_tujuan_id'     => array_key_exists('kk_tujuan_id', $payload) 
                        ? ($payload['kk_tujuan_id'] ?: null) 
                        : $detail->kk_tujuan_id,
                    'no_surat_pindah'       => $resolve('no_surat_pindah',       $detail->no_surat_pindah),
                    'tanggal_surat_pindah'  => $resolve('tanggal_surat_pindah',  $detail->tanggal_surat_pindah),
                ];

                $detail->update($eventDatangUpdate);
            }

            // Update penduduk data if exists
            if ($event->penduduk) {
                $pendudukUpdate = [
                    'nama_lengkap' => $payload['nama_lengkap'] ?? $event->penduduk->nama_lengkap,
                    'jenis_kelamin' => $payload['jenis_kelamin'] ?? $event->penduduk->jenis_kelamin,
                    'tempat_lahir' => $payload['tempat_lahir'] ?? $event->penduduk->tempat_lahir,
                    'tgl_lahir' => $payload['tgl_lahir'] ?? $event->penduduk->tgl_lahir,
                    'agama_id' => $payload['agama_id'] ?? $event->penduduk->agama_id,
                    'status_perkawinan' => $payload['status_perkawinan'] ?? $event->penduduk->status_perkawinan,
                    'rt_id' => $payload['rt_id'] ?? $event->penduduk->rt_id,
                    'nama_ayah' => $payload['nama_ayah'] ?? $event->penduduk->nama_ayah,
                    'nama_ibu' => $payload['nama_ibu'] ?? $event->penduduk->nama_ibu,
                    'pendidikan_id' => $payload['pendidikan_id'] ?? $event->penduduk->pendidikan_id,
                    'pekerjaan_id' => $payload['pekerjaan_id'] ?? $event->penduduk->pekerjaan_id,
                    'pendapatan_range_id' => $payload['pendapatan_range_id'] ?? $event->penduduk->pendapatan_range_id,
                    'golongan_darah_id' => $payload['golongan_darah_id'] ?? $event->penduduk->golongan_darah_id,
                    'no_hp' => $payload['no_hp'] ?? $event->penduduk->no_hp,
                    'email' => $payload['email'] ?? $event->penduduk->email,
                    'updated_by' => $actor->id,
                ];

                $this->pendudukRepo->update($event->penduduk, $pendudukUpdate);
            }

            return $event->fresh(['eventDatang', 'penduduk', 'rt.rw.desa', 'kartuKeluarga']);
        });
    }

    /**
     * Get paginated event datang with filters
     * 
     * @param User $user
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithFilters(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Territory scope auto-applied via HasTerritory trait
        return $this->eventRepo->paginateDatangWithFilters($filters, $perPage);
    }

    /**
     * Get event datang statistics
     * 
     * @param User $user
     * @return array
     */
    public function getStats(User $user): array
    {
        // Territory scope auto-applied via HasTerritory trait
        $query = Event::where('event_type_code', 'DATANG');

        return [
            'total' => $query->count(),
            'draft' => (clone $query)->where('status_data', 'DRAFT')->count(),
            'verified' => (clone $query)->where('status_data', 'VERIFIED')->count(),
            'void' => (clone $query)->where('status_data', 'VOID')->count(),
            'pending_approval' => (clone $query)
                ->where('status_data', 'DRAFT')
                ->whereNull('verified_at')
                ->count(),
        ];
    }

    /**
     * Authorize event creation in specific RT
     * 
     * @param User $actor
     * @param int|null $rtId
     * @return void
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
     * Validate business rules before creating event
     * 
     * @param User $actor
     * @param array $payload
     * @return void
     * @throws DomainException
     */
    /**
     * Validasi business rules untuk event datang.
     *
     * K3: Catatan — validasi RT ayah/ibu (di StoreEventKelahiranRequest)
     * hanya berlaku jika ayah_id/ibu_id diisi. Ini by design karena
     * ayah/ibu bisa manual (nama saja, tanpa ID penduduk).
     */
    private function validateBusinessRules(User $actor, array $payload): void
    {
        // 1. Event date validation - tidak boleh future
        if (isset($payload['event_date'])) {
            $eventDate = \Carbon\Carbon::parse($payload['event_date']);

            if ($eventDate->isFuture()) {
                throw new DomainException('Tanggal kedatangan tidak boleh di masa depan.');
            }
        }

        // 2. NIK required untuk pendatang baru
        if (($payload['jenis_kedatangan'] ?? null) === 'PENDATANG_BARU') {
            if (empty($payload['nik'])) {
                throw new DomainException('NIK wajib diisi untuk pendatang baru.');
            }
        }

        // 3. Active event check - penduduk tidak boleh punya event DRAFT/WAITING lain
        if (!empty($payload['penduduk_id']) && Event::pendudukHasPendingEvent($payload['penduduk_id'])) {
            throw new DomainException('Penduduk masih memiliki event yang belum diverifikasi.');
        }
    }

    /**
     * CRITICAL: Handle restore pattern untuk soft-deleted penduduk
     *
     * Jika NIK sudah ada di deleted records:
     * - Set flag _restore_penduduk_id untuk Action
     * - Action akan restore + update bukan create baru
     *
     * UPDATED: Juga set _restored_from_id untuk disimpan di event_datang
     * sehingga rollback saat void bisa detect apakah penduduk hasil restore
     */
    private function handleNikRestore(array &$payload): void
    {
        $existing = $this->pendudukRepo->findByNikWithTrashed($payload['nik']);

        if ($existing && !$existing->trashed()) {
            // D9: NIK sudah ada di record aktif — akan gagal di DB unique constraint
            // tapi log warning agar lebih mudah di-debug.
            Log::warning('Event Datang: NIK sudah ada di record aktif, akan gagal constraint', [
                'nik'         => $payload['nik'],
                'penduduk_id' => $existing->id,
                'status'      => $existing->status_kependudukan_code,
                'actor_id'    => auth()->id(),
            ]);
        }

        if ($existing && $existing->trashed()) {
            Log::info('Event Datang: Restoring soft-deleted penduduk', [
                'nik'             => $payload['nik'],
                'old_penduduk_id' => $existing->id,
                'deleted_at'      => $existing->deleted_at,
                'actor_id'        => auth()->id(),
            ]);

            // Flag untuk Action: restore bukan create baru
            $payload['_restore_penduduk_id'] = $existing->id;

            // Flag untuk EventDatang: simpan restored_from_id
            // Digunakan saat void untuk re-soft-delete
            $payload['_restored_from_id'] = $existing->id;
        }
    }

    /**
     * Update KK membership when event.kk_id changes during edit
     * 
     * Handles 3 scenarios:
     * 1. KK A → KK B: Remove from old KK, add to new KK
     * 2. KK A → NULL: Remove from old KK
     * 3. NULL → KK B: Add to new KK
     * 
     * @param Penduduk $penduduk
     * @param int|null $oldKkId
     * @param int|null $newKkId
     * @param string|Carbon $eventDate
     * @return void
     */
    private function updateKkMembership(Penduduk $penduduk, $oldKkId, $newKkId, $eventDate): void
    {
        $eventDateString = is_string($eventDate) ? $eventDate : $eventDate->format('Y-m-d');

        // Scenario 1 & 2: Remove from old KK if exists
        if ($oldKkId) {
            $updated = \App\Models\KkMember::where('kartu_keluarga_id', $oldKkId)
                ->where('penduduk_id', $penduduk->id)
                ->where('status', 'AKTIF')
                ->update([
                    'status'         => 'KELUAR',
                    'tanggal_keluar' => $eventDateString,
                    'alasan_keluar'  => 'Event datang diubah - KK tujuan berubah.',
                ]);

            if ($updated > 0) {
                Log::info('Update Event Datang: Removed from old KK', [
                    'penduduk_id' => $penduduk->id,
                    'old_kk_id'   => $oldKkId,
                    'new_kk_id'   => $newKkId,
                ]);
            }
        }

        // Scenario 1 & 3: Add to new KK if specified
        if ($newKkId) {
            // Check if already member (shouldn't happen, but defensive)
            $existingMember = \App\Models\KkMember::where('kartu_keluarga_id', $newKkId)
                ->where('penduduk_id', $penduduk->id)
                ->where('status', 'AKTIF')
                ->exists();

            if (!$existingMember) {
                \App\Models\KkMember::create([
                    'kartu_keluarga_id'      => $newKkId,
                    'penduduk_id'            => $penduduk->id,
                    'hubungan_keluarga_code' => 'LAINNYA', // Default, bisa diubah manual
                    'is_kepala_keluarga'     => false,
                    'tanggal_masuk'          => $eventDateString,
                    'status'                 => 'AKTIF',
                    'kk_asal_id'             => $oldKkId, // Track origin KK
                    'created_by'             => auth()->id(),
                ]);

                Log::info('Update Event Datang: Added to new KK', [
                    'penduduk_id' => $penduduk->id,
                    'new_kk_id'   => $newKkId,
                    'old_kk_id'   => $oldKkId,
                ]);
            } else {
                Log::warning('Update Event Datang: Already member of new KK', [
                    'penduduk_id' => $penduduk->id,
                    'new_kk_id'   => $newKkId,
                ]);
            }
        }
    }
}