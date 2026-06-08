<?php

namespace Database\Factories;

use App\Models\Desa;
use App\Models\Rw;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rw>
 */
class RwFactory extends Factory
{
    protected $model = Rw::class;

    public function definition(): array
    {
        return [
            'desa_id' => Desa::factory(),
            'nomor_rw' => $this->faker->unique()->numerify('###'),
            'nama_ketua' => $this->faker->name(),
            'no_hp_ketua' => $this->faker->phoneNumber(),
        ];
    }
}
