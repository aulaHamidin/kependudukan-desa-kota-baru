<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface TerritoryAware
{
    public function applyTerritoryFilter(Builder $query, User $user): Builder;
}
