<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Penduduk;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PendudukRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Penduduk;

    public function findByNik(string $nik): ?Penduduk;

    public function findByNikWithTrashed(string $nik): ?Penduduk;

    public function create(array $data): Penduduk;

    public function update(Penduduk $penduduk, array $data): bool;

    public function delete(Penduduk $penduduk): bool;

    public function checkNikExists(string $nik): bool;

    public function getByStatus(string $statusCode): Collection;

    public function countByStatus(string $statusCode): int;

    public function countByGender(string $gender): int;

    /**
     * Get aggregated stats in a single query (total, by status, by gender)
     *
     * Territory scope auto-applied via HasTerritory global scope.
     * Replaces 6 separate count queries with 1 query.
     *
     * @return array{total: int, aktif: int, pindah: int, meninggal: int, laki_laki: int, perempuan: int}
     */
    public function getStatsAggregated(): array;
}
