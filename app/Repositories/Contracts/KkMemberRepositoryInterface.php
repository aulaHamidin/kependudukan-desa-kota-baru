<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\KkMember;
use App\Models\Penduduk;
use Illuminate\Database\Eloquent\Collection;

interface KkMemberRepositoryInterface
{
    public function create(array $data): KkMember;

    public function findActiveByPenduduk(int $pendudukId): ?KkMember;

    /**
     * Find active membership WITH LOCK
     * CRITICAL: Must be called inside DB::transaction()
     */
    public function findActiveByPendudukWithLock(int $pendudukId): ?KkMember;

    public function getActiveByKk(int $kkId): Collection;

    public function hasKepalaKeluarga(int $kkId): bool;

    /**
     * Check if KK has kepala keluarga WITH LOCK
     * CRITICAL: Must be called inside DB::transaction()
     */
    public function hasKepalaKeluargaWithLock(int $kkId): bool;

    /**
     * Set member as kepala keluarga WITH LOCK
     * CRITICAL: Must be called inside DB::transaction()
     */
    public function setAsKepalaKeluarga(KkMember $member): bool;

    public function findByIdWithLock(int $id): ?KkMember;

    public function lockPenduduk(int $pendudukId): ?Penduduk;

    /**
     * Check if penduduk has active event (DRAFT or WAITING_APPROVAL)
     *
     * BUSINESS RULE: A penduduk can only have 1 active event at a time
     */
    public function hasActiveEvent(int $pendudukId): bool;

    /**
     * Lock penduduk and check for active events (atomic operation)
     * CRITICAL: Must be called inside DB::transaction()
     */
    public function lockPendudukAndCheckActiveEvent(int $pendudukId): bool;
}
