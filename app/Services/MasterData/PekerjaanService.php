<?php

namespace App\Services\MasterData;

use App\Models\Pekerjaan;
use Illuminate\Database\Eloquent\Collection;

class PekerjaanService extends MasterReferenceService
{
    /**
     * @return Collection<int, Pekerjaan>
     */
    public function list(): Collection
    {
        return Pekerjaan::query()
            ->withCount('penduduks')
            ->orderBy('urutan')
            ->get();
    }

    protected function getModelClass(): string
    {
        return Pekerjaan::class;
    }

    protected function getRelationName(): string
    {
        return 'penduduks';
    }
}
