<?php

namespace Database\Factories;

use App\Models\JenisSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JenisSurat>
 */
class JenisSuratFactory extends Factory
{
    protected $model = JenisSurat::class;

    public function definition(): array
    {
        $kode = strtoupper($this->faker->unique()->lexify('???'));
        
        return [
            'kode' => $kode,
            'nama' => $this->faker->sentence(3),
            'deskripsi' => $this->faker->sentence(),
            'template_category' => $this->faker->randomElement(['keterangan', 'pengantar', 'izin', 'pernyataan', 'rekomendasi', 'internal']),
            'template_sections' => [
                'intro' => 'Yang bertanda tangan di bawah ini',
                'body' => 'Menerangkan bahwa',
                'fields' => ['nama_lengkap', 'nik', 'alamat'],
                'signature_type' => $this->faker->randomElement(['kepala_desa', 'sekdes', 'kasi', 'dual']),
            ],
            'prefix_nomor' => $kode,
            'format_nomor' => '{prefix}/{seq}/{rt}/{tahun}',
            'masa_berlaku_hari' => $this->faker->randomElement([null, 30, 60, 90, 180, 365]),
            'is_active' => true,
            'keterangan' => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withExpiry(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_berlaku_hari' => $days,
        ]);
    }

    public function noExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_berlaku_hari' => null,
        ]);
    }
}
