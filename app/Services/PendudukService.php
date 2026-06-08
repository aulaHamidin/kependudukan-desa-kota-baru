<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Penduduk;
use App\Repositories\Contracts\PendudukRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PendudukService
{
    public function __construct(
        private PendudukRepositoryInterface $repository
    ) {}

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Get paginated penduduk with filters
     * Territory scope is auto-applied via HasTerritory trait
     */
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateWithFilters($filters, $perPage);
    }

    /**
     * Get statistics for current user's territory
     *
     * Uses single aggregated query instead of 6 separate counts.
     * Territory scope is auto-applied via HasTerritory trait.
     */
    public function getStats(): array
    {
        return $this->repository->getStatsAggregated();
    }

    public function findById(int $id): ?Penduduk
    {
        return $this->repository->findById($id);
    }

    public function findByNik(string $nik): ?Penduduk
    {
        return $this->repository->findByNik($nik);
    }

    public function updatePenduduk(int $id, array $data): Penduduk
    {
        $penduduk = $this->repository->findById($id);
        if (!$penduduk) {
            throw new \DomainException('Penduduk tidak ditemukan');
        }

        // Allow all fields for admin_desa
        $allowedFields = [
            'nik',
            'nama_lengkap',
            'tempat_lahir',
            'tgl_lahir',
            'jenis_kelamin',
            'agama_id',
            'nama_ayah',
            'nama_ibu',
            'status_perkawinan',
            'kewarganegaraan',
            'pendidikan_id',
            'pekerjaan_id',
            'pendapatan_range_id',
            'golongan_darah_id',
            'no_hp',
            'email',
        ];
        
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        $updateData['updated_by'] = auth()->id();

        // NIK update logic (restore/uniqueness)
        if (isset($updateData['nik'])) {
            $newNik = $updateData['nik'];
            if ($newNik !== $penduduk->nik) {
                // Check for existing (active or soft-deleted) NIK
                $existing = $this->repository->findByNikWithTrashed($newNik);
                if ($existing && $existing->id !== $penduduk->id) {
                    if ($existing->deleted_at) {
                        // Restore pattern: restore and update existing record, delete current
                        $existing->restore();
                        $this->repository->update($existing, array_merge($updateData, [
                            'updated_by' => auth()->id(),
                        ]));
                        $penduduk->delete();
                        return $existing->fresh();
                    } else {
                        throw new \DomainException('NIK sudah terdaftar di sistem. Tidak boleh duplikat.');
                    }
                }
            }
        }

        $this->repository->update($penduduk, $updateData);
        return $penduduk->fresh();
    }

    public function calculateDataCompleteness(Penduduk $penduduk): array
    {
        $requiredFields = [
            'nik' => 'NIK',
            'nama_lengkap' => 'Nama Lengkap',
            'jenis_kelamin' => 'Jenis Kelamin',
            'tempat_lahir' => 'Tempat Lahir',
            'tgl_lahir' => 'Tanggal Lahir',
            'agama_id' => 'Agama'
        ];

        $optionalFields = [
            'nama_ayah' => 'Nama Ayah',
            'nama_ibu' => 'Nama Ibu',
            'pendidikan_id' => 'Pendidikan',
            'pekerjaan_id' => 'Pekerjaan',
            'no_hp' => 'No. HP',
            'email' => 'Email',
            'golongan_darah_id' => 'Golongan Darah'
        ];

        $requiredFilled = 0;
        $missingFields = [];
        
        foreach ($requiredFields as $field => $label) {
            if (!empty($penduduk->$field)) {
                $requiredFilled++;
            } else {
                $missingFields[] = $label;
            }
        }

        $optionalFilled = 0;
        foreach ($optionalFields as $field => $label) {
            if (!empty($penduduk->$field)) {
                $optionalFilled++;
            } else {
                $missingFields[] = $label;
            }
        }

        $totalFields = count($requiredFields) + count($optionalFields);
        $totalFilled = $requiredFilled + $optionalFilled;
        
        $requiredPercentage = ($requiredFilled / count($requiredFields)) * 70;
        $optionalPercentage = ($optionalFilled / count($optionalFields)) * 30;
        $percentage = (int) round($requiredPercentage + $optionalPercentage);

        return [
            'percentage' => $percentage,
            'filled' => $totalFilled,
            'total' => $totalFields,
            'required_filled' => $requiredFilled,
            'required_total' => count($requiredFields),
            'optional_filled' => $optionalFilled,
            'optional_total' => count($optionalFields),
            'missing' => $missingFields,
        ];
    }
}
