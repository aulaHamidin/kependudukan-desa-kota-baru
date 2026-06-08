<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Rt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'event_type_code' => $this->faker->randomElement(['KELAHIRAN', 'KEMATIAN', 'PINDAH', 'DATANG']),
            'penduduk_id' => null,
            'event_date' => $this->faker->date(),
            'keterangan' => $this->faker->optional()->sentence(),
            'rt_id' => Rt::factory(),
            'rw_id' => null,
            'desa_id' => null,
            'kk_id' => null,
            'status_data' => 'DRAFT',
            'created_by' => User::factory(),
            'verified_by' => null,
            'voided_by' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Event $event) {
            if ($event->rt_id && ($event->rw_id === null || $event->desa_id === null)) {
                $rt = Rt::with('rw')->find($event->rt_id);
                if ($rt && $rt->rw) {
                    $event->rw_id = $event->rw_id ?? $rt->rw_id;
                    $event->desa_id = $event->desa_id ?? $rt->rw->desa_id;
                }
            }
        });
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_data' => 'DRAFT',
            'verified_by' => null,
            'voided_by' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_data' => 'VERIFIED',
            'verified_by' => User::factory(),
            'voided_by' => null,
        ]);
    }

    public function void(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_data' => 'VOID',
            'verified_by' => User::factory(),
            'voided_by' => User::factory(),
        ]);
    }
}
