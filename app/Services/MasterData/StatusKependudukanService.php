<?php

namespace App\Services\MasterData;

use App\Models\StatusKependudukan;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StatusKependudukanService
{
    public const CORE_STATUSES = [
        'AKTIF',
        'MENINGGAL',
        'PINDAH',
    ];

    /**
     * @param array<string, mixed>|null $filters
     * @return Collection<int, StatusKependudukan>
     */
    public function getAll(?array $filters = null): Collection
    {
        $filters = $filters ?? [];

        $query = StatusKependudukan::query()->orderBy('nama');

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($builder) use ($filters) {
                $builder->where('nama', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get();
    }

    /**
     * @return Collection<int, StatusKependudukan>
     */
    public function list(): Collection
    {
        return StatusKependudukan::query()
            ->withCount('penduduks')
            ->orderBy('nama')
            ->get();
    }

    public function getById(string $kode): StatusKependudukan
    {
        $item = StatusKependudukan::find($kode);

        if (!$item) {
            throw new ModelNotFoundException('Status kependudukan tidak ditemukan.');
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $kode, array $data): StatusKependudukan
    {
        return DB::transaction(function () use ($kode, $data) {
            $item = StatusKependudukan::findOrFail($kode);

            if ($this->isCore($item->kode)) {
                if (array_key_exists('nama', $data) && $data['nama'] !== $item->nama) {
                    throw new DomainException('Status inti tidak boleh diubah namanya.');
                }

                if (array_key_exists('is_active', $data) && !$data['is_active']) {
                    throw new DomainException('Status inti tidak boleh dinonaktifkan.');
                }
            }

            $payload = Arr::except($data, ['kode']);
            $item->update($payload);

            return $item->refresh();
        });
    }

    public function deactivate(string $kode): bool
    {
        return DB::transaction(function () use ($kode) {
            $item = StatusKependudukan::findOrFail($kode);

            if ($this->isCore($item->kode)) {
                throw new DomainException('Status inti tidak boleh dinonaktifkan.');
            }

            if ($item->penduduks()->exists()) {
                throw new DomainException(
                    'Status masih digunakan oleh data penduduk.'
                );
            }

            if ($item->is_active === false) {
                return true;
            }

            return $item->update(['is_active' => false]);
        });
    }

    private function isCore(string $kode): bool
    {
        return in_array(strtoupper($kode), self::CORE_STATUSES, true);
    }
}
