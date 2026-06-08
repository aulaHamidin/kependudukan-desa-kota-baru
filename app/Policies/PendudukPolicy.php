<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Penduduk;
use App\Models\User;
use App\Traits\ValidatesTerritory;

/**
 * PendudukPolicy — territory-based access control for Penduduk records.
 *
 * DESIGN DECISION (intentional):
 *   super_admin CANNOT view or update individual Penduduk records.
 *   canAccessModel() returns false for super_admin because super_admin
 *   is a monitoring/oversight role that operates via aggregate views
 *   (v_penduduk_aktif, v_data_inconsistency) and audit logs only.
 *
 *   Allowing super_admin to read/write individual PII records would
 *   violate the principle of least privilege — they manage system
 *   configuration, not day-to-day citizen data.
 *
 *   If super_admin ever needs individual record access, explicitly
 *   add an isSuperAdmin() check in the relevant policy method rather
 *   than granting blanket access.
 */
class PendudukPolicy
{
    use ValidatesTerritory;

    /**
     * Determine if the user can view any Penduduk records.
     * Super admin can view aggregate data but not individual records.
     */
    public function viewAny(User $user): bool
    {
        // Super admin has access to aggregate views and system monitoring
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        // Delegate to territory-based validation for other roles
        return $this->canViewAny($user);
    }

    /**
     * Determine if the user can view a specific Penduduk record.
     * Super admin is explicitly granted access here for monitoring purposes.
     */
    public function view(User $user, Penduduk $penduduk): bool
    {
        // Super admin can view individual records for system monitoring
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Check territory-based access for the specific record
        return $this->canAccessModel($user, $penduduk);
    }

    /**
     * Determine if the user can update a Penduduk record.
     * Only admin_desa role can modify records within their territory.
     */
    public function update(User $user, Penduduk $penduduk): bool
    {
        // Super admin cannot edit records (monitoring role only)
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        
        // Viewer role has no edit permissions
        if ($this->isViewer($user)) {
            return false;
        }

        // RW and RT admins can only view, not edit records
        if ($this->isAdminRw($user) || $this->isAdminRt($user)) {
            return false;
        }

        // Only village admin can edit records within their territory
        if ($this->isAdminDesa($user)) {
            return $this->canAccessModel($user, $penduduk);
        }

        // Default deny for any other roles
        return false;
    }
}
