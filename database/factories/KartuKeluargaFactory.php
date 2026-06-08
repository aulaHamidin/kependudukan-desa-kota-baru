<?php

namespace Database\Factories;

use App\Models\KartuKeluarga;
use App\Models\Rt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KartuKeluarga>
 */
class KartuKeluargaFactory extends Factory
{
    protected $model = KartuKeluarga::class;

    public function definition(): array
    {
        return [
            'no_kk' => $this->faker->unique()->numerify('################'),
            'rt_id' => '1',
            'alamat' => $this->faker->streetAddress(),
            'status_kk' => $this->faker->randomElement(['AKTIF', 'NON_AKTIF']),
            'tanggal_terbentuk' => $this->faker->date(),
            'created_by' => 1,
            'updated_by' => null,
        ];
    }

    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_kk' => 'AKTIF',
        ]);
    }

    public function nonAktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_kk' => 'NON_AKTIF',
        ]);
    }
}
