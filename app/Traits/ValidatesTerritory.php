<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ValidatesTerritory
{
    /**
     * Check if user has super admin role
     */
    protected function isSuperAdmin(User $user): bool
    {
        return $this->userHasRole($user, 'super_admin');
    }

    /**
     * Check if user has admin desa (village admin) role
     */
    protected function isAdminDesa(User $user): bool
    {
        return $this->userHasRole($user, 'admin_desa');
    }

    /**
     * Check if user has admin RW (neighborhood group admin) role
     */
    protected function isAdminRw(User $user): bool
    {
        return $this->userHasRole($user, 'admin_rw');
    }

    /**
     * Check if user has admin RT (neighborhood unit admin) role
     */
    protected function isAdminRt(User $user): bool
    {
        return $this->userHasRole($user, 'admin_rt');
    }

    /**
     * Check if user has viewer role
     */
    protected function isViewer(User $user): bool
    {
        return $this->userHasRole($user, 'viewer');
    }

    /**
     * Check if user can view any records based on their role and territory assignment
     */
    protected function canViewAny(User $user): bool
    {
        // Admin desa can view if they have a desa_id assigned
        if ($this->isAdminDesa($user)) {
            return $user->desa_id !== null;
        }

        // Admin RW can view if they have an rw_id assigned
        if ($this->isAdminRw($user)) {
            return $user->rw_id !== null;
        }

        // Admin RT can view if they have an rt_id assigned
        if ($this->isAdminRt($user)) {
            return $user->rt_id !== null;
        }

        // Viewer can view if they can resolve a desa_id through their RT assignment
        if ($this->isViewer($user)) {
            return $this->resolveViewerDesaId($user) !== null;
        }

        return false;
    }

    /**
     * Check if user can create new records (viewers cannot create)
     */
    protected function canCreate(User $user): bool
    {
        // Viewers are not allowed to create
        if ($this->isViewer($user)) {
            return false;
        }

        // Other roles can create if they can view
        return $this->canViewAny($user);
    }

    /**
     * Check if user can access a specific model based on territory matching
     */
    protected function canAccessModel(User $user, Model $model): bool
    {
        $territory = $this->resolveTerritory($model);

        // Admin desa can access if both user and model have matching desa_id
        if ($this->isAdminDesa($user)) {
            return $user->desa_id !== null
                && $territory['desa_id'] !== null
                && $user->desa_id === $territory['desa_id'];
        }

        // Admin RW can access if both user and model have matching rw_id
        if ($this->isAdminRw($user)) {
            return $user->rw_id !== null
                && $territory['rw_id'] !== null
                && $user->rw_id === $territory['rw_id'];
        }

        // Admin RT can access if both user and model have matching rt_id
        if ($this->isAdminRt($user)) {
            return $user->rt_id !== null
                && $territory['rt_id'] !== null
                && $user->rt_id === $territory['rt_id'];
        }

        // Viewer can access if their resolved desa_id matches model's desa_id
        if ($this->isViewer($user)) {
            $viewerDesaId = $this->resolveViewerDesaId($user);

            return $viewerDesaId !== null
                && $territory['desa_id'] !== null
                && (int) $viewerDesaId === (int) $territory['desa_id'];
        }

        return false;
    }

    /**
     * Resolve territory information (desa_id, rw_id, rt_id) from a model
     * by checking direct attributes and related models
     */
    protected function resolveTerritory(Model $model): array
    {
        $desaId = $model->getAttribute('desa_id');
        $rwId = $model->getAttribute('rw_id');
        $rtId = $model->getAttribute('rt_id');

        // If desa_id or rw_id is missing, try to find them through RT relation
        if (($desaId === null || $rwId === null) && method_exists($model, 'rt')) {
            // Auto-load relation if not eager loaded (prevents 500 error)
            if (!$model->relationLoaded('rt')) {
                $model->load('rt.rw');
            }

            $rt = $model->getRelation('rt');
            if ($rt instanceof \Illuminate\Database\Eloquent\Model) {
                $rtId = $rtId ?? $rt->getAttribute('id');
                $rwId = $rwId ?? $rt->getAttribute('rw_id');

                // Continue searching for desa_id from RT's RW relation
                if ($desaId === null && method_exists($rt, 'rw')) {
                    if (!$rt->relationLoaded('rw')) {
                        $rt->load('rw');
                    }

                    $rw = $rt->getRelation('rw');
                    if ($rw instanceof \Illuminate\Database\Eloquent\Model) {
                        $desaId = $rw->getAttribute('desa_id');
                    }
                }
            }
        }

        // If desa_id is still empty, try to find it from direct RW relation
        if ($desaId === null && method_exists($model, 'rw') && $rwId !== null) {
            if (!$model->relationLoaded('rw')) {
                $model->load('rw');
            }
            $rw = $model->getRelation('rw');
            if ($rw instanceof \Illuminate\Database\Eloquent\Model) {
                $desaId = $rw->getAttribute('desa_id');
            }
        }

        // If desa_id is still empty, try from direct desa relation
        if ($desaId === null && method_exists($model, 'desa')) {
            if (!$model->relationLoaded('desa')) {
                $model->load('desa');
            }
            $desa = $model->getRelation('desa');
            if ($desa instanceof \Illuminate\Database\Eloquent\Model) {
                $desaId = $desa->getAttribute('id');
            }
        }

        return [
            'desa_id' => $desaId,
            'rw_id' => $rwId,
            'rt_id' => $rtId,
        ];
    }

    /**
     * Resolve desa_id for viewer users through their RT → RW → Desa chain
     * Viewers always access through rt_id, never have direct desa_id or rw_id
     */
    protected function resolveViewerDesaId(User $user): ?int
    {
        // Viewer always via rt_id → rw_id → desa_id
        // Never has direct desa_id or rw_id
        if ($user->rt_id !== null) {
            // Ensure relations are loaded to avoid N+1 and null issues
            if (!$user->relationLoaded('rt')) {
                $user->load('rt.rw');
            }

            $rt = $user->rt;
            if ($rt !== null) {
                // Ensure rw is loaded
                if (!$rt->relationLoaded('rw')) {
                    $rt->load('rw');
                }

                if ($rt->rw !== null) {
                    return $rt->rw->desa_id;
                }
            }
        }

        return null;
    }

    /**
     * Check if user has a specific role
     */
    protected function userHasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }
}
