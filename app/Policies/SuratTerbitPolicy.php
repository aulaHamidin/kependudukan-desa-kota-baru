<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\{User, SuratTerbit};
use App\Traits\ValidatesTerritory;

/**
 * SuratTerbitPolicy - Authorization untuk surat terbit operations
 * 
 * Implements realistic business authorization matrix:
 * - admin_desa: FULL operational control dalam desa
 * - admin_rw/rt: READ-ONLY monitoring dalam territory  
 * - viewer: READ-ONLY AKTIF surat only
 * - super_admin: MONITORING only (no operational)
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class SuratTerbitPolicy
{
    use ValidatesTerritory;

    /**
     * Determine if user can view any surat (list/index)
     * 
     * BUSINESS RULE: Modul surat hanya untuk admin_desa (fitur internal layanan surat)
     */
    public function viewAny(User $user): bool
    {
        // Hanya super_admin (monitoring) dan admin_desa (operasional)
        return $user->hasAnyRole(['super_admin', 'admin_desa']);
    }

    /**
     * Determine if user can view specific surat
     */
    public function view(User $user, SuratTerbit $surat): bool
    {
        // Super admin can view all for monitoring
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Hanya admin_desa yang bisa view surat dalam desanya
        if ($this->isAdminDesa($user)) {
            return $user->desa_id === $surat->desa_id;
        }

        return false;
    }

    /**
     * Determine if user can create/generate new surat
     * 
     * BUSINESS RULE: Only admin_desa can generate surat
     * (Warga datang langsung ke kantor desa, no RT/RW involvement)
     */
    public function create(User $user): bool
    {
        // ❌ Super admin: monitoring only, no operational
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        // ✅ Admin desa: full operational control
        if ($this->isAdminDesa($user)) {
            return $user->desa_id !== null;
        }

        // ❌ Admin RW/RT: read-only monitoring
        // ❌ Viewer: read-only
        return false;
    }

    /**
     * Determine if user can update surat
     * 
     * BUSINESS RULE: Only admin_desa can modify surat
     */
    public function update(User $user, SuratTerbit $surat): bool
    {
        // ❌ Super admin: monitoring only
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        // ✅ Admin desa: can update surat in their desa
        if ($this->isAdminDesa($user)) {
            return $user->desa_id === $surat->desa_id;
        }

        // ❌ Other roles: read-only
        return false;
    }

    /**
     * Determine if user can delete surat (soft delete)
     * 
     * BUSINESS RULE: Only admin_desa can delete surat
     */
    public function delete(User $user, SuratTerbit $surat): bool
    {
        // Same as update policy
        return $this->update($user, $surat);
    }

    /**
     * Determine if user can download PDF
     * 
     * BUSINESS RULE: Hanya super_admin (monitoring) dan admin_desa (operasional)
     */
    public function download(User $user, SuratTerbit $surat): bool
    {
        // Super admin can download all for monitoring
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Hanya admin_desa yang bisa download surat dalam desanya
        if ($this->isAdminDesa($user)) {
            return $user->desa_id === $surat->desa_id;
        }

        return false;
    }

    /**
     * Determine if user can cancel surat
     * 
     * BUSINESS RULE: Only admin_desa can cancel surat
     */
    public function batalkan(User $user, SuratTerbit $surat): bool
    {
        // ❌ Super admin: monitoring only
        if ($this->isSuperAdmin($user)) {
            return false;
        }

        // ✅ Admin desa: can cancel surat in their desa (if AKTIF)
        if ($this->isAdminDesa($user)) {
            return $surat->isAktif() && $user->desa_id === $surat->desa_id;
        }

        // ❌ Other roles: read-only
        return false;
    }

    /**
     * Determine if user can view expiring surat widget
     * 
     * BUSINESS RULE: Hanya untuk admin_desa (modul surat internal)
     */
    public function viewExpiring(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_desa']);
    }

    /**
     * Determine if user can permanently delete surat
     */
    public function forceDelete(User $user, SuratTerbit $surat): bool
    {
        // Hanya admin_desa yang bisa force delete surat dalam desanya
        if ($this->isAdminDesa($user)) {
            return $user->desa_id === $surat->desa_id;
        }

        return false;
    }
}
