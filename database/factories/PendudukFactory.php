<?php

namespace Database\Factories;

use App\Models\Agama;
use App\Models\Pendidikan;
use App\Models\Pekerjaan;
use App\Models\PendapatanRange;
use App\Models\GolonganDarah;
use App\Models\Penduduk;
use App\Models\StatusKependudukan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penduduk>
 */
class PendudukFactory extends Factory
{
    protected $model = Penduduk::class;

    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('################'),
            'nama_lengkap' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'tempat_lahir' => $this->faker->city(),
            'tgl_lahir' => $this->faker->date(),
            'ayah_id' => null,
            'ibu_id' => null,
            'nama_ayah' => $this->faker->optional()->name('male'),
            'nama_ibu' => $this->faker->optional()->name('female'),
            'agama_id' => Agama::pluck('kode')->random(),
            'pendidikan_id' => Pendidikan::pluck('kode')->random(),
            'pekerjaan_id' => Pekerjaan::pluck('kode')->random(),
            'pendapatan_range_id' => PendapatanRange::pluck('id')->random(),
            'golongan_darah_id' => GolonganDarah::pluck('kode')->random(),
            'kewarganegaraan' => 'WNI',
            'no_paspor' => null,
            'status_perkawinan' => $this->faker->randomElement(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']),
            'no_hp' => $this->faker->boolean(70) ? $this->faker->phoneNumber() : null,
            'email' => $this->faker->boolean(50) ? $this->faker->unique()->safeEmail() : null,
            'rt_id' => '1',
            'status_kependudukan_code' => StatusKependudukan::pluck('kode')->random(),
            'current_event_id' => null,
            'tanggal_status' => $this->faker->date(),
            'data_version' => 1,
            'created_by' => 1,
            'updated_by' => null,
        ];
    }
}
