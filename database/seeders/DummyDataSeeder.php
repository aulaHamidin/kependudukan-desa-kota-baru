<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agama;
use App\Models\Pendidikan;
use App\Models\Pekerjaan;
use App\Models\GolonganDarah;
use App\Models\PendapatanRange;
use App\Models\StatusKependudukan;
use App\Models\EventType;
use App\Models\HubunganKeluarga;
use App\Models\Desa;
use App\Models\Rw;
use App\Models\Rt;
use App\Models\User;
use App\Models\JenisSurat;
use App\Models\Penduduk;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Event;
use App\Models\EventDatang;
use App\Models\EventKelahiran;
use App\Models\EventKematian;
use App\Models\EventPindah;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $penduduks = Penduduk::factory(15)->create();
        $kks = KartuKeluarga::factory(15)->create();

        foreach ($penduduks as $index => $penduduk) {
            KkMember::factory()->create([
                'penduduk_id' => $penduduk->id,
                'kartu_keluarga_id' => $kks[$index]->id ?? $kks[0]->id,
            ]);
        }
        EventDatang::factory(4)->create();
        EventKelahiran::factory(4)->create();
        EventKematian::factory(4)->create();
        EventPindah::factory(3)->create();
    }
}
