<?php

declare(strict_types=1);

namespace App\DTOs\Event;

use Carbon\Carbon;

class PindahDTO
{
    public function __construct(
        // Event parent
        public readonly int $rtId,
        public readonly Carbon $eventDate,
        public readonly ?string $keterangan = null,

        // Penduduk yang pindah
        public readonly int $pendudukId = 0,

        // Data tujuan pindah
        public readonly string $alamatTujuan = '',
        public readonly ?string $rtTujuan = null,
        public readonly ?string $rwTujuan = null,
        public readonly ?string $desaTujuan = null,
        public readonly ?string $kecamatanTujuan = null,
        public readonly ?string $kabupatenTujuan = null,
        public readonly ?string $provinsiTujuan = null,
        public readonly ?string $kodePosTujuan = null,

        // Alasan
        public readonly ?string $alasanPindah = null,
        public readonly ?string $keteranganAlasan = null,
        public readonly string $jenisKepindahan = 'INDIVIDU',

        // KK sumber
        public readonly ?int $kkId = null,

        // Pengganti kepala (wajib jika yang pindah adalah kepala keluarga)
        public readonly ?int $penggantiKepalaId = null,

        // Internal
        public readonly int $createdBy = 0,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            rtId: (int) ($data['rt_id'] ?? 0),
            eventDate: Carbon::parse($data['event_date']),
            keterangan: $data['keterangan'] ?? null,

            pendudukId: (int) ($data['penduduk_id'] ?? 0),

            alamatTujuan: $data['alamat_tujuan'] ?? '',
            rtTujuan: $data['rt_tujuan'] ?? null,
            rwTujuan: $data['rw_tujuan'] ?? null,
            desaTujuan: $data['desa_tujuan'] ?? null,
            kecamatanTujuan: $data['kecamatan_tujuan'] ?? null,
            kabupatenTujuan: $data['kabupaten_tujuan'] ?? null,
            provinsiTujuan: $data['provinsi_tujuan'] ?? null,
            kodePosTujuan: $data['kode_pos_tujuan'] ?? null,

            alasanPindah: $data['alasan_pindah'] ?? null,
            keteranganAlasan: $data['keterangan_alasan'] ?? null,
            jenisKepindahan: strtoupper($data['jenis_kepindahan'] ?? 'INDIVIDU'),

            kkId: isset($data['kk_id']) ? (int) $data['kk_id'] : null,
            penggantiKepalaId: isset($data['pengganti_kepala_id'])
                ? (int) $data['pengganti_kepala_id']
                : null,

            createdBy: (int) ($data['created_by'] ?? auth()->id()),
        );
    }
}