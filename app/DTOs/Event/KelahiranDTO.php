<?php

declare(strict_types=1);

namespace App\DTOs\Event;

use App\Enums\StatusKelahiran;
use Carbon\Carbon;

class KelahiranDTO
{
    public function __construct(
        public readonly string $eventTypeCode,
        public readonly int $rtId,
        public readonly Carbon $eventDate,
        public readonly ?string $keterangan,

        // Event Kelahiran specific
        public readonly string $namaBayi,
        public readonly string $jenisKelamin,
        public readonly StatusKelahiran $statusKelahiran,
        public readonly string $agamaId,
        public readonly ?int $ayahId,
        public readonly ?int $ibuId,
        public readonly ?string $namaAyah,
        public readonly ?string $namaIbu,
        public readonly string $tempatLahir,
        public readonly ?string $jamLahir,
        public readonly ?int $anakKe,
        public readonly ?float $beratBadanKg,
        public readonly ?float $panjangBadanCm,
        public readonly ?string $penolongKelahiran,
        public readonly ?string $namaPenolong,
        public readonly ?int $kkTujuanId,

        public readonly int $createdBy,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            eventTypeCode: 'KELAHIRAN',
            rtId: (int) $data['rt_id'],
            eventDate: Carbon::parse($data['event_date']),
            keterangan: $data['keterangan'] ?? null,

            namaBayi: $data['nama_bayi'],
            jenisKelamin: $data['jenis_kelamin'],
            statusKelahiran: StatusKelahiran::from($data['status_kelahiran'] ?? 'HIDUP'),
            agamaId: (string) $data['agama_id'], // K4: Explicit cast — agama_id adalah string FK ke agamas.kode
            ayahId: !empty($data['ayah_id']) ? (int) $data['ayah_id'] : null,
            ibuId: !empty($data['ibu_id']) ? (int) $data['ibu_id'] : null,
            namaAyah: $data['nama_ayah'] ?? null,
            namaIbu: $data['nama_ibu'] ?? null,
            tempatLahir: $data['tempat_lahir'],
            jamLahir: $data['jam_lahir'] ?? null,
            anakKe: !empty($data['anak_ke']) ? (int) $data['anak_ke'] : null,
            beratBadanKg: !empty($data['berat_badan_kg']) ? (float) $data['berat_badan_kg'] : null,
            panjangBadanCm: !empty($data['panjang_badan_cm']) ? (float) $data['panjang_badan_cm'] : null,
            penolongKelahiran: $data['penolong_kelahiran'] ?? null,
            namaPenolong: $data['nama_penolong'] ?? null,
            kkTujuanId: !empty($data['kk_tujuan_id']) ? (int) $data['kk_tujuan_id'] : null,

            createdBy: (int) ($data['created_by'] ?? auth()->id()),
        );
    }

    public function toEventArray(): array
    {
        return [
            'event_type_code' => $this->eventTypeCode,
            'event_date' => $this->eventDate->format('Y-m-d'),
            'keterangan' => $this->keterangan,
            'rt_id' => $this->rtId,
            'status_data' => 'DRAFT',
            'created_by' => $this->createdBy,
        ];
    }

    public function toEventKelahiranArray(int $eventId): array
    {
        return [
            'event_id' => $eventId,
            'nama_bayi' => $this->namaBayi,
            'jenis_kelamin' => $this->jenisKelamin,
            'status_kelahiran' => $this->statusKelahiran->value,
            'ayah_id' => $this->ayahId,
            'ibu_id' => $this->ibuId,
            'nama_ayah' => $this->namaAyah,
            'nama_ibu' => $this->namaIbu,
            'tempat_lahir' => $this->tempatLahir,
            'jam_lahir' => $this->jamLahir,
            'anak_ke' => $this->anakKe,
            'berat_badan_kg' => $this->beratBadanKg,
            'panjang_badan_cm' => $this->panjangBadanCm,
            'penolong_kelahiran' => $this->penolongKelahiran,
            'nama_penolong' => $this->namaPenolong,
            'kk_tujuan_id' => $this->kkTujuanId,
        ];
    }

    public function toPendudukBayiArray(int $eventId, int $rwId, int $desaId): array
    {
        return [
            'nik' => self::generateTemporaryNik($eventId),
            'nama_lengkap' => $this->namaBayi,
            'jenis_kelamin' => $this->jenisKelamin,
            'tempat_lahir' => $this->tempatLahir,
            'tgl_lahir' => $this->eventDate->format('Y-m-d'),
            'ayah_id' => $this->ayahId,
            'ibu_id' => $this->ibuId,
            'nama_ayah' => $this->ayahId ? null : $this->namaAyah, // NULL jika ayah adalah penduduk
            'nama_ibu' => $this->ibuId ? null : $this->namaIbu,     // NULL jika ibu adalah penduduk
            'agama_id' => $this->agamaId, // User input manual, bukan auto dari ortu
            'pendidikan_id' => null, 
            'pekerjaan_id'  => null,
            'rt_id' => $this->rtId,
            'status_kependudukan_code' => $this->statusKelahiran === StatusKelahiran::HIDUP ? 'AKTIF' : 'MENINGGAL',
            'current_event_id' => $eventId,
            'tanggal_status' => $this->eventDate->format('Y-m-d'),
            'created_by' => $this->createdBy,
        ];
    }

    /**
     * Generate temporary NIK (16 digit)
     * Format: 99 + YmdHis (12 digit) + eventId last 2 digits
     * 
     * K2: Menggunakan eventId sebagai suffix untuk menghindari collision.
     * random_int(0, 99) hanya 100 kemungkinan — pada volume tinggi bisa duplicate.
     * eventId dijamin unik per event sehingga collision tidak mungkin terjadi.
     */
    public static function generateTemporaryNik(int $eventId): string
    {
        $suffix = str_pad((string) ($eventId % 100), 2, '0', STR_PAD_LEFT);
        return '99' . now()->format('ymdHis') . $suffix;
    }

    /**
     * Check apakah bayi lahir hidup atau mati
     */
    public function isBayiHidup(): bool
    {
        return $this->statusKelahiran === StatusKelahiran::HIDUP;
    }
}