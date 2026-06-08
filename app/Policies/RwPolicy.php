<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Rw;
use App\Models\User;

class RwPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasRole('admin_desa')
            || $user->hasRole('admin_rw')
            || $user->hasRole('admin_rt')
            || $user->hasRole('viewer');
    }

    public function view(User $user, Rw $rw): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('admin_desa')) {
            return $user->desa_id !== null && $user->desa_id === $rw->desa_id;
        }

        if ($user->hasRole('admin_rw')) {
            return $user->rw_id !== null && $user->rw_id === $rw->id;
        }

        if ($user->hasRole('admin_rt')) {
            return $user->rt_id !== null
                && $user->rt
                && (int) $user->rt->rw_id === (int) $rw->id;
        }

        if ($user->hasRole('viewer')) {
            $viewerDesaId = $user->rt?->rw?->desa_id;
            return $viewerDesaId !== null && (int) $viewerDesaId === (int) $rw->desa_id;
        }

        return false;
    }

    public function create(User $user, ?int $desaId = null): bool
    {
        // Super admin tidak menyentuh operasional wilayah
        if ($user->hasRole('super_admin')) {
            return false;
        }

        // Jika desaId tidak diberikan (akses halaman create), cek apakah user adalah admin_desa
        if ($desaId === null) {
            return $user->hasRole('admin_desa') && $user->desa_id !== null;
        }

        // Jika desaId diberikan (submit form), cek apakah user punya akses ke desa tersebut
        return $user->hasRole('admin_desa')
            && $user->desa_id !== null
            && $user->desa_id === $desaId;
    }

    public function update(User $user, Rw $rw): bool
    {
        // Super admin tidak menyentuh operasional wilayah
        if ($user->hasRole('super_admin')) {
            return false;
        }

        return $user->hasRole('admin_desa')
            && $user->desa_id !== null
            && $user->desa_id === $rw->desa_id;
    }

    public function delete(User $user, Rw $rw): bool
    {
        return $this->update($user, $rw);
    }
}
