<?php

namespace Database\Factories;

use App\Models\Desa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Desa>
 */
class DesaFactory extends Factory
{
    protected $model = Desa::class;

    public function definition(): array
    {
        return [
            'kode_desa' => $this->faker->unique()->numerify('##########'),
            'nama' => $this->faker->city(),
            'kecamatan' => $this->faker->city(),
            'kabupaten' => $this->faker->city(),
            'provinsi' => $this->faker->state(),
            'kode_pos' => $this->faker->postcode(),
        ];
    }
}
