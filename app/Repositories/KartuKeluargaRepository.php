<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\KartuKeluarga;
use App\Repositories\Contracts\KartuKeluargaRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class KartuKeluargaRepository implements KartuKeluargaRepositoryInterface
{
    /**
     * Base query dengan territory relations
     *
     * Menjamin semua relasi yang dibutuhkan ValidatesTerritory trait
     * selalu ter-load.
     */
    private function queryWithTerritory(): Builder
    {
        return KartuKeluarga::query()->with(['rt.rw']);
    }

    public function all(): Collection
    {
        return $this->queryWithTerritory()
            ->with(['createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Paginate dengan filters dan eager load kepala keluarga
     *
     * Menggunakan relasi kepalaKeluarga() (HasOne) untuk menghindari
     * N+1 dan filter di setiap query.
     *
     * Column select penduduk dibatasi hanya id,nama_lengkap
     * untuk mengurangi data transfer.
     * 
     * ADDED: Eager load kkMembers.kartuKeluarga.rt.rw untuk policy checks
     * ADDED: Count only active members (status = AKTIF)
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->queryWithTerritory()
            ->withCount(['kkMembers as active_members_count' => function ($q) {
                $q->where('status', 'AKTIF');
            }])
            ->with([
                'kepalaKeluarga.penduduk:id,nama_lengkap',
                'kkMembers.kartuKeluarga.rt.rw', // For policy checks
            ]);

        if (!empty($filters['rt_id'])) {
            $query->where('rt_id', $filters['rt_id']);
        }

        if (!empty($filters['status_kk'])) {
            $query->where('status_kk', $filters['status_kk']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('no_kk', 'like', '%' . $search . '%')
                    ->orWhere('alamat', 'like', '%' . $search . '%');
            });
        }

        // Filter KK tanpa kepala keluarga
        if (!empty($filters['no_kepala'])) {
            $query->whereDoesntHave('kepalaKeluarga');
        }

        return $query
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?KartuKeluarga
    {
        /** @var KartuKeluarga|null */
        return $this->queryWithTerritory()
            ->with(['createdBy'])
            ->find($id);
    }

    public function findByNoKk(string $noKk): ?KartuKeluarga
    {
        return KartuKeluarga::where('no_kk', $noKk)->first();
    }

    public function findByNoKkWithTrashed(string $noKk): ?KartuKeluarga
    {
        return KartuKeluarga::withTrashed()->where('no_kk', $noKk)->first();
    }

    public function create(array $data): KartuKeluarga
    {
        return KartuKeluarga::create($data);
    }

    public function update(KartuKeluarga $kk, array $data): bool
    {
        return $kk->update($data);
    }

    public function delete(KartuKeluarga $kk): bool
    {
        return $kk->delete();
    }

    public function getByRt(int $rtId): Collection
    {
        return KartuKeluarga::where('rt_id', $rtId)
            ->with(['rt.rw'])
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return KartuKeluarga::where('status_kk', $status)
            ->with(['rt.rw'])
            ->get();
    }

    /**
     * Count KK berdasarkan status
     *
     * Territory scope auto-applied via HasTerritory global scope.
     * CRITICAL: Jangan query KartuKeluarga::count() langsung di luar repository
     * karena akan bypass territory scope.
     */
    public function countByStatus(string $status): int
    {
        return KartuKeluarga::where('status_kk', $status)->count();
    }

    /**
     * Count total KK (territory-scoped)
     */
    public function countAll(): int
    {
        return KartuKeluarga::count();
    }

    /**
     * Get aggregated KK stats in a single query
     *
     * Territory scope auto-applied via HasTerritory global scope.
     * Combines separate COUNT queries into 1 query using conditional aggregation.
     *
     * @return array{total: int, aktif: int, non_aktif: int, no_kepala: int}
     */
    public function getStatsAggregated(): array
    {
        $result = KartuKeluarga::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status_kk = 'AKTIF' THEN 1 ELSE 0 END) as aktif")
            ->selectRaw("SUM(CASE WHEN status_kk = 'NON_AKTIF' THEN 1 ELSE 0 END) as non_aktif")
            ->first();

        // Count KK without kepala keluarga (separate query karena involves join)
        $noKepala = KartuKeluarga::query()
            ->whereDoesntHave('kepalaKeluarga')
            ->count();

        return [
            'total'     => (int) ($result->total ?? 0),
            'aktif'     => (int) ($result->aktif ?? 0),
            'non_aktif' => (int) ($result->non_aktif ?? 0),
            'no_kepala' => (int) $noKepala,
        ];
    }
}
