<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Contracts\TerritoryAware;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use LogicException;

class TerritoryScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $this->applyForUser($builder, $model, $user);
    }

    public function applyForUser(Builder $builder, Model $model, User $user): Builder
    {
        if ($user->hasRole('super_admin')) {
            return $builder;
        }

        if (!$model instanceof TerritoryAware) {
            throw new LogicException('Model wajib mengimplementasikan TerritoryAware.');
        }

        return $model->applyTerritoryFilter($builder, $user);
    }
}
