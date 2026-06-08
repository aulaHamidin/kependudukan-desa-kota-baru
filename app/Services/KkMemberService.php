<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\User;
use App\Repositories\Contracts\KkMemberRepositoryInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;

class KkMemberService
{
    public function __construct(
        private KkMemberRepositoryInterface $kkMemberRepo
    ) {}

    public function addMember(User $actor, array $payload): KkMember
    {
        // 1. AUTHORIZATION via Policy
        $kkId = $payload['kartu_keluarga_id'] ?? null;
        if (!$kkId) {
            throw new DomainException('Kartu keluarga wajib dipilih.');
        }

        $kk = KartuKeluarga::with('rt.rw')->findOrFail($kkId);

        if (!$actor->can('manageMembers', $kk)) {
            throw new AuthorizationException(
                'Anda tidak memiliki izin untuk mengelola anggota KK ini.'
            );
        }

        // 2. Business validation
        $penduduk = Penduduk::query()->find($payload['penduduk_id'] ?? 0);
        if (!$penduduk) {
            throw new DomainException('Penduduk tidak ditemukan.');
        }

        if (in_array($penduduk->status_kependudukan_code, ['MENINGGAL', 'PINDAH'], true)) {
            throw new DomainException('Penduduk sudah tidak aktif.');
        }

        // 3. Execute with locking
        return DB::transaction(function () use ($payload, $penduduk) {
            // LOCK: Lock penduduk to prevent concurrent membership assignment
            $this->kkMemberRepo->lockPenduduk($penduduk->id);

            // Check existing membership - satu query, dua skenario
            $existing = KkMember::query()
                ->where('penduduk_id', $penduduk->id)
                ->where('status', 'AKTIF')
                ->first();

            if ($existing) {
                if ((int) $existing->kartu_keluarga_id === (int) $payload['kartu_keluarga_id']) {
                    throw new DomainException('Penduduk sudah terdaftar sebagai anggota KK ini.');
                }
                throw new DomainException('Penduduk sudah terdaftar sebagai anggota KK lain.');
            }

            // LOCK: Check kepala keluarga with lock to prevent race condition
            if (!empty($payload['is_kepala_keluarga'])) {
                $hasKepala = $this->kkMemberRepo->hasKepalaKeluargaWithLock($payload['kartu_keluarga_id']);

                if ($hasKepala) {
                    throw new DomainException('Kartu keluarga sudah memiliki kepala keluarga aktif.');
                }
            }

            $payload['status'] = $payload['status'] ?? 'AKTIF';
            $payload['created_by'] = auth()->id();

            $newMember = $this->kkMemberRepo->create($payload);

            // Reactivate KK if it was NON_AKTIF and now has active member
            $kk = KartuKeluarga::find($payload['kartu_keluarga_id']);
            if ($kk && $kk->status_kk === 'NON_AKTIF' && $payload['status'] === 'AKTIF') {
                $kk->status_kk = 'AKTIF';
                $kk->save();
            }

            return $newMember;
        });
    }

    public function updateMember(User $actor, KkMember $member, array $payload): KkMember
    {
        // 1. AUTHORIZATION via Policy
        $kk = $member->kartuKeluarga()->with('rt.rw')->first();

        if (!$actor->can('manageMembers', $kk)) {
            throw new AuthorizationException(
                'Anda tidak memiliki izin untuk mengelola anggota KK ini.'
            );
        }

        // 2. Execute with locking
        return DB::transaction(function () use ($member, $payload) {
            // LOCK: Lock the member record being updated
            $lockedMember = $this->kkMemberRepo->findByIdWithLock($member->id);

            if (!$lockedMember) {
                throw new DomainException('Data keanggotaan tidak ditemukan.');
            }

            // LOCK: Check kepala keluarga with lock if changing to kepala
            if (!empty($payload['is_kepala_keluarga'])) {
                $kkId = $payload['kartu_keluarga_id'] ?? $lockedMember->kartu_keluarga_id;
                $hasKepala = $this->kkMemberRepo->hasKepalaKeluargaWithLock($kkId);

                if ($hasKepala) {
                    // Check if the existing kepala is this member (allow update)
                    $existingKepala = KkMember::query()
                        ->where('kartu_keluarga_id', $kkId)
                        ->where('status', 'AKTIF')
                        ->where('is_kepala_keluarga', true)
                        ->first();

                    if ($existingKepala && $existingKepala->id !== $lockedMember->id) {
                        throw new DomainException('Kartu keluarga sudah memiliki kepala keluarga aktif.');
                    }
                }
            }

            $lockedMember->fill($payload);
            $lockedMember->save();

            return $lockedMember;
        });
    }

