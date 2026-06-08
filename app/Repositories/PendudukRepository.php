<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Penduduk;
use App\Repositories\Contracts\PendudukRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PendudukRepository implements PendudukRepositoryInterface
{
    public function all(): Collection
    {
        return Penduduk::with(['rt.rw', 'agama', 'statusKependudukan'])->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Penduduk::with(['rt.rw', 'agama', 'statusKependudukan'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        // Eager load rt.rw.desa for territory validation in policies
        $query = Penduduk::with(['rt.rw.desa', 'agama', 'statusKependudukan', 'currentEvent']);

        // Search by NIK (exact) or name (partial)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                // NIK 16 digit = exact match (fast, uses index)
                if (preg_match('/^\d{16}$/', $search)) {
                    $q->where('nik', $search);
                } else {
                    // Name search (partial match)
                    $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                        ->orWhere('nik', 'LIKE', "%{$search}%");
                }
            });
        }

        // Filter by status kependudukan
        if (!empty($filters['status_kependudukan_code'])) {
            $query->where('status_kependudukan_code', $filters['status_kependudukan_code']);
        }

        // Filter by jenis kelamin
        if (!empty($filters['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $filters['jenis_kelamin']);
        }

        // Filter by RT (for Admin RW/Desa to drill down)
        if (!empty($filters['rt_id'])) {
            $query->where('rt_id', $filters['rt_id']);
        }

        return $query->orderBy('nama_lengkap', 'asc')->paginate($perPage);
    }

    public function create(array $data): Penduduk
    {
        return Penduduk::create($data);
    }

    public function update(Penduduk $penduduk, array $data): bool
    {
        return $penduduk->update($data);
    }

    public function findByNik(string $nik): ?Penduduk
    {
        return Penduduk::where('nik', $nik)->first();
    }

    /**
     * Find penduduk by NIK including soft deleted records
     * 
     * RESTORE PATTERN:
     * Used when re-inputting deleted NIK
     * Instead of creating new record, restore old one
     * 
     * Why? NIK has unique constraint at database level
     * Cannot insert duplicate NIK even if old one is soft-deleted
     */
    public function findByNikWithTrashed(string $nik): ?Penduduk
    {
        return Penduduk::withTrashed()->where('nik', $nik)->first();
    }

    public function checkNikExists(string $nik): bool
    {
        return Penduduk::where('nik', $nik)->exists();
    }

    public function findById(int $id): ?Penduduk
    {
        return Penduduk::find($id);
    }

    public function delete(Penduduk $penduduk): bool
    {
        return $penduduk->delete();
    }

    public function getByStatus(string $statusCode): Collection
    {
        return Penduduk::where('status_kependudukan_code', $statusCode)
            ->with(['rt.rw', 'agama'])
            ->get();
    }

    public function countByStatus(string $statusCode): int
    {
        return Penduduk::where('status_kependudukan_code', $statusCode)->count();
    }

    public function countByGender(string $gender): int
    {
        return Penduduk::where('jenis_kelamin', $gender)->count();
    }

    /**
     * Get aggregated stats in a single query
     *
     * Territory scope auto-applied via HasTerritory global scope.
     * Combines 6 separate COUNT queries into 1 query using conditional aggregation.
     *
     * @return array{total: int, aktif: int, pindah: int, meninggal: int, laki_laki: int, perempuan: int}
     */
    public function getStatsAggregated(): array
    {
        $result = Penduduk::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'AKTIF' THEN 1 ELSE 0 END) as aktif")
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'PINDAH' THEN 1 ELSE 0 END) as pindah")
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'MENINGGAL' THEN 1 ELSE 0 END) as meninggal")
            ->selectRaw("SUM(CASE WHEN jenis_kelamin = 'L' AND status_kependudukan_code = 'AKTIF' THEN 1 ELSE 0 END) as laki_laki")
            ->selectRaw("SUM(CASE WHEN jenis_kelamin = 'P' AND status_kependudukan_code = 'AKTIF' THEN 1 ELSE 0 END) as perempuan")
            ->first();

        return [
            'total'     => (int) ($result->total ?? 0),
            'aktif'     => (int) ($result->aktif ?? 0),
            'pindah'    => (int) ($result->pindah ?? 0),
            'meninggal' => (int) ($result->meninggal ?? 0),
            'laki_laki' => (int) ($result->laki_laki ?? 0),
            'perempuan' => (int) ($result->perempuan ?? 0),
        ];
    }
}
