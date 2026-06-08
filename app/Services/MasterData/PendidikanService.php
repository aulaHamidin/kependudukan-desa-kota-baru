<?php

namespace App\Services\MasterData;

use App\Models\Pendidikan;
use Illuminate\Database\Eloquent\Collection;

class PendidikanService extends MasterReferenceService
{
    /**
     * @return Collection<int, Pendidikan>
     */
    public function list(): Collection
    {
        return Pendidikan::query()
            ->withCount('penduduks')
            ->orderBy('urutan')
            ->get();
    }

    protected function getModelClass(): string
    {
        return Pendidikan::class;
    }

    protected function getRelationName(): string
    {
        return 'penduduks';
    }
}
