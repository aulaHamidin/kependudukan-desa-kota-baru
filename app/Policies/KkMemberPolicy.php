<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KkMember;
use App\Models\User;
use App\Traits\ValidatesTerritory;

class KkMemberPolicy
{
    use ValidatesTerritory;

    public function viewAny(User $user): bool
    {
        // Super admin read only (monitoring)
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        return $this->canViewAny($user);
    }

    public function view(User $user, KkMember $member): bool
    {
        // Super admin read only (monitoring)
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        return $this->canAccessMember($user, $member);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        if ($this->isViewer($user)) {
            return false;
        }
        return $this->canCreate($user);
    }

    public function update(User $user, KkMember $member): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        if ($this->isViewer($user)) {
            return false;
        }
        if ($this->isAdminDesa($user) || $this->isAdminRw($user) || $this->isAdminRt($user)) {
            return $this->canAccessMember($user, $member);
        }

        return false;
    }

    public function delete(User $user, KkMember $member): bool
    {
        // Anggota keluar hanya via event (kematian/pindah/void)
        // Tidak ada role yang boleh delete langsung
        return false;
    }

    private function canAccessMember(User $user, KkMember $member): bool
    {
        if (!$member->relationLoaded('kartuKeluarga')) {
            throw new \RuntimeException('Relasi kartuKeluarga harus di-eager load untuk policy wilayah.');
        }

        $kartuKeluarga = $member->getRelation('kartuKeluarga');
        if (!$kartuKeluarga) {
            return false;
        }

        if (!$kartuKeluarga->relationLoaded('rt')) {
            throw new \RuntimeException('Relasi rt harus di-eager load untuk policy wilayah.');
        }

        $rt = $kartuKeluarga->getRelation('rt');
        if ($rt && !$rt->relationLoaded('rw')) {
            throw new \RuntimeException('Relasi rw harus di-eager load untuk policy wilayah.');
        }

        return $this->canAccessModel($user, $kartuKeluarga);
    }
}
