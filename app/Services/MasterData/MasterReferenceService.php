<?php

namespace App\Services\MasterData;

use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

abstract class MasterReferenceService
{
    /**
     * @return class-string<Model>
     */
    abstract protected function getModelClass(): string;

    abstract protected function getRelationName(): string;

    /**
     * @param array<string, mixed>|null $filters
     * @return Collection<int, Model>|LengthAwarePaginator
     */
    public function getAll(?array $filters = null): Collection|LengthAwarePaginator
    {
        $filters = $filters ?? [];
        $modelClass = $this->getModelClass();

        $query = $modelClass::query()->orderBy('urutan');

        if (array_key_exists('is_active', $filters)) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where('nama', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['per_page'])) {
            return $query->paginate((int) $filters['per_page']);
        }

        return $query->get();
    }

    public function getById(string $kode): Model
    {
        $modelClass = $this->getModelClass();
        $item = $modelClass::find($kode);

        if (!$item) {
            throw new ModelNotFoundException('Data tidak ditemukan.');
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function store(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['urutan'])) {
                $data['urutan'] = $this->getNextUrutan();
            }

            if (!array_key_exists('is_active', $data)) {
                $data['is_active'] = true;
            }

            $modelClass = $this->getModelClass();

            return $modelClass::create($data);
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $kode, array $data): Model
    {
        return DB::transaction(function () use ($kode, $data) {
            $modelClass = $this->getModelClass();
            /** @var Model $item */
            $item = $modelClass::findOrFail($kode);

            if (array_key_exists('is_active', $data) && !$data['is_active']) {
                if (!$this->canBeDeactivated($kode)) {
                    throw new DomainException('Tidak dapat menonaktifkan data yang masih digunakan oleh penduduk aktif.');
                }
            }

            $payload = Arr::except($data, ['kode']);
            if (array_key_exists('urutan', $payload) && empty($payload['urutan'])) {
                $payload['urutan'] = $this->getNextUrutan();
            }

            $item->update($payload);

            return $item->refresh();
        });
    }

    public function delete(string $kode): bool
    {
        return DB::transaction(function () use ($kode) {
            $modelClass = $this->getModelClass();
            $relation = $this->getRelationName();

            $item = $modelClass::withCount($relation)->lockForUpdate()->findOrFail($kode);

            if ($item->{$relation . '_count'} > 0) {
                if (!$this->canBeDeactivated($kode)) {
                    throw new DomainException('Tidak dapat menonaktifkan data yang masih digunakan oleh penduduk aktif.');
                }

                return $item->update(['is_active' => false]);
            }

            return (bool) $item->delete();
        });
    }

    protected function canBeDeactivated(string $kode): bool
    {
        $modelClass = $this->getModelClass();
        $relation = $this->getRelationName();

        return !$modelClass::query()
            ->whereKey($kode)
            ->whereHas($relation, function ($query) {
                $query->where('status_kependudukan_code', 'AKTIF');
            })
            ->exists();
    }

    protected function getNextUrutan(): int
    {
        $modelClass = $this->getModelClass();
        $max = $modelClass::query()->lockForUpdate()->max('urutan');

        return ((int) $max) + 1;
    }
}
