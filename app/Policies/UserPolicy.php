<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return in_array($actor->role, ['super_admin', 'admin_desa', 'admin_rw', 'admin_rt'], true);
    }

    public function view(User $actor, User $target): bool
    {
        if ($actor->role === 'super_admin') {
            return true;
        }

        return $this->canAccessUser($actor, $target);
    }

    public function create(User $actor): bool
    {
        // Super admin hanya bisa buat role admin_desa & super_admin
        // Admin desa bisa buat RW/RT/viewer
        // Admin RW bisa buat RT/viewer
        return in_array($actor->role, ['super_admin', 'admin_desa', 'admin_rw'], true);
    }

    public function update(User $actor, User $target): bool
    {
        // Tidak boleh update diri sendiri
        if ($actor->id === $target->id) {
            return false;
        }

        // Super admin hanya bisa update role admin_desa
        if ($actor->role === 'super_admin') {
            return $target->role === 'admin_desa';
        }

        // Cek territory access
        if (! $this->canAccessUser($actor, $target)) {
            return false;
        }

        // Admin desa bisa update RW/RT/viewer dalam territory
        // Admin RW bisa update RT/viewer dalam RW
        return $this->canManageRole($actor, $target->role);
    }

    public function delete(User $actor, User $target): bool
    {
        // Tidak boleh delete diri sendiri
        if ($actor->id === $target->id) {
            return false;
        }

        // Super admin hanya bisa delete role admin_desa (kecuali last super admin)
        if ($actor->role === 'super_admin') {
            if ($target->role === 'super_admin') {
                return ! $this->isLastSuperAdmin($target->id);
            }
            return $target->role === 'admin_desa';
        }

        // Cek territory access
        if (! $this->canAccessUser($actor, $target)) {
            return false;
        }

        // Admin desa bisa delete dalam territory (kecuali diri sendiri)
        // Admin RW bisa delete dalam RW (kecuali diri sendiri)
        return $this->canManageRole($actor, $target->role);
    }

    public function restore(User $actor, User $target): bool
    {
        // Tidak boleh restore diri sendiri
        if ($actor->id === $target->id) {
            return false;
        }

        // Super admin bisa restore semua
        if ($actor->role === 'super_admin') {
            return true;
        }

        // Admin desa bisa restore dalam territory (tidak bisa naikkan role)
        if ($actor->role === 'admin_desa') {
            if (! $this->canAccessUser($actor, $target)) {
                return false;
            }
            return $this->canManageRole($actor, $target->role);
        }

        // Admin RW diblock untuk hindari privilege escalation
        return false;
    }

    protected function canManageRole(User $actor, string $targetRole): bool
    {
        if ($actor->role === 'super_admin') {
            return true;
        }

        if ($actor->role === 'admin_desa') {
            return in_array($targetRole, ['admin_rw', 'admin_rt', 'viewer'], true);
        }

        if ($actor->role === 'admin_rw') {
            return in_array($targetRole, ['admin_rt', 'viewer'], true);
        }

        return false;
    }

    protected function canAccessUser(User $actor, User $target): bool
    {
        $actorDesaId = $this->resolveDesaId($actor);
        $targetDesaId = $this->resolveDesaId($target);

        if ($actor->role === 'admin_desa') {
            return $actorDesaId !== null && $actorDesaId === $targetDesaId;
        }

        $actorRwId = $this->resolveRwId($actor);
        $targetRwId = $this->resolveRwId($target);

        if ($actor->role === 'admin_rw') {
            return $actorRwId !== null && $actorRwId === $targetRwId;
        }

        if ($actor->role === 'admin_rt') {
            return $actor->rt_id !== null && $actor->rt_id === $target->rt_id;
        }

        return false;
    }

    protected function resolveDesaId(User $user): ?int
    {
        return match ($user->role) {
            'admin_desa' => $user->desa_id,
            'admin_rw' => $user->rw?->desa_id,
            'admin_rt', 'viewer' => $user->rt?->rw?->desa_id,
            default => null,
        };
    }

    protected function resolveRwId(User $user): ?int
    {
        return match ($user->role) {
            'admin_rw' => $user->rw_id,
            'admin_rt', 'viewer' => $user->rt?->rw_id,
            default => null,
        };
    }

    protected function isLastSuperAdmin(int $userId): bool
    {
        return User::query()
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->where('id', '!=', $userId)
            ->doesntExist();
    }
}
