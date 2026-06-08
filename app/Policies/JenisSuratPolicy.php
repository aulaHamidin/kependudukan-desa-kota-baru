<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\{User, JenisSurat};
use App\Traits\ValidatesTerritory;

/**
 * JenisSuratPolicy - Authorization untuk jenis surat master data
 * 
 * BUSINESS RULE: Only super_admin can manage master data
 * All other roles are read-only for reference during surat creation
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class JenisSuratPolicy
{
    use ValidatesTerritory;

    /**
     * Determine if user can view any jenis surat (for dropdown/reference)
     * 
     * BUSINESS RULE: Admin_desa butuh akses untuk dropdown saat buat surat terbit
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_desa']);
    }

    /**
     * Determine if user can view specific jenis surat details
     */
    public function view(User $user, JenisSurat $jenisSurat): bool
    {
        // Super admin dan admin_desa bisa view untuk preview template
        return $this->viewAny($user);
    }

    /**
     * Determine if user can create new jenis surat
     * 
     * BUSINESS RULE: Only super_admin manages master data
     */
    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine if user can update jenis surat
     * 
     * BUSINESS RULE: Only super_admin manages master data
     */
    public function update(User $user, JenisSurat $jenisSurat): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine if user can delete jenis surat
     * 
     * BUSINESS RULE: Only super_admin manages master data
     * Must check referential integrity
     */
    public function delete(User $user, JenisSurat $jenisSurat): bool
    {
        if (!$this->isSuperAdmin($user)) {
            return false;
        }

        // Prevent deletion if still referenced by surat_terbit
        return $jenisSurat->suratTerbit()->count() === 0;
    }

    /**
     * Determine if user can restore soft-deleted jenis surat
     */
    public function restore(User $user, JenisSurat $jenisSurat): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Determine if user can permanently delete jenis surat
     */
    public function forceDelete(User $user, JenisSurat $jenisSurat): bool
    {
        if (!$this->isSuperAdmin($user)) {
            return false;
        }

        // Double check no dependencies exist
        return $jenisSurat->suratTerbit()->withTrashed()->count() === 0;
    }

    /**
     * Determine if user can activate/deactivate jenis surat
     */
    public function toggle(User $user, JenisSurat $jenisSurat): bool
    {
        return $this->isSuperAdmin($user);
    }
}
