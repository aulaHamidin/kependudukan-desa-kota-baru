<?php

namespace Database\Factories;

use App\Models\EventKelahiran;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventKelahiranFactory extends Factory
{
    protected $model = EventKelahiran::class;

    public function definition(): array
    {
        $jenisKelamin = $this->faker->randomElement(['L', 'P']);

        return [
            // Pastikan event parent bertipe KELAHIRAN
            'event_id' => Event::factory(['event_type_code' => 'KELAHIRAN']),
            'nama_bayi' => $this->faker->firstName($jenisKelamin === 'L' ? 'male' : 'female'),
            'jenis_kelamin' => $jenisKelamin,
            'status_kelahiran' => $this->faker->randomElement(['HIDUP', 'MATI']),
            'ayah_id' => null,
            'ibu_id' => null,
            'nama_ayah' => $this->faker->name('male'),
            'nama_ibu' => $this->faker->name('female'),
            'tempat_lahir' => $this->faker->randomElement([
                'Rumah Sakit', 'Puskesmas', 'Bidan', 'Rumah',
            ]),
            'jam_lahir' => $this->faker->time('H:i:s'),
            'anak_ke' => $this->faker->numberBetween(1, 5),
            'berat_badan_kg' => $this->faker->randomFloat(2, 2.5, 4.5),
            'panjang_badan_cm' => $this->faker->randomFloat(1, 45, 55),
            'penolong_kelahiran' => $this->faker->randomElement(['DOKTER', 'BIDAN', 'DUKUN', 'SENDIRI']),
            'nama_penolong' => $this->faker->name(),
        ];
    }
}