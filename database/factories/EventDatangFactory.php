<?php

namespace Database\Factories;

use App\Models\EventDatang;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventDatangFactory extends Factory
{
    protected $model = EventDatang::class;

    public function definition(): array
    {
        return [
            // Pastikan event parent bertipe DATANG
            'event_id' => Event::factory(['event_type_code' => 'DATANG']),
            'alamat_asal' => $this->faker->address(),
            'rt_asal' => $this->faker->numerify('00#'),
            'rw_asal' => $this->faker->numerify('00#'),
            'desa_asal' => $this->faker->city(),
            'kecamatan_asal' => $this->faker->city(),
            'kabupaten_asal' => $this->faker->city(),
            'provinsi_asal' => $this->faker->randomElement([
                'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'DKI Jakarta', 'Sumatera Utara',
            ]),
            'alasan_datang' => $this->faker->randomElement([
                'Mengikuti suami/istri', 'Pekerjaan', 'Pendidikan', 'Ikut orang tua',
            ]),
            'keterangan_alasan' => $this->faker->optional()->sentence(),
            'jenis_kedatangan' => $this->faker->randomElement(['KEPALA_KELUARGA', 'ANGGOTA_KELUARGA']),
            'no_surat_pindah' => $this->faker->numerify('SURAT/####/2025'),
            'tanggal_surat_pindah' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}