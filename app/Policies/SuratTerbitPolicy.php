<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\{SuratTerbit, User};
use App\Traits\ValidatesTerritory;

class SuratTerbitPolicy
{
    use ValidatesTerritory;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_desa']);
    }

    public function view(User $user, SuratTerbit $surat): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdminDesa($user) && $user->desa_id === $surat->desa_id;
    }

    public function create(User $user): bool
    {
        return $this->isAdminDesa($user) && $user->desa_id !== null;
    }

    public function update(User $user, SuratTerbit $surat): bool
    {
        return false;
    }

    public function delete(User $user, SuratTerbit $surat): bool
    {
        return false;
    }

    public function download(User $user, SuratTerbit $surat): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isAdminDesa($user) && $user->desa_id === $surat->desa_id;
    }

    public function batalkan(User $user, SuratTerbit $surat): bool
    {
        return $this->isAdminDesa($user)
            && $surat->isAktif()
            && $user->desa_id === $surat->desa_id;
    }

    public function regeneratePdf(User $user, SuratTerbit $surat): bool
    {
        return $this->isAdminDesa($user)
            && $surat->isAktif()
            && $user->desa_id === $surat->desa_id;
    }

    public function viewExpiring(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_desa']);
    }

    public function forceDelete(User $user, SuratTerbit $surat): bool
    {
        return false;
    }
}
