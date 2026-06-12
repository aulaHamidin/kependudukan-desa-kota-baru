<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventDatang;
use App\Models\EventKelahiran;
use App\Models\EventKematian;
use App\Models\EventPindah;
use App\Models\JenisSurat;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Database\Seeder;

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

        $this->verifyEventsForCurrentYear();
        $this->createSuratTerbitSamples($penduduks, $kks);
    }

    private function verifyEventsForCurrentYear(): void
    {
        $verifierId = User::query()->value('id');

        Event::query()
            ->orderBy('id')
            ->get()
            ->each(function (Event $event, int $index) use ($verifierId): void {
                $event->forceFill([
                    'event_date' => now()->startOfYear()->addMonths($index % 12)->addDays($index % 20)->toDateString(),
                    'status_data' => 'VERIFIED',
                    'verified_by' => $verifierId,
                    'verified_at' => now(),
                ])->save();
            });
    }

    private function createSuratTerbitSamples($penduduks, $kks): void
    {
        $jenisSuratCodes = JenisSurat::query()->pluck('kode')->values();
        $creatorId = User::query()->value('id');

        if ($penduduks->isEmpty() || $kks->isEmpty() || $jenisSuratCodes->isEmpty() || $creatorId === null) {
            return;
        }

        foreach (range(0, 5) as $index) {
            $penduduk = $penduduks[$index % $penduduks->count()];
            $kk = $kks[$index % $kks->count()];
            $rt = Rt::with('rw')->find($kk->rt_id);

            if ($rt === null || $rt->rw === null) {
                continue;
            }

            SuratTerbit::create([
                'nomor_surat' => sprintf('%03d/%s/%s/%d', $index + 1, $jenisSuratCodes[$index % $jenisSuratCodes->count()], now()->format('m'), now()->year),
                'jenis_surat_kode' => $jenisSuratCodes[$index % $jenisSuratCodes->count()],
                'penduduk_id' => $penduduk->id,
                'tanggal_terbit' => now()->startOfMonth()->addDays($index)->toDateString(),
                'keperluan' => 'Data contoh halaman utama',
                'keterangan_tambahan' => null,
                'data_surat' => [
                    'nama_lengkap' => $penduduk->nama_lengkap,
                    'nik' => $penduduk->nik,
                    'alamat' => $kk->alamat,
                ],
                'file_path' => null,
                'pdf_status' => 'READY',
                'rt_id' => $rt->id,
                'rw_id' => $rt->rw_id,
                'desa_id' => $rt->rw->desa_id,
                'kk_id' => $kk->id,
                'masa_berlaku_hari' => 30,
                'tanggal_kadaluarsa' => now()->addDays($index + 1)->toDateString(),
                'status' => 'AKTIF',
                'alasan_batal' => null,
                'cancelled_by' => null,
                'cancelled_at' => null,
                'created_by' => $creatorId,
            ]);
        }
    }
}
