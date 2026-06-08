<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Scopes\TerritoryScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasTerritory
{
    protected static function bootHasTerritory(): void
    {
        static::addGlobalScope(new TerritoryScope());
    }

    public function scopeForTerritory(Builder $query, User $user): Builder
    {
        return (new TerritoryScope())->applyForUser($query, $this, $user);
    }

    public function applyTerritoryFilter(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin_rt')) {
            if ($user->rt_id === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where($this->getTable() . '.rt_id', $user->rt_id);
        }

        if ($user->hasRole('admin_rw')) {
            if ($user->rw_id === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('rt', function (Builder $rtQuery) use ($user) {
                $rtQuery->where('rw_id', $user->rw_id);
            });
        }

        if ($user->hasRole('admin_desa')) {
            if ($user->desa_id === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('rt.rw', function (Builder $rwQuery) use ($user) {
                $rwQuery->where('desa_id', $user->desa_id);
            });
        }

        if ($user->hasRole('viewer')) {
            $viewerDesaId = $user->rt?->rw?->desa_id;

            if ($viewerDesaId === null) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('rt.rw', function (Builder $rwQuery) use ($viewerDesaId) {
                $rwQuery->where('desa_id', $viewerDesaId);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
