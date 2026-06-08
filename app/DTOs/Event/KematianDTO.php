<?php

declare(strict_types=1);

namespace App\DTOs\Event;

use Carbon\Carbon;

class KematianDTO
{
    public function __construct(
        public readonly int $rtId,
        public readonly int $pendudukId,
        public readonly Carbon $eventDate,
        public readonly string $tempatMeninggal,
        public readonly ?string $keterangan = null,

        // Event Kematian detail
        public readonly ?string $jamMeninggal = null,
        public readonly ?string $sebabKematian = null,
        public readonly ?string $penyakit = null,
        public readonly ?string $keteranganKematian = null,

        // Pelapor
        public readonly ?int $pelaporId = null,
        public readonly ?string $namaPelapor = null,
        public readonly ?string $hubunganPelaporCode = null,

        // KK
        public readonly ?int $kkId = null,

        // Pengganti kepala (wajib jika almarhum adalah kepala keluarga)
        public readonly ?int $penggantiKepalaId = null,

        public readonly int $createdBy = 0,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            rtId: (int) ($data['rt_id'] ?? 0),
            pendudukId: (int) ($data['penduduk_id'] ?? 0),
            eventDate: Carbon::parse($data['event_date']),
            keterangan: $data['keterangan'] ?? null,

            tempatMeninggal: !empty($data['tempat_meninggal'])
                ? $data['tempat_meninggal']
                : throw new \InvalidArgumentException('tempat_meninggal wajib diisi.'),
            jamMeninggal: $data['jam_meninggal'] ?? null,
            sebabKematian: $data['sebab_kematian'] ?? null,
            penyakit: $data['penyakit'] ?? null,
            keteranganKematian: $data['keterangan_kematian'] ?? null,

            pelaporId: isset($data['pelapor_id']) ? (int) $data['pelapor_id'] : null,
            namaPelapor: $data['nama_pelapor'] ?? null,
            hubunganPelaporCode: $data['hubungan_pelapor_code'] ?? null,

            kkId: isset($data['kk_id']) ? (int) $data['kk_id'] : null,
            penggantiKepalaId: isset($data['pengganti_kepala_id'])
                ? (int) $data['pengganti_kepala_id']
                : null,

            createdBy: (int) ($data['created_by'] ?? auth()->id()),
        );
    }
}