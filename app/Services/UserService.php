<?php

namespace App\Services;

use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function listForIndex(User $actor): Collection
    {
        $query = User::query()
            ->withTrashed()
            ->with(['desa', 'rw.desa', 'rt.rw.desa'])
            ->latest('id');

        if ($actor->role !== 'super_admin') {
            $actorDesaId = $this->resolveDesaId($actor);
            $actorRwId = $this->resolveRwId($actor);

            if ($actor->role === 'admin_desa') {
                $query->where(function ($inner) use ($actorDesaId) {
                    $inner->where('desa_id', $actorDesaId)
                        ->orWhereHas('rw', fn($q) => $q->where('desa_id', $actorDesaId))
                        ->orWhereHas('rt.rw', fn($q) => $q->where('desa_id', $actorDesaId));
                });
            }

            if ($actor->role === 'admin_rw') {
                $query->where(function ($inner) use ($actorRwId) {
                    $inner->where('rw_id', $actorRwId)
                        ->orWhereHas('rt', fn($q) => $q->where('rw_id', $actorRwId));
                });
            }

            if ($actor->role === 'admin_rt') {
                $query->where('rt_id', $actor->rt_id);
            }
        }

        return $query->get();
    }

    public function paginate(User $actor, array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['desa', 'rw.desa', 'rt.rw.desa'])
            ->latest('id');

        if ($actor->role !== 'super_admin') {
            $actorDesaId = $this->resolveDesaId($actor);
            $actorRwId = $this->resolveRwId($actor);

            if ($actor->role === 'admin_desa') {
                $query->where(function ($inner) use ($actorDesaId) {
                    $inner->where('desa_id', $actorDesaId)
                        ->orWhereHas('rw', fn($q) => $q->where('desa_id', $actorDesaId))
                        ->orWhereHas('rt.rw', fn($q) => $q->where('desa_id', $actorDesaId));
                });
            }

            if ($actor->role === 'admin_rw') {
                $query->where(function ($inner) use ($actorRwId) {
                    $inner->where('rw_id', $actorRwId)
                        ->orWhereHas('rt', fn($q) => $q->where('rw_id', $actorRwId));
                });
            }

            if ($actor->role === 'admin_rt') {
                $query->where('rt_id', $actor->rt_id);
            }
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function getById(int $id): User
    {
        return User::query()
            ->with(['desa', 'rw.desa', 'rt.rw.desa'])
            ->findOrFail($id);
    }

    public function store(User $actor, array $payload): User
    {
        if ($actor->role === 'super_admin' && in_array($payload['role'], ['admin_rt', 'viewer'], true)) {
            throw new DomainException('Super admin hanya dapat membuat admin desa atau admin RW.');
        }

        $this->validateRoleAssignment($actor, $payload['role']);
        $this->validateTerritoryAssignment($payload['role'], $payload['desa_id'] ?? null, $payload['rw_id'] ?? null, $payload['rt_id'] ?? null);

        if (! $this->canAssignTerritory($actor, $payload['role'], $payload['desa_id'] ?? null, $payload['rw_id'] ?? null, $payload['rt_id'] ?? null)) {
            throw new DomainException('Wilayah yang dipilih berada di luar cakupan Anda.');
        }

        if ($payload['role'] === 'viewer') {
            if (empty($payload['nik'])) {
                throw new DomainException('NIK wajib diisi untuk role viewer.');
            }

            $nikExists = DB::table('penduduks')->where('nik', $payload['nik'])->exists();
            if (!$nikExists) {
                throw new DomainException('NIK tidak ditemukan dalam database penduduk.');
            }
        }

        return DB::transaction(function () use ($payload) {
            $payload['password'] = Hash::make($payload['password']);
            $payload['email_verified_at'] = now();

            return User::create($payload);
        });
    }

    public function update(User $actor, int $id, array $payload): User
    {
        $user = $this->getById($id);

        $targetRole = $payload['role'] ?? $user->role;

        if ($actor->role === 'super_admin' && $targetRole === 'admin_rt') {
            throw new DomainException('Super admin tidak dapat mengedit akun admin RT.');
        }

        if ($actor->id === $user->id) {
            $this->guardSelfUpdate($user, $payload);
        }

        $this->validateRoleAssignment($actor, $targetRole);

        $desaId = $payload['desa_id'] ?? $user->desa_id;
        $rwId = $payload['rw_id'] ?? $user->rw_id;
        $rtId = $payload['rt_id'] ?? $user->rt_id;

        $this->validateTerritoryAssignment($targetRole, $desaId, $rwId, $rtId);

        if (! $this->canAssignTerritory($actor, $targetRole, $desaId, $rwId, $rtId)) {
            throw new DomainException('Wilayah yang dipilih berada di luar cakupan Anda.');
        }

        if (array_key_exists('is_active', $payload) && $user->role === 'super_admin' && $payload['is_active'] === false) {
            if ($this->isLastSuperAdmin($user->id)) {
                throw new DomainException('Tidak dapat menonaktifkan super admin terakhir.');
            }
        }

        return DB::transaction(function () use ($user, $payload) {
            if (!empty($payload['password'])) {
                $payload['password'] = Hash::make($payload['password']);
            } else {
                unset($payload['password']);
            }

            $user->fill($payload);
            $user->save();

            return $user;
        });
    }

    public function delete(User $actor, int $id): void
    {
        $user = $this->getById($id);

        if ($actor->id === $user->id) {
            throw new DomainException('Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->role === 'super_admin' && $this->isLastSuperAdmin($user->id)) {
            throw new DomainException('Tidak dapat menghapus super admin terakhir.');
        }

        $user->delete();
    }

    protected function validateRoleAssignment(User $actor, string $targetRole): void
    {
        if ($actor->role === 'super_admin') {
            return;
        }

        $allowedRoles = match ($actor->role) {
            'admin_desa' => ['admin_rw', 'admin_rt', 'viewer'],
            'admin_rw' => ['admin_rt', 'viewer'],
            default => [],
        };

        if (!in_array($targetRole, $allowedRoles, true)) {
            throw new DomainException('Anda tidak memiliki izin untuk role tersebut.');
        }
    }

    protected function validateTerritoryAssignment(string $role, ?int $desaId, ?int $rwId, ?int $rtId): void
    {
        if ($role === 'super_admin') {
            if ($desaId || $rwId || $rtId) {
                throw new DomainException('Super admin tidak boleh memiliki wilayah.');
            }

            return;
        }

        if ($role === 'admin_desa') {
            if (!$desaId || $rwId || $rtId) {
                throw new DomainException('Admin desa harus memiliki desa_id saja.');
            }

            return;
        }

        if ($role === 'admin_rw') {
            if ($desaId || !$rwId || $rtId) {
                throw new DomainException('Admin RW harus memiliki rw_id saja.');
            }

            return;
        }

        if (in_array($role, ['admin_rt', 'viewer'], true)) {
            if ($desaId || $rwId || !$rtId) {
                throw new DomainException('Admin RT/Viewer harus memiliki rt_id saja.');
            }
        }
    }

    protected function canAssignTerritory(User $actor, string $role, ?int $desaId, ?int $rwId, ?int $rtId): bool
    {
        if ($actor->role === 'super_admin') {
            return true;
        }

        if ($actor->role === 'admin_desa') {
            if ($role === 'admin_rw') {
                $rw = Rw::find($rwId);
                return $rw && (int) $rw->desa_id === (int) $actor->desa_id;
            }

            if (in_array($role, ['admin_rt', 'viewer'], true)) {
                $rt = Rt::query()->with('rw')->find($rtId);
                return $rt && $rt->rw && (int) $rt->rw->desa_id === (int) $actor->desa_id;
            }
        }

        if ($actor->role === 'admin_rw') {
            if (in_array($role, ['admin_rt', 'viewer'], true)) {
                $rt = Rt::find($rtId);
                return $rt && (int) $rt->rw_id === (int) $actor->rw_id;
            }
        }

        return false;
    }

    protected function isLastSuperAdmin(int $userId): bool
    {
        return User::query()
            ->where('role', 'super_admin')
            ->where('is_active', true)
            ->where('id', '!=', $userId)
            ->doesntExist();
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

    protected function guardSelfUpdate(User $user, array $payload): void
    {
        if (array_key_exists('role', $payload) && $payload['role'] !== $user->role) {
            throw new DomainException('Anda tidak dapat mengubah role akun sendiri.');
        }

        if (array_key_exists('desa_id', $payload) && (int) $payload['desa_id'] !== (int) $user->desa_id) {
            throw new DomainException('Anda tidak dapat mengubah wilayah akun sendiri.');
        }

        if (array_key_exists('rw_id', $payload) && (int) $payload['rw_id'] !== (int) $user->rw_id) {
            throw new DomainException('Anda tidak dapat mengubah wilayah akun sendiri.');
        }

        if (array_key_exists('rt_id', $payload) && (int) $payload['rt_id'] !== (int) $user->rt_id) {
            throw new DomainException('Anda tidak dapat mengubah wilayah akun sendiri.');
        }
    }
}
