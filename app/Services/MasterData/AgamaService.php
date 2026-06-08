<?php

namespace App\Services\MasterData;

use App\Models\Agama;
use Illuminate\Database\Eloquent\Collection;

class AgamaService extends MasterReferenceService
{
    /**
     * @return Collection<int, Agama>
     */
    public function list(): Collection
    {
        return Agama::query()
            ->withCount('penduduks')
            ->orderBy('urutan')
            ->get();
    }

    protected function getModelClass(): string
    {
        return Agama::class;
    }

    protected function getRelationName(): string
    {
        return 'penduduks';
    }
}
