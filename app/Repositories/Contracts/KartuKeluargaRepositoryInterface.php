<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\KartuKeluarga;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface KartuKeluargaRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?KartuKeluarga;

    public function findByNoKk(string $noKk): ?KartuKeluarga;

    public function findByNoKkWithTrashed(string $noKk): ?KartuKeluarga;

    public function create(array $data): KartuKeluarga;

    public function update(KartuKeluarga $kk, array $data): bool;

    public function delete(KartuKeluarga $kk): bool;

    public function getByRt(int $rtId): Collection;

    public function getByStatus(string $status): Collection;

    /**
     * Count KK by status (territory-scoped via HasTerritory global scope)
     */
    public function countByStatus(string $status): int;

    /**
     * Count all KK (territory-scoped via HasTerritory global scope)
     */
    public function countAll(): int;

    /**
     * Get aggregated stats in a single query (total, aktif, non_aktif)
     *
     * Territory scope auto-applied via HasTerritory global scope.
     * Replaces 3 separate count queries with 1 query.
     *
     * @return array{total: int, aktif: int, non_aktif: int, no_kepala: int}
     */
    public function getStatsAggregated(): array;
}
