<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Approve (verify) event
     *
     * Workflow:
     * - RT creates DRAFT → RW approves → VERIFIED
     * - RW creates DRAFT → RW self-approve → VERIFIED
     * - Desa creates DRAFT → Desa self-approve → VERIFIED
     *
     * Rules (dari Authorization Matrix):
     * - ADMIN_DESA: approve any event in Desa
     * - ADMIN_RW: approve events created by RT (not self)
     * - ADMIN_RT: CANNOT approve (tidak ada di matrix)
     * - SUPER_ADMIN: CANNOT approve
     * - VIEWER: CANNOT approve
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function approveEvent(User $actor, Event $event): Event
    {
        // Authorization via Policy (sudah implemented di Phase 0)
        if (!$actor->can('verify', $event)) {
            throw new AuthorizationException(
                'Anda tidak memiliki izin untuk menyetujui event ini.'
            );
        }

        return DB::transaction(function () use ($actor, $event) {
            // Lock event untuk prevent concurrent approval
            $event = Event::lockForUpdate()->findOrFail($event->id);

            // Validate: hanya DRAFT yang bisa di-approve
            if ($event->status_data !== 'DRAFT') {
                throw new DomainException(
                    'Hanya event berstatus DRAFT yang dapat disetujui.'
                );
            }

            // Validate: tidak boleh self-approve (kecuali RW/Desa approve sendiri)
            $this->validateNoSelfApproval($actor, $event);

            // Update ke VERIFIED
            $event->update([
                'status_data' => 'VERIFIED',
                'verified_by' => $actor->id,
                'verified_at' => now(),
            ]);

            return $event->fresh();
        });
    }

    /**
     * Reject event (kembalikan ke DRAFT dengan catatan)
     *
     * Digunakan ketika approver menolak event yang dibuat RT.
     * Event tetap DRAFT tapi ada rejection note untuk creator.
     *
     * Rules:
     * - Sama dengan approve: ADMIN_RW & ADMIN_DESA
     * - ADMIN_RT tidak bisa reject event orang lain
     *
     * @throws AuthorizationException
     * @throws DomainException
     */
    public function rejectEvent(User $actor, Event $event, string $reason): Event
    {
        // Gunakan policy verify karena rejecter = approver
        if (!$actor->can('verify', $event)) {
            throw new AuthorizationException(
                'Anda tidak memiliki izin untuk menolak event ini.'
            );
        }

        if (empty(trim($reason))) {
            throw new DomainException('Alasan penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($actor, $event, $reason) {
            $event = Event::lockForUpdate()->findOrFail($event->id);

            if ($event->status_data !== 'DRAFT') {
                throw new DomainException(
                    'Hanya event berstatus DRAFT yang dapat ditolak.'
                );
            }

            // Validate: tidak boleh self-reject
            $this->validateNoSelfApproval($actor, $event);

            // Tetap DRAFT tapi tambah rejection note di keterangan
            $event->update([
                'keterangan' => '[DITOLAK] ' . $reason . "\n\n" . ($event->keterangan ?? ''),
            ]);

            return $event->fresh();
        });
    }

    /**
     * Validate no self-approval rule
     *
     * Exception: RW bisa approve event yang dia sendiri buat
     * (karena RW self-approve diperbolehkan sesuai workflow)
     *
     * @throws DomainException
     */
    private function validateNoSelfApproval(User $actor, Event $event): void
    {
        // Admin RW tidak boleh approve event yang dibuat oleh Admin RW lain
        // TAPI boleh approve event yang dibuat oleh RT dalam RW-nya
        if ($actor->hasRole('admin_rw')) {
            $creator = User::find($event->created_by);

            if (!$creator) {
                return;
            }

            // Self-approval: RW approve event buatan diri sendiri
            // Ini DIPERBOLEHKAN (RW self-approve)
            if ((int) $creator->id === (int) $actor->id) {
                return;
            }

            // RW approve event dari RW lain = TIDAK BOLEH
            if ($creator->hasRole('admin_rw') && (int) $creator->id !== (int) $actor->id) {
                throw new DomainException(
                    'Admin RW tidak dapat menyetujui event yang dibuat oleh Admin RW lain.'
                );
            }

            // RW hanya bisa approve event dari RT dalam RW-nya (dicek di Policy)
            if ($creator->hasRole('admin_rt')) {
                return; // Policy sudah handle territory check
            }
        }

        // Admin Desa: bisa approve semua event di desanya (termasuk milik sendiri)
        // Tidak ada restriction self-approval untuk Admin Desa
    }

    /**
     * Get pending events untuk actor berdasarkan role
     *
     * ADMIN_RW: events DRAFT yang dibuat RT dalam RW-nya
     * ADMIN_DESA: semua events DRAFT dalam desanya
     */
    public function getPendingEvents(User $actor, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildPendingQuery($actor)
            ->with(['eventType', 'penduduk', 'rt.rw', 'createdBy']);

        return $query
            ->orderBy('created_at', 'asc') // FIFO: oldest first
            ->paginate($perPage);
    }

    /**
     * Get pending event counts grouped by event_type_code
     *
     * @return array<string, int>
     */
    public function getPendingCountsByType(User $actor): array
    {
        return $this->buildPendingQuery($actor)
            ->select('event_type_code', DB::raw('count(*) as total'))
            ->groupBy('event_type_code')
            ->pluck('total', 'event_type_code')
            ->toArray();
    }

    /**
     * Build base query for pending events filtered by actor role
     */
    private function buildPendingQuery(User $actor): \Illuminate\Database\Eloquent\Builder
    {
        $query = Event::where('status_data', 'DRAFT');

        // Filter berdasarkan role
        if ($actor->hasRole('admin_rw')) {
            $query->whereHas('rt', function ($q) use ($actor) {
                $q->where('rw_id', $actor->rw_id);
            });
        } elseif ($actor->hasRole('admin_desa')) {
            $query->whereHas('rt.rw', function ($q) use ($actor) {
                $q->where('desa_id', $actor->desa_id);
            });
        } else {
            $query->whereRaw('1 = 0'); // Role lain tidak bisa akses
        }

        return $query;
    }
}
