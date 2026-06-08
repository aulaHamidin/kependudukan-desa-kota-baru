<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\KartuKeluarga;
use App\Models\User;
use App\Traits\ValidatesTerritory;

class KartuKeluargaPolicy
{
    use ValidatesTerritory;

    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        if ($this->isAdminDesa($user)) {
            return true;
        }

        return $this->canViewAny($user);
    }

    public function view(User $user, KartuKeluarga $kk): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->canAccessModel($user, $kk);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        if ($this->isViewer($user)) {
            return false;
        }

        if ($this->isAdminDesa($user)) {
            return true;
        }

        return $this->canCreate($user);
    }

    public function update(User $user, KartuKeluarga $kk): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        if ($this->isViewer($user)) {
            return false;
        }

        return $this->canAccessModel($user, $kk);
    }

    public function delete(User $user, KartuKeluarga $kk): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        if ($this->isViewer($user)) {
            return false;
        }

        // Admin desa bisa delete KK apapun statusnya dalam desanya
        if ($this->isAdminDesa($user)) {
            return $this->canAccessModel($user, $kk);
        }

        // Admin RW dan RT hanya bisa delete KK NON_AKTIF dalam territory
        if ($this->isAdminRw($user) || $this->isAdminRt($user)) {
            return $kk->status_kk === 'NON_AKTIF' && $this->canAccessModel($user, $kk);
        }

        return false;
    }

    public function manageMembers(User $user, KartuKeluarga $kk): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        if ($this->isViewer($user)) {
            return false;
        }

        return $this->canAccessModel($user, $kk);
    }

    /**
     * Hanya admin_desa yang boleh memindahkan KK ke RT lain.
     * KK harus berada dalam wilayah desa admin tersebut.
     * Relasi rt.rw harus di-eager load sebelum authorize dipanggil.
     */
    public function pindah(User $user, KartuKeluarga $kk): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        if (!$this->isAdminDesa($user) || $user->desa_id === null) {
            return false;
        }

        return $this->canAccessModel($user, $kk);
    }
}
