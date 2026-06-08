<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;

class RtPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasRole('admin_desa')
            || $user->hasRole('admin_rw')
            || $user->hasRole('admin_rt')
            || $user->hasRole('viewer');
    }

    public function view(User $user, Rt $rt): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null
                && $rt->rw
                && $user->desa_id === $rt->rw->desa_id;
        }

        if ($user->hasRole('admin_rw')) {
            return $user->rw_id !== null && $user->rw_id === $rt->rw_id;
        }

        if ($user->hasRole('admin_rt')) {
            return $user->rt_id !== null && $user->rt_id === $rt->id;
        }

        if ($user->hasRole('viewer')) {
            $viewerDesaId = $user->rt?->rw?->desa_id;
            return $viewerDesaId !== null
                && $rt->rw
                && (int) $viewerDesaId === (int) $rt->rw->desa_id;
        }

        return false;
    }

    public function create(User $user, ?int $rwId = null): bool
    {
        // Super admin tidak menyentuh operasional wilayah
        if ($user->hasRole('super_admin')) {
            return false;
        }

        if ($user->hasRole('admin_desa') && $user->desa_id !== null) {
            return true;
        }

        // Jika rwId tidak diberikan (akses halaman create), cek apakah user adalah admin_rw
        if ($rwId === null) {
            return $user->hasRole('admin_rw') && $user->rw_id !== null;
        }

        // Jika rwId diberikan (submit form), cek apakah user punya akses ke RW tersebut
        return $user->hasRole('admin_rw')
            && $user->rw_id !== null
            && $user->rw_id === $rwId;
    }

    public function update(User $user, Rt $rt): bool
    {
        // Super admin tidak menyentuh operasional wilayah
        if ($user->hasRole('super_admin')) {
            return false;
        }

        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null
                && $rt->rw
                && $user->desa_id === $rt->rw->desa_id;
        }

        return $user->hasRole('admin_rw')
            && $user->rw_id !== null
            && $user->rw_id === $rt->rw_id;
    }

    public function delete(User $user, Rt $rt): bool
    {
        return $this->update($user, $rt);
    }
}
