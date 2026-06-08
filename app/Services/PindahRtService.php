<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\Penduduk;
use App\Models\Rt;
use Illuminate\Support\Facades\DB;

class PindahRtService
{
    /**
     * Cek apakah ada event DRAFT terkait KK atau anggota aktifnya.
     * Kembalikan collection event DRAFT yang ditemukan (kosong = aman).
     */
    public function getDraftEvents(KartuKeluarga $kk): \Illuminate\Database\Eloquent\Collection
    {
        $anggotaIds = $kk->kkMembers()
            ->where('status', 'AKTIF')
            ->pluck('penduduk_id');

        return Event::where('status_data', 'DRAFT')
            ->where(function ($q) use ($kk, $anggotaIds) {
                $q->where('kk_id', $kk->id)
                    ->orWhereIn('penduduk_id', $anggotaIds);
            })
            ->with('penduduk:id,nama_lengkap', 'kartuKeluarga:id,no_kk')
            ->get();
    }

    /**
     * Validasi RT tujuan: aktif, berbeda, dalam desa yang sama.
     *
     * @throws \DomainException
     */
    public function validateRtTujuan(KartuKeluarga $kk, int $rtIdTujuan): Rt
    {
        $rtTujuan = Rt::with('rw')->findOrFail($rtIdTujuan);

        if ($rtTujuan->id === $kk->rt_id) {
            throw new \DomainException('RT tujuan harus berbeda dari RT saat ini.');
        }

        // Pastikan RT tujuan dalam desa yang sama
        $rtAsal = $kk->rt()->with('rw')->first();
        if ($rtAsal && $rtTujuan->rw?->desa_id !== $rtAsal->rw?->desa_id) {
            throw new \DomainException('RT tujuan harus berada dalam desa yang sama.');
        }

        return $rtTujuan;
    }

    /**
     * Eksekusi pindah RT: update KK + semua anggota aktif dalam satu transaction.
     *
     * @throws \DomainException
     */
    public function execute(KartuKeluarga $kk, int $rtIdTujuan, ?string $keterangan = null): void
    {
        // 1. Validasi RT tujuan
        $rtTujuan = $this->validateRtTujuan($kk, $rtIdTujuan);

        // 2. Cek event DRAFT — blokir jika ada
        $draftEvents = $this->getDraftEvents($kk);
        if ($draftEvents->isNotEmpty()) {
            throw new \DomainException(
                'Tidak dapat memindahkan RT karena masih ada ' . $draftEvents->count() .
                    ' event berstatus DRAFT terkait KK atau anggotanya. ' .
                    'Selesaikan atau batalkan event tersebut terlebih dahulu.'
            );
        }

        // 3. Ambil ID semua anggota aktif
        $anggotaIds = $kk->kkMembers()
            ->where('status', 'AKTIF')
            ->pluck('penduduk_id');

        // 4. Eksekusi dalam transaction
        DB::transaction(function () use ($kk, $rtTujuan, $anggotaIds) {
            // Update KK — observer Auditable otomatis log ke audit_logs
            $kk->update(['rt_id' => $rtTujuan->id]);

            // Update setiap penduduk anggota aktif
            // Pakai loop agar observer Auditable tiap model ter-trigger
            Penduduk::whereIn('id', $anggotaIds)->get()->each(function (Penduduk $penduduk) use ($rtTujuan) {
                $penduduk->update(['rt_id' => $rtTujuan->id]);
            });
        });
    }

    /**
     * Ambil data untuk ditampilkan di halaman konfirmasi.
     */
    public function getPreviewData(KartuKeluarga $kk): array
    {
        $kk->load([
            'rt.rw.desa',
            'kkMembers' => fn($q) => $q->where('status', 'AKTIF')->with(['penduduk:id,nama_lengkap,nik,jenis_kelamin,tgl_lahir', 'hubunganKeluarga']),
        ]);

        $draftEvents = $this->getDraftEvents($kk);

        return [
            'kk'          => $kk,
            'draftEvents' => $draftEvents,
            'anggotaCount' => $kk->kkMembers->count(),
        ];
    }
}
