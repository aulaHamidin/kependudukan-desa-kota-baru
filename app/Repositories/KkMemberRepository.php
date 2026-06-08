<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\KkMember;
use App\Models\KartuKeluarga;
use App\Models\Penduduk;
use App\Repositories\Contracts\KkMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class KkMemberRepository implements KkMemberRepositoryInterface
{
    public function create(array $data): KkMember
    {
        return KkMember::create($data);
    }

    public function findActiveByPenduduk(int $pendudukId): ?KkMember
    {
        return KkMember::where('penduduk_id', $pendudukId)
            ->where('status', 'AKTIF')
            ->first();
    }

    /**
     * Find active membership WITH LOCK
     * CRITICAL: Must be called inside DB::transaction()
     */
    public function findActiveByPendudukWithLock(int $pendudukId): ?KkMember
    {
        return KkMember::where('penduduk_id', $pendudukId)
            ->where('status', 'AKTIF')
            ->lockForUpdate()
            ->first();
    }

    public function getActiveByKk(int $kkId): Collection
    {
        return KkMember::where('kartu_keluarga_id', $kkId)
            ->where('status', 'AKTIF')
            ->with(['penduduk', 'hubunganKeluarga'])
            ->get();
    }

    public function hasKepalaKeluarga(int $kkId): bool
    {
        return KkMember::where('kartu_keluarga_id', $kkId)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->exists();
    }

    /**
     * Check if KK has kepala keluarga WITH LOCK
     * CRITICAL: Must be called inside DB::transaction()
     *
     * FIX: Lock langsung pada rows kk_members yang relevan, bukan pada
     * parent kartu_keluargas. Lock pada parent table TIDAK otomatis
     * melindungi rows di child table — harus lock di tabel yang dibaca.
     */
    public function hasKepalaKeluargaWithLock(int $kkId): bool
    {
        return KkMember::where('kartu_keluarga_id', $kkId)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->lockForUpdate()
            ->exists();
    }

    /**
     * Set member as kepala keluarga WITH LOCK
     * CRITICAL: Must be called inside DB::transaction()
     */
    public function setAsKepalaKeluarga(KkMember $member): bool
    {
        KartuKeluarga::lockForUpdate()->find($member->kartu_keluarga_id);

        KkMember::where('kartu_keluarga_id', $member->kartu_keluarga_id)
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF')
            ->update(['is_kepala_keluarga' => false]);

        return $member->update(['is_kepala_keluarga' => true]);
    }

    public function findByIdWithLock(int $id): ?KkMember
    {
        return KkMember::lockForUpdate()->find($id);
    }

    public function lockPenduduk(int $pendudukId): ?Penduduk
    {
        return Penduduk::lockForUpdate()->find($pendudukId);
    }

    /**
     * Check if penduduk has active event (DRAFT or WAITING_APPROVAL)
     *
     * BUSINESS RULE ENFORCEMENT:
     * A penduduk can only have 1 active event at a time.
     * This prevents concurrent/conflicting events.
     *
     * Example:
     * - Cannot have simultaneous "DATANG" and "PINDAH" events
     * - Cannot have multiple "KELAHIRAN" events for same baby
     *
     * NOTE: This is NOT a database constraint (events can legitimately duplicate)
     * This is business logic enforced with locking.
     */
    public function hasActiveEvent(int $pendudukId): bool
    {
        return \App\Models\Event::where('penduduk_id', $pendudukId)
            ->whereIn('status_data', ['DRAFT', 'WAITING_APPROVAL'])
            ->exists();
    }

    /**
     * Lock penduduk and check for active events (atomic operation)
     *
     * CRITICAL: Must be called inside DB::transaction()
     *
     * Pattern:
     * 1. Lock penduduk record (prevent concurrent event creation)
     * 2. Check for active events
     * 3. Proceed with event creation if no active events
     */
    public function lockPendudukAndCheckActiveEvent(int $pendudukId): bool
    {
        // Lock penduduk first (acquire row lock)
        $this->lockPenduduk($pendudukId);

        // Then check for active events (while holding lock)
        return $this->hasActiveEvent($pendudukId);
    }
}