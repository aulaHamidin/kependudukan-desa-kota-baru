<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\KartuKeluargaDTO;
use App\Models\KartuKeluarga;
use App\Repositories\Contracts\KartuKeluargaRepositoryInterface;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class KartuKeluargaService
{
    public function __construct(
        private KartuKeluargaRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filters);
    }

    public function findById(int $id): ?KartuKeluarga
    {
        return $this->repository->findById($id);
    }

    /**
     * Get stats KK yang territory-aware
     *
     * CRITICAL: Jangan query KartuKeluarga::count() langsung di Controller.
     * Query langsung bypass HasTerritory global scope → admin RT bisa
     * lihat stats seluruh desa. Ini security boundary violation.
     *
     * Uses aggregated query for counts.
     *
     * @return array{total: int, aktif: int, non_aktif: int, no_kepala: int}
     */
    public function getStats(): array
    {
        return $this->repository->getStatsAggregated();
    }

    public function createKartuKeluarga(KartuKeluargaDTO $dto): KartuKeluarga
    {
        return DB::transaction(function () use ($dto) {
            // Cek apakah No KK sudah ada (termasuk yang terhapus)
            $existing = $this->repository->findByNoKkWithTrashed($dto->noKk);

            if ($existing) {
                if ($existing->trashed()) {
                    // Restore dan update data
                    $existing->restore();
                    $this->repository->update($existing, $dto->toArray());

                    return $existing->fresh();
                }

                throw new DomainException('Nomor KK sudah terdaftar.');
            }

            return $this->repository->create($dto->toArray());
        });
    }

    public function updateKartuKeluarga(int $id, KartuKeluargaDTO $dto): KartuKeluarga
    {
        return DB::transaction(function () use ($id, $dto) {
            $kk = $this->repository->findById($id);

            if (!$kk) {
                throw new DomainException('Kartu Keluarga tidak ditemukan.');
            }
            
            // Cek jika status diubah ke NON_AKTIF tapi masih ada member aktif
            if ($dto->statusKk === 'NON_AKTIF') {
                $hasActiveMembers = $kk->kkMembers()
                    ->where('status', 'AKTIF')
                    ->exists();

                if ($hasActiveMembers) {
                    throw new DomainException(
                        'Tidak dapat menonaktifkan KK yang masih memiliki anggota aktif.'
                    );
                }
            }
            
            // Cek No KK conflict (exclude current)
            $existing = $this->repository->findByNoKkWithTrashed($dto->noKk);

            if ($existing && $existing->id !== $id) {
                throw new DomainException(
                    $existing->trashed()
                        ? 'Nomor KK sudah digunakan oleh KK lain (terhapus).'
                        : 'Nomor KK sudah digunakan oleh KK lain.'
                );
            }

            $this->repository->update($kk, $dto->toUpdateArray());

            return $kk->fresh();
        });
    }

    public function deleteKartuKeluarga(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $kk = $this->repository->findById($id);

            if (!$kk) {
                throw new DomainException('Kartu Keluarga tidak ditemukan.');
            }

            $hasActiveMembers = $kk->kkMembers()
                ->where('status', 'AKTIF')
                ->exists();

            if ($hasActiveMembers) {
                throw new DomainException(
                    'Tidak dapat menghapus KK yang masih memiliki anggota aktif.'
                );
            }

            return $this->repository->delete($kk);
        });
    }
}
