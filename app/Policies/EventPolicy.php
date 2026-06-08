<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Traits\ValidatesTerritory;

class EventPolicy
{
    use ValidatesTerritory;

    public function viewAny(User $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }
        return $this->canViewAny($user);
    }

    public function view(User $user, Event $event): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Viewer hanya bisa lihat VERIFIED
        if ($this->isViewer($user)) {
            return $event->status_data === 'VERIFIED' && $this->canAccessModel($user, $event);
        }

        // Admin desa/rw/rt: semua status dalam territory-nya
        return $this->canAccessModel($user, $event);
    }

    public function create(User $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        return $this->canCreate($user);
    }

    public function createInRt(User $user, int $rtId): bool
    {
        if (!$this->create($user)) {
            return false;
        }
        $rt = \App\Models\Rt::with('rw')->find($rtId);
        if (!$rt || !$rt->rw) {
            return false;
        }

        $territory = [
            'rt_id' => $rtId,
            'rw_id' => $rt->rw_id,
            'desa_id' => $rt->rw->desa_id,
        ];

        if ($this->isAdminDesa($user)) {
            return $user->desa_id !== null
                && $territory['desa_id'] !== null
                && $user->desa_id === $territory['desa_id'];
        }

        if ($this->isAdminRw($user)) {
            return $user->rw_id !== null
                && $territory['rw_id'] !== null
                && $user->rw_id === $territory['rw_id'];
        }

        if ($this->isAdminRt($user)) {
            return $user->rt_id !== null
                && $territory['rt_id'] !== null
                && $user->rt_id === $territory['rt_id'];
        }

        if ($this->isViewer($user)) {
            $viewerDesaId = $this->resolveViewerDesaId($user);

            return $viewerDesaId !== null
                && $territory['desa_id'] !== null
                && (int) $viewerDesaId === (int) $territory['desa_id'];
        }

        return false;
    }

    public function update(User $user, Event $event): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        if ($this->isViewer($user)) {
            return false;
        }
        if ($event->status_data !== 'DRAFT') {
            return false;
        }
        if (!$this->canAccessModel($user, $event)) {
            return false;
        }
        // Semua role operasional: hanya bisa edit DRAFT buatannya sendiri
        return (int) $event->created_by === (int) $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        if ($this->isViewer($user)) {
            return false;
        }
        if ($event->status_data !== 'DRAFT') {
            return false;
        }
        if (!$this->canAccessModel($user, $event)) {
            return false;
        }
        // Admin desa: semua DRAFT dalam desanya
        if ($this->isAdminDesa($user)) {
            return true;
        }
        // Admin RW: semua DRAFT dalam territory RW-nya
        if ($this->isAdminRw($user)) {
            return true;
        }
        // Admin RT: hanya DRAFT buatannya sendiri
        return (int) $event->created_by === (int) $user->id;
    }

    public function verify(User $user, Event $event): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        if ($this->isViewer($user)) {
            return false;
        }
        if ($event->status_data !== 'DRAFT') {
            return false;
        }
        if (!$this->canAccessModel($user, $event)) {
            return false;
        }
        
        // Admin RT tidak boleh verify event apapun
        if ($this->isAdminRt($user)) {
            return false;
        }
        
        // Admin desa boleh verify semua event dalam desanya (termasuk self-approve by design)
        if ($this->isAdminDesa($user)) {
            return true;
        }
        
        // Admin RW hanya boleh verify event dari admin_rt dalam RW-nya, tidak bisa verify buatan sendiri
        if ($this->isAdminRw($user)) {
            $creator = \App\Models\User::find($event->created_by);
            if (!$creator) {
                return false;
            }
            if ((int) $creator->id === (int) $user->id) {
                return false;
            }
            return $creator->hasRole('admin_rt');
        }
        return false;
    }

    public function void(User $user, Event $event): bool
    {
        if ($this->isSuperAdmin($user)) {
            return false;
        }
        if ($event->status_data !== 'VERIFIED') {
            return false;
        }
        if ($this->isAdminRt($user)) {
            return false;
        }
        if ($this->isViewer($user)) {
            return false;
        }
        
        // Hanya admin_desa yang boleh void event (VERIFIED → VOID final)
        return $this->isAdminDesa($user) && $this->canAccessModel($user, $event);
    }

    /**
     * VOID bersifat final — tidak ada role yang boleh unvoid.
     */
    public function unvoid(User $user, Event $event): bool
    {
        return false;
    }
}
