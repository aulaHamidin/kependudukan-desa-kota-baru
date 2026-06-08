<?php

declare(strict_types=1);

namespace App\DTOs\Event;

use Carbon\Carbon;

class DatangDTO
{
    public function __construct(
        // Event Datang
        public readonly string $jenisKedatangan,
        public readonly Carbon $tanggalDatang,
        public readonly string $alamatAsal,
        public readonly int $kkTujuanId,
        public readonly string $alasanDatang,
        public readonly ?string $noSuratPindah = null,
        public readonly ?Carbon $tanggalSuratPindah = null,
        public readonly ?string $keteranganAlasan = null,

        // Keterangan umum event (ADDED: dipakai di Event::create)
        public readonly ?string $keterangan = null,

        // Asal
        public readonly ?string $rtAsal = null,
        public readonly ?string $rwAsal = null,
        public readonly ?string $desaAsal = null,
        public readonly ?string $kecamatanAsal = null,
        public readonly ?string $kabupatenAsal = null,
        public readonly ?string $provinsiAsal = null,

        // Data Penduduk
        public readonly ?string $nik = null,
        public readonly ?string $namaLengkap = null,
        public readonly ?string $jenisKelamin = null,
        public readonly ?string $tempatLahir = null,
        public readonly ?Carbon $tglLahir = null,   // nullable - null check di Action
        public readonly ?string $agamaId = null,
        public readonly ?string $statusPerkawinan = null,
        public readonly int $rtId = 0,

        // Optional Penduduk
        public readonly ?string $namaAyah = null,
        public readonly ?string $namaIbu = null,
        public readonly ?string $pendidikanId = null,
        public readonly ?string $pekerjaanId = null,
        public readonly ?int $pendapatanRangeId = null,
        public readonly ?string $golonganDarahId = null,
        public readonly ?string $noHp = null,
        public readonly ?string $email = null,

        // Kembali scenario
        public readonly ?int $pendudukId = null,

        // KK membership (ADDED: dipakai di addToKartuKeluarga)
        public readonly ?string $hubunganKeluargaCode = null,

        // Internal flags (tidak dari request)
        public readonly int $createdBy = 0,
        public readonly array $payload = [],
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            jenisKedatangan: strtoupper($data['jenis_kedatangan']),
            tanggalDatang: Carbon::parse($data['tanggal_datang'] ?? $data['event_date']),
            alamatAsal: $data['alamat_asal'] ?? '',
            kkTujuanId: (int) ($data['kk_tujuan_id'] ?? 0),
            alasanDatang: $data['alasan_datang'] ?? '',
            noSuratPindah: $data['no_surat_pindah'] ?? null,
            tanggalSuratPindah: isset($data['tanggal_surat_pindah'])
                ? Carbon::parse($data['tanggal_surat_pindah'])
                : null,
            keteranganAlasan: $data['keterangan_alasan'] ?? null,
            keterangan: $data['keterangan'] ?? null,

            rtAsal: $data['rt_asal'] ?? null,
            rwAsal: $data['rw_asal'] ?? null,
            desaAsal: $data['desa_asal'] ?? null,
            kecamatanAsal: $data['kecamatan_asal'] ?? null,
            kabupatenAsal: $data['kabupaten_asal'] ?? null,
            provinsiAsal: $data['provinsi_asal'] ?? null,

            nik: $data['nik'] ?? null,
            namaLengkap: $data['nama_lengkap'] ?? null,
            jenisKelamin: $data['jenis_kelamin'] ?? null,
            tempatLahir: $data['tempat_lahir'] ?? null,
            tglLahir: isset($data['tgl_lahir']) ? Carbon::parse($data['tgl_lahir']) : null,
            agamaId: $data['agama_id'] ?? null,
            statusPerkawinan: $data['status_perkawinan'] ?? null,
            rtId: (int) ($data['rt_id'] ?? 0),

            namaAyah: $data['nama_ayah'] ?? null,
            namaIbu: $data['nama_ibu'] ?? null,
            pendidikanId: $data['pendidikan_id'] ?? null,
            pekerjaanId: $data['pekerjaan_id'] ?? null,
            pendapatanRangeId: isset($data['pendapatan_range_id'])
                ? (int) $data['pendapatan_range_id']
                : null,
            golonganDarahId: $data['golongan_darah_id'] ?? null,
            noHp: $data['no_hp'] ?? null,
            email: $data['email'] ?? null,

            pendudukId: isset($data['penduduk_id']) ? (int) $data['penduduk_id'] : null,
            hubunganKeluargaCode: $data['hubungan_keluarga_code'] ?? null,

            createdBy: (int) ($data['created_by'] ?? auth()->id()),
            payload: $data,
        );
    }

    /**
     * FIXED: Normalize ke UPPERCASE untuk konsistensi
     */
    public function isPendatangBaru(): bool
    {
        return $this->jenisKedatangan === 'PENDATANG_BARU';
    }

    public function isPindahMasuk(): bool
    {
        return $this->jenisKedatangan === 'PINDAH_MASUK';
    }

    public function isKembali(): bool
    {
        return $this->jenisKedatangan === 'KEMBALI';
    }

    public function toEventArray(): array
    {
        return [
            'event_type_code' => 'DATANG',
            'event_date'      => $this->tanggalDatang->format('Y-m-d'),
            'keterangan'      => $this->keterangan,
            // FIXED: PENDING → DRAFT
            'status_data'     => 'DRAFT',
            'created_by'      => $this->createdBy,
        ];
    }

    public function toEventDatangArray(int $eventId): array
    {
        return [
            'event_id'             => $eventId,
            'alamat_asal'          => $this->alamatAsal,
            'rt_asal'              => $this->rtAsal,
            'rw_asal'              => $this->rwAsal,
            'desa_asal'            => $this->desaAsal,
            'kecamatan_asal'       => $this->kecamatanAsal,
            'kabupaten_asal'       => $this->kabupatenAsal,
            'provinsi_asal'        => $this->provinsiAsal,
            'alasan_datang'        => $this->alasanDatang,
            'jenis_kedatangan'     => $this->jenisKedatangan,
            'kk_tujuan_id'         => $this->kkTujuanId,
            'no_surat_pindah'      => $this->noSuratPindah,
            'tanggal_surat_pindah' => $this->tanggalSuratPindah?->format('Y-m-d'),
            'keterangan_alasan'    => $this->keteranganAlasan,
        ];
    }

    public function toPendudukArray(?int $eventId = null): array
    {
        $data = [
            'nik'                      => $this->nik,
            'nama_lengkap'             => $this->namaLengkap,
            'jenis_kelamin'            => $this->jenisKelamin,
            'tempat_lahir'             => $this->tempatLahir,
            'tgl_lahir'                => $this->tglLahir?->format('Y-m-d'),
            'agama_id'                 => $this->agamaId,
            'status_perkawinan'        => $this->statusPerkawinan,
            'rt_id'                    => $this->rtId,
            'nama_ayah'                => $this->namaAyah,
            'nama_ibu'                 => $this->namaIbu,
            'pendidikan_id'            => $this->pendidikanId,
            'pekerjaan_id'             => $this->pekerjaanId,
            'pendapatan_range_id'      => $this->pendapatanRangeId,
            'golongan_darah_id'        => $this->golonganDarahId,
            'no_hp'                    => $this->noHp,
            'email'                    => $this->email,
            'status_kependudukan_code' => 'AKTIF',
            'tanggal_status'           => $this->tanggalDatang->format('Y-m-d'),
            'created_by'               => $this->createdBy,
        ];

        if ($eventId) {
            $data['current_event_id'] = $eventId;
        }

        return $data;
    }
}