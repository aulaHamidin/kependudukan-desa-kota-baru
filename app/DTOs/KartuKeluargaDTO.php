<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

class KartuKeluargaDTO
{
    public function __construct(
        public readonly string $noKk,
        public readonly string $alamat,
        public readonly int $rtId,
        public readonly string $statusKk,
        public readonly Carbon $tanggalTerbentuk,
        public readonly ?int $id = null
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            noKk: $data['no_kk'],
            alamat: $data['alamat'],
            rtId: (int) $data['rt_id'],
            statusKk: $data['status_kk'] ?? 'AKTIF',
            tanggalTerbentuk: Carbon::parse($data['tanggal_terbentuk']),
            id: isset($data['id']) ? (int) $data['id'] : null
        );
    }

    public function toArray(): array
    {
        return [
            'no_kk' => $this->noKk,
            'alamat' => $this->alamat,
            'rt_id' => $this->rtId,
            'status_kk' => $this->statusKk,
            'tanggal_terbentuk' => $this->tanggalTerbentuk->format('Y-m-d'),
            'created_by' => auth()->id(),
        ];
    }

    public function toUpdateArray(): array
    {
        return [
            'no_kk' => $this->noKk,
            'alamat' => $this->alamat,
            'rt_id' => $this->rtId,
            'status_kk' => $this->statusKk,
            'tanggal_terbentuk' => $this->tanggalTerbentuk->format('Y-m-d'),
            'updated_by' => auth()->id(),
        ];
    }
}
