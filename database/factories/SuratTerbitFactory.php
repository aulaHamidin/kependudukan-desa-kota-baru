<?php

namespace Database\Factories;

use App\Models\SuratTerbit;
use App\Models\JenisSurat;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\Desa;
use App\Models\KartuKeluarga;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuratTerbit>
 */
class SuratTerbitFactory extends Factory
{
    protected $model = SuratTerbit::class;

    public function definition(): array
    {
        $tanggalTerbit = $this->faker->dateTimeBetween('-1 year', 'now');
        $masaBerlaku = $this->faker->randomElement([null, 30, 60, 90, 180, 365]);

        return [
            'nomor_surat'           => $this->faker->unique()->numerify('###/RT-##/####'),
            'jenis_surat_kode'      => JenisSurat::factory(),
            'penduduk_id'           => Penduduk::factory()->state(fn () => [
                'rt_id'      => Rt::factory(),
                'created_by' => User::factory(),
            ]),
            'tanggal_terbit'        => $tanggalTerbit,
            'keperluan'             => $this->faker->sentence(),
            'keterangan_tambahan'   => $this->faker->optional()->sentence(),
            'data_surat'            => [
                'nama_lengkap' => $this->faker->name(),
                'nik'          => $this->faker->numerify('################'),
                'alamat'       => $this->faker->address(),
            ],
            'file_path'             => $this->faker->optional()->filePath(),
            'pdf_status'            => $this->faker->randomElement(['PROCESSING', 'READY', 'FAILED']),
            'rt_id'                 => 1,
            'rw_id'                 => 1,
            'desa_id'               => 1,
            'kk_id'                 => KartuKeluarga::factory()->state(fn () => [
                'rt_id'      => Rt::factory(),
                'created_by' => User::factory(),
            ]),
            'masa_berlaku_hari'     => $masaBerlaku,
            'tanggal_kadaluarsa'    => $masaBerlaku
                ? date('Y-m-d', strtotime("+{$masaBerlaku} days", strtotime($tanggalTerbit->format('Y-m-d'))))
                : null,
            'status'                => 'AKTIF',
            'alasan_batal'          => null,
            'cancelled_by'          => null,
            'cancelled_at'          => null,
            'created_by'            => 1,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'AKTIF',
            'alasan_batal' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
        ]);
    }

    public function batal(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'BATAL',
            'alasan_batal' => $this->faker->sentence(),
            'cancelled_by' => User::factory(),
            'cancelled_at' => now(),
        ]);
    }

    public function withPdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path'  => 'surat/' . $this->faker->uuid() . '.pdf',
            'pdf_status' => 'READY',
        ]);
    }

    public function pdfPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path'  => null,
            'pdf_status' => 'PROCESSING',
        ]);
    }

    public function pdfProcessing(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path'  => null,
            'pdf_status' => 'PROCESSING',
        ]);
    }

    public function pdfFailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_path'  => null,
            'pdf_status' => 'FAILED',
        ]);
    }

    public function withExpiry(int $days): static
    {
        $tanggalTerbit = $this->faker->dateTimeBetween('-1 year', 'now');

        return $this->state(fn (array $attributes) => [
            'masa_berlaku_hari'  => $days,
            'tanggal_kadaluarsa' => date('Y-m-d', strtotime("+{$days} days", strtotime($tanggalTerbit->format('Y-m-d')))),
        ]);
    }

    public function noExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_berlaku_hari'  => null,
            'tanggal_kadaluarsa' => null,
        ]);
    }
}
