<?php

namespace Database\Factories;

use App\Models\KkMember;
use App\Models\KartuKeluarga;
use App\Models\Penduduk;
use App\Models\HubunganKeluarga;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KkMember>
 */
class KkMemberFactory extends Factory
{
    protected $model = KkMember::class;

    public function definition(): array
    {
        return [
            'kartu_keluarga_id' => KartuKeluarga::factory(),
            'penduduk_id' => Penduduk::factory(),
            'hubungan_keluarga_code' => HubunganKeluarga::pluck('kode')->random(),
            'is_kepala_keluarga' => false,
            'tanggal_masuk' => $this->faker->date(),
            'tanggal_keluar' => null,
            'status' => 'AKTIF',
            'kk_asal_id' => null,
            'event_keluar_id' => null,
            'alasan_keluar' => null,
            'created_by' => 1,
        ];
    }

    public function kepalaKeluarga(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_kepala_keluarga' => true,
            'hubungan_keluarga_code' => 'KEPALA',
        ]);
    }
}
