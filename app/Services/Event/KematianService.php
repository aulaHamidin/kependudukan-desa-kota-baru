<?php

declare(strict_types=1);

namespace App\Services\Event;

use App\Actions\Event\CreateEventKematianAction;
use App\DTOs\Event\KematianDTO;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KematianService
{
    public function __construct(
        private CreateEventKematianAction $createAction,
        private EventVoidService $voidService
    ) {}

    public function createEventKematian(User $actor, array $payload): Event
    {
        if (!$actor->can('createInRt', [Event::class, $payload['rt_id'] ?? null])) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk membuat event di RT ini.');
        }

        // NOTE: Check pendudukHasPendingEvent TIDAK dilakukan di sini.
        // Check dilakukan di dalam transaction setelah lockForUpdate pada penduduk
        // di CreateEventKematianAction::execute — itu satu-satunya check yang
        // benar-benar atomic dan aman dari race condition.

        $dto = KematianDTO::fromRequest(array_merge($payload, ['created_by' => $actor->id]));
        return $this->createAction->execute($dto);
    }

    public function updateEventKematian(User $actor, Event $event, array $payload): Event
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
                'event_date' => $payload['event_date'] ?? $event->event_date,
                'keterangan' => $payload['keterangan'] ?? $event->keterangan,
            ]);

            if ($event->eventKematian) {
                // Gunakan array_key_exists agar field nullable bisa di-clear (kirim null).
                // Operator ?? tidak bisa membedakan antara "key tidak dikirim" vs "sengaja null".
                $detail  = $event->eventKematian;
                $resolve = fn(string $key, mixed $fallback) =>
                    array_key_exists($key, $payload) ? $payload[$key] : $fallback;

                $detail->update([
                    'tempat_meninggal'      => $payload['tempat_meninggal']      ?? $detail->tempat_meninggal, // required, aman pakai ??
                    'jam_meninggal'         => $resolve('jam_meninggal',         $detail->jam_meninggal),
                    'sebab_kematian'        => $resolve('sebab_kematian',        $detail->sebab_kematian),
                    'penyakit'              => $resolve('penyakit',              $detail->penyakit),
                    'keterangan_kematian'   => $resolve('keterangan_kematian',   $detail->keterangan_kematian),
                    'pelapor_id'            => $resolve('pelapor_id',            $detail->pelapor_id),
                    'nama_pelapor'          => $resolve('nama_pelapor',          $detail->nama_pelapor),
                    'hubungan_pelapor_code' => $resolve('hubungan_pelapor_code', $detail->hubungan_pelapor_code),
                    'pengganti_id'          => $resolve('pengganti_kepala_id',   $detail->pengganti_id),
                ]);

                // M3: Sync perubahan pengganti_kepala_id ke KK membership
                if (array_key_exists('pengganti_kepala_id', $payload)) {
                    $oldPenggantiId = $detail->getOriginal('pengganti_id');
                    $newPenggantiId = $payload['pengganti_kepala_id'];

                    if ($oldPenggantiId !== $newPenggantiId && $event->kk_id) {
                        // Unset kepala lama
                        if ($oldPenggantiId) {
                            KkMember::where('penduduk_id', $oldPenggantiId)
                                ->where('kartu_keluarga_id', $event->kk_id)
                                ->where('status', 'AKTIF')
                                ->update(['is_kepala_keluarga' => false]);
                        }

                        // Set kepala baru
                        if ($newPenggantiId) {
                            KkMember::where('penduduk_id', $newPenggantiId)
                                ->where('kartu_keluarga_id', $event->kk_id)
                                ->where('status', 'AKTIF')
                                ->update(['is_kepala_keluarga' => true]);
                        }
                    }
                }
            }

            return $event->fresh(['eventKematian', 'penduduk', 'rt.rw.desa']);
        });
    }

    public function deleteEventKematian(User $actor, Event $event): bool
    {
        if (!$actor->can('delete', $event)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk menghapus event ini.');
        }

        if ($event->status_data !== 'DRAFT') {
            throw new DomainException(
                'Hanya event berstatus DRAFT yang dapat dihapus. Gunakan void untuk event yang sudah diverifikasi.'
            );
        }

        return DB::transaction(function () use ($event) {
            $event    = Event::lockForUpdate()->findOrFail($event->id);
            $almarhum = $event->penduduk;
            $detail   = $event->eventKematian; // was_kepala & pengganti_id ada di sini

            if ($almarhum) {
                // M4: Restore tanggal_status dari event sebelumnya (bukan membership)
                // agar konsisten dengan pola inferPreviousStatus() di EventVoidService.
                $previousEvent = Event::where('penduduk_id', $almarhum->id)
                    ->where('id', '!=', $event->id)
                    ->where('status_data', '!=', 'VOID')
                    ->orderByDesc('event_date')
                    ->orderByDesc('id')
                    ->first();

                $previousEventDate = $previousEvent?->event_date ?? $event->event_date;

                $almarhum->update([
                    'status_kependudukan_code' => 'AKTIF',
                    'current_event_id'         => $previousEvent?->id,
                    'tanggal_status'           => $previousEventDate,
                ]);

                // Restore KK membership dengan nilai is_kepala_keluarga yang presisi.
                // was_kepala adalah snapshot saat event dibuat — bukan asumsi selalu true.
                KkMember::where('penduduk_id', $almarhum->id)
                    ->where('event_keluar_id', $event->id)
                    ->update([
                        'status'             => 'AKTIF',
                        'tanggal_keluar'     => null,
                        'event_keluar_id'    => null,
                        'alasan_keluar'      => null,
                        'is_kepala_keluarga' => $detail?->was_kepala ?? false,
                    ]);

                if ($event->kk_id) {
                    // Rollback pengganti kepala secara presisi — hanya unset pengganti
                    // yang spesifik ditunjuk saat event ini dibuat, bukan semua kepala aktif.
                    if ($detail?->pengganti_id) {
                        KkMember::where('penduduk_id', $detail->pengganti_id)
                            ->where('kartu_keluarga_id', $event->kk_id)
                            ->where('status', 'AKTIF')
                            ->update(['is_kepala_keluarga' => false]);
                    }

                    // Reactivate KK jika sebelumnya di-deactivate karena kosong
                    KartuKeluarga::where('id', $event->kk_id)
                        ->where('status_kk', 'NON_AKTIF')
                        ->update(['status_kk' => 'AKTIF']);
                }
            }

            $detail?->delete();
            return (bool) $event->delete();
        });
    }

    public function voidEvent(User $actor, Event $event, string $voidReason): Event
    {
        return $this->voidService->voidEvent($actor, $event, $voidReason);
    }

    public function paginateWithFilters(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Event::where('event_type_code', 'KEMATIAN')
            ->with(['penduduk', 'rt.rw.desa', 'eventKematian', 'createdBy', 'verifiedBy']);

        // Territory filter — admin hanya bisa lihat data wilayahnya sendiri
        $this->applyTerritoryFilter($query, $user);

        if (!empty($filters['status_data'])) $query->where('status_data', $filters['status_data']);
        if (!empty($filters['start_date']))  $query->whereDate('event_date', '>=', $filters['start_date']);
        if (!empty($filters['end_date']))    $query->whereDate('event_date', '<=', $filters['end_date']);
        if (!empty($filters['rt_id']))       $query->where('rt_id', $filters['rt_id']);
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->whereHas('penduduk', function ($pendudukQuery) use ($search) {
                    $pendudukQuery->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            });
        }

        return $query->orderBy('event_date', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getStats(User $user): array
    {
        $query = Event::where('event_type_code', 'KEMATIAN');

        // Territory filter — konsisten dengan paginateWithFilters
        $this->applyTerritoryFilter($query, $user);

        return [
            'total'            => (clone $query)->count(),
            'draft'            => (clone $query)->where('status_data', 'DRAFT')->count(),
            'verified'         => (clone $query)->where('status_data', 'VERIFIED')->count(),
            'void'             => (clone $query)->where('status_data', 'VOID')->count(),
            'pending_approval' => (clone $query)->where('status_data', 'DRAFT')->whereNull('verified_at')->count(),
        ];
    }

    /**
     * Filter query berdasarkan territory user.
     * Super admin / viewer tidak difilter (bisa lihat semua).
     */
    private function applyTerritoryFilter(\Illuminate\Database\Eloquent\Builder $query, User $user): void
    {
        if ($user->hasRole('admin_rt')) {
            $query->where('rt_id', $user->rt_id);
        } elseif ($user->hasRole('admin_rw')) {
            $query->where('rw_id', $user->rw_id);
        } elseif ($user->hasRole('admin_desa')) {
            $query->where('desa_id', $user->desa_id);
        }
        // super_admin, viewer, dll: tidak difilter
    }
}
