<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Desa;
use App\Models\User;

class DesaPolicy
{
    public function viewAny(User $user): bool
    {
        // All roles can view desa list (with territory filtering)
        return $user->hasAnyRole(['super_admin', 'admin_desa', 'admin_rw', 'admin_rt', 'viewer']);
    }

    public function view(User $user, Desa $desa): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null && $user->desa_id === $desa->id;
        }

        if ($user->hasRole('admin_rw')) {
            $rwDesaId = $user->rw?->desa_id;
            return $rwDesaId !== null && (int) $rwDesaId === (int) $desa->id;
        }

        if ($user->hasRole('admin_rt')) {
            $rtDesaId = $user->rt?->rw?->desa_id;
            return $rtDesaId !== null && (int) $rtDesaId === (int) $desa->id;
        }

        if ($user->hasRole('viewer')) {
            $viewerDesaId = $user->rt?->rw?->desa_id;
            return $viewerDesaId !== null && (int) $viewerDesaId === (int) $desa->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Desa $desa): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Desa $desa): bool
    {
        return $user->hasRole('super_admin');
    }
}
