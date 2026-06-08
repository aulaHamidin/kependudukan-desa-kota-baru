<?php

namespace Database\Factories;

use App\Models\EventPindah;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventPindahFactory extends Factory
{
    protected $model = EventPindah::class;

    public function definition(): array
    {
        return [
            // Pastikan event parent bertipe PINDAH
            'event_id' => Event::factory(['event_type_code' => 'PINDAH']),
            'alamat_tujuan' => $this->faker->streetAddress(),
            'rt_tujuan' => $this->faker->numerify('00#'),
            'rw_tujuan' => $this->faker->numerify('00#'),
            'desa_tujuan' => $this->faker->city(),
            'kecamatan_tujuan' => $this->faker->city(),
            'kabupaten_tujuan' => $this->faker->city(),
            'provinsi_tujuan' => $this->faker->randomElement([
                'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'DKI Jakarta', 'Banten',
            ]),
            'kode_pos_tujuan' => $this->faker->numerify('#####'),
            'alasan_pindah' => $this->faker->randomElement([
                'Pekerjaan', 'Ikut suami/istri', 'Mengikuti orang tua', 'Pendidikan', 'Lainnya',
            ]),
            'keterangan_alasan' => $this->faker->optional()->sentence(),
            'jenis_kepindahan' => $this->faker->randomElement(['KEPALA_KELUARGA', 'ANGGOTA_KELUARGA', 'SATU_KK']),
            'tanggal_pindah' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'was_kepala' => false,
        ];
    }
}