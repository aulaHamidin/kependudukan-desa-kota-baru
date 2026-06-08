<?php

namespace Database\Factories;

use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rt>
 */
class RtFactory extends Factory
{
    protected $model = Rt::class;

    public function definition(): array
    {
        return [
            'rw_id' => Rw::factory(),
            'nomor_rt' => $this->faker->unique()->numerify('###'),
            'nama_ketua' => $this->faker->name(),
            'no_hp_ketua' => $this->faker->phoneNumber(),
        ];
    }
}