    public function removeMember(User $actor, KkMember $member, array $payload): KkMember
    {
        // 1. AUTHORIZATION via Policy
        $kk = $member->kartuKeluarga()->with('rt.rw')->first();

        if (!$actor->can('manageMembers', $kk)) {
            throw new AuthorizationException(
                'Anda tidak memiliki izin untuk mengelola anggota KK ini.'
            );
        }

        // 2. Business validation
        $status = $payload['status'] ?? 'KELUAR';
        $tanggalKeluar = $payload['tanggal_keluar'] ?? null;

        if (!$tanggalKeluar) {
            throw new DomainException('Tanggal keluar wajib diisi.');
        }

        // 3. Execute with locking
        return DB::transaction(function () use ($member, $payload, $status, $tanggalKeluar) {
            // LOCK: Lock the member record being removed
            $lockedMember = $this->kkMemberRepo->findByIdWithLock($member->id);

            if (!$lockedMember) {
                throw new DomainException('Data keanggotaan tidak ditemukan.');
            }

            $lockedMember->status = $status;
            $lockedMember->tanggal_keluar = $tanggalKeluar;
            $lockedMember->event_keluar_id = $payload['event_keluar_id'] ?? $lockedMember->event_keluar_id;
            $lockedMember->alasan_keluar = $payload['alasan_keluar'] ?? $lockedMember->alasan_keluar;
            $lockedMember->kk_asal_id = $payload['kk_asal_id'] ?? $lockedMember->kk_asal_id;
            $lockedMember->is_kepala_keluarga = false;
            $lockedMember->save();

            $hasRemainingActiveMembers = KkMember::where('kartu_keluarga_id', $lockedMember->kartu_keluarga_id)
                ->where('status', 'AKTIF')
                ->exists();

            if (!$hasRemainingActiveMembers) {
                KartuKeluarga::where('id', $lockedMember->kartu_keluarga_id)
                    ->update(['status_kk' => 'NON_AKTIF']);
            }

            return $lockedMember;
        });
    }

    public function setKepalaKeluarga(User $actor, KkMember $member): KkMember
    {
        // 1. AUTHORIZATION via Policy
        $kk = $member->kartuKeluarga()->with('rt.rw')->first();

        if (!$actor->can('manageMembers', $kk)) {
            throw new AuthorizationException(
                'Anda tidak memiliki izin untuk mengelola anggota KK ini.'
            );
        }

        // 2. Business validation
        if ($member->status !== 'AKTIF') {
            throw new DomainException('Hanya anggota aktif yang dapat dijadikan kepala keluarga.');
        }

        if ($member->is_kepala_keluarga) {
            throw new DomainException('Anggota ini sudah menjadi kepala keluarga.');
        }

        // 3. Execute with locking
        return DB::transaction(function () use ($member) {
            // LOCK: Lock the member record being updated
            $lockedMember = $this->kkMemberRepo->findByIdWithLock($member->id);

            if (!$lockedMember) {
                throw new DomainException('Data keanggotaan tidak ditemukan.');
            }

            // Remove existing kepala keluarga if any
            KkMember::where('kartu_keluarga_id', $lockedMember->kartu_keluarga_id)
                ->where('status', 'AKTIF')
                ->where('is_kepala_keluarga', true)
                ->update(['is_kepala_keluarga' => false]);

            // Set this member as kepala keluarga
            $lockedMember->is_kepala_keluarga = true;
            $lockedMember->save();

            return $lockedMember;
        });
    }
}
