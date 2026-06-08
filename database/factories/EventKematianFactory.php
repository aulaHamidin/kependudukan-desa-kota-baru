<?php

namespace Database\Factories;

use App\Models\EventKematian;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventKematianFactory extends Factory
{
    protected $model = EventKematian::class;

    public function definition(): array
    {
        return [
            // Pastikan event parent bertipe KEMATIAN
            'event_id' => Event::factory(['event_type_code' => 'KEMATIAN']),
            'tempat_meninggal' => $this->faker->randomElement([
                'Rumah Sakit', 'Rumah', 'Puskesmas', 'Dalam Perjalanan',
            ]),
            'jam_meninggal' => $this->faker->time('H:i:s'),
            'sebab_kematian' => $this->faker->randomElement([
                'SAKIT', 'KECELAKAAN', 'USIA_TUA', 'LAINNYA',
            ]),
            'penyakit' => $this->faker->optional(0.7)->randomElement([
                'Hipertensi', 'Diabetes Melitus', 'Stroke', 'Gagal Jantung', 'Kanker',
            ]),
            'keterangan_kematian' => $this->faker->optional()->sentence(),
            'was_kepala' => $this->faker->boolean(20), // 20% kemungkinan kepala keluarga
            'nama_pelapor' => $this->faker->name(),
            // Kode dari HubunganKeluarga seeder:  ANAK, SUAMI, ISTRI, ORANGTUA, MENANTU
            'hubungan_pelapor_code' => $this->faker->randomElement([
                'ANAK', 'SUAMI', 'ISTRI', 'ORANGTUA', 'MENANTU',
            ]),
        ];
    }
}