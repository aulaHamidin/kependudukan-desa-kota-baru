<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\WelcomeStatsService;
use Database\Seeders\JenisSuratSeeder;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class WelcomeStatsServiceTest extends TestCase
{
    use PolicyTestHelper;
    use RefreshDatabase;

    private WelcomeStatsService $service;

    private int $actorId;

    private int $rtId;

    private int $rwId;

    private int $desaId;

    private int $nikCounter = 1;

    private int $kkCounter = 1;

    private int $suratCounter = 1;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));

        $this->seed(SeedMasterData::class);
        $this->seed(JenisSuratSeeder::class);

        $territory = $this->createTerritory();
        $this->desaId = $territory['desa']->id;
        $this->rwId = $territory['rw']->id;
        $this->rtId = $territory['rt']->id;
        $this->actorId = User::factory()->create([
            'role' => 'admin_desa',
            'desa_id' => $this->desaId,
        ])->id;

        $this->service = app(WelcomeStatsService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_get_public_stats_uses_real_database_aggregates(): void
    {
        $activeMaleId = $this->createPenduduk([
            'jenis_kelamin' => 'L',
            'tgl_lahir' => '2024-06-15',
            'status_kependudukan_code' => 'AKTIF',
        ]);
        $this->createPenduduk([
            'jenis_kelamin' => 'P',
            'tgl_lahir' => '1990-06-15',
            'status_kependudukan_code' => 'AKTIF',
        ]);
        $this->createPenduduk(['status_kependudukan_code' => 'PINDAH']);
        $this->createPenduduk(['status_kependudukan_code' => 'MENINGGAL']);
        $this->createPenduduk([
            'status_kependudukan_code' => 'AKTIF',
            'deleted_at' => now(),
        ]);

        $activeKkId = $this->createKartuKeluarga(['status_kk' => 'AKTIF']);
        $this->createKartuKeluarga(['status_kk' => 'NON_AKTIF']);
        $this->createKartuKeluarga([
            'status_kk' => 'AKTIF',
            'deleted_at' => now(),
        ]);

        $this->createEvent([
            'event_type_code' => 'KELAHIRAN',
            'event_date' => '2026-06-10',
            'status_data' => 'VERIFIED',
        ]);
        $this->createEvent([
            'event_type_code' => 'DATANG',
            'event_date' => '2026-01-05',
            'status_data' => 'VERIFIED',
        ]);
        $this->createEvent([
            'event_type_code' => 'PINDAH',
            'event_date' => '2026-06-11',
            'status_data' => 'DRAFT',
        ]);
        $this->createEvent([
            'event_type_code' => 'KEMATIAN',
            'event_date' => '2025-12-31',
            'status_data' => 'VERIFIED',
        ]);

        $this->createSurat($activeMaleId, $activeKkId, [
            'tanggal_terbit' => '2026-06-01',
            'tanggal_kadaluarsa' => '2026-06-15',
            'status' => 'AKTIF',
        ]);
        $this->createSurat($activeMaleId, $activeKkId, [
            'tanggal_terbit' => '2026-05-20',
            'tanggal_kadaluarsa' => null,
            'status' => 'AKTIF',
        ]);
        $this->createSurat($activeMaleId, $activeKkId, [
            'tanggal_terbit' => '2026-06-02',
            'tanggal_kadaluarsa' => '2026-06-20',
            'status' => 'BATAL',
        ]);
        $this->createSurat($activeMaleId, $activeKkId, [
            'tanggal_terbit' => '2026-06-03',
            'tanggal_kadaluarsa' => '2026-06-15',
            'status' => 'AKTIF',
            'deleted_at' => now(),
        ]);

        $stats = $this->service->getPublicStats();

        $this->assertSame([
            'total' => 4,
            'aktif' => 2,
            'pindah' => 1,
            'meninggal' => 1,
            'laki_laki' => 1,
            'perempuan' => 1,
        ], $stats['pendudukStats']);
        $this->assertSame(['total' => 2, 'aktif' => 1, 'non_aktif' => 1], $stats['kkStats']);
        $this->assertSame(['kelahiran' => 1, 'kematian' => 0, 'pindah' => 0, 'datang' => 1, 'total' => 2], $stats['eventStats']);
        $this->assertSame(['total_aktif' => 2, 'bulan_ini' => 1, 'akan_kadaluarsa' => 1, 'kadaluarsa_hari_ini' => 1], $stats['suratStats']);
        $this->assertSame(['0-4', '5-14', '15-24', '25-34', '35-44', '45-54', '55-64', '65+'], $stats['pendudukByAge']['labels']);
        $this->assertSame([1, 0, 0, 0, 1, 0, 0, 0], $stats['pendudukByAge']['data']);
        $this->assertSame(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], $stats['eventsByMonth']['labels']);
        $this->assertSame([1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0], $stats['eventsByMonth']['data']);
    }

    public function test_empty_database_returns_stable_chart_labels_with_zero_values(): void
    {
        $stats = $this->service->getPublicStats();

        $this->assertSame(['0-4', '5-14', '15-24', '25-34', '35-44', '45-54', '55-64', '65+'], $stats['pendudukByAge']['labels']);
        $this->assertSame(array_fill(0, 8, 0), $stats['pendudukByAge']['data']);
        $this->assertSame(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], $stats['eventsByMonth']['labels']);
        $this->assertSame(array_fill(0, 12, 0), $stats['eventsByMonth']['data']);
    }

    private function createPenduduk(array $overrides = []): int
    {
        $now = now();

        return (int) DB::table('penduduks')->insertGetId(array_merge([
            'nik' => str_pad((string) $this->nikCounter++, 16, '0', STR_PAD_LEFT),
            'nama_lengkap' => 'Penduduk Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Kota Test',
            'tgl_lahir' => '2000-01-01',
            'agama_id' => 'ISLAM',
            'pendidikan_id' => 'SD',
            'pekerjaan_id' => 'BELUM',
            'pendapatan_range_id' => null,
            'golongan_darah_id' => null,
            'kewarganegaraan' => 'WNI',
            'status_perkawinan' => 'Belum Kawin',
            'rt_id' => $this->rtId,
            'status_kependudukan_code' => 'AKTIF',
            'tanggal_status' => $now->toDateString(),
            'data_version' => 1,
            'created_by' => $this->actorId,
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ], $overrides));
    }

    private function createKartuKeluarga(array $overrides = []): int
    {
        $now = now();

        return (int) DB::table('kartu_keluargas')->insertGetId(array_merge([
            'no_kk' => str_pad((string) $this->kkCounter++, 16, '1', STR_PAD_LEFT),
            'alamat' => 'Alamat Test',
            'rt_id' => $this->rtId,
            'status_kk' => 'AKTIF',
            'tanggal_terbentuk' => '2026-01-01',
            'created_by' => $this->actorId,
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ], $overrides));
    }

    private function createEvent(array $overrides = []): void
    {
        $now = now();

        DB::table('events')->insert(array_merge([
            'event_type_code' => 'KELAHIRAN',
            'penduduk_id' => null,
            'event_date' => '2026-06-01',
            'keterangan' => null,
            'rt_id' => $this->rtId,
            'rw_id' => $this->rwId,
            'desa_id' => $this->desaId,
            'kk_id' => null,
            'status_data' => 'VERIFIED',
            'void_reason' => null,
            'void_at' => null,
            'voided_by' => null,
            'verified_by' => null,
            'verified_at' => $now,
            'created_by' => $this->actorId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $overrides));
    }

    private function createSurat(int $pendudukId, int $kkId, array $overrides = []): void
    {
        $now = now();

        DB::table('surat_terbit')->insert(array_merge([
            'nomor_surat' => str_pad((string) $this->suratCounter++, 3, '0', STR_PAD_LEFT).'/SKD/VI/2026',
            'jenis_surat_kode' => 'SKD',
            'tanggal_terbit' => '2026-06-01',
            'keperluan' => 'Keperluan Test',
            'keterangan_tambahan' => null,
            'data_surat' => null,
            'file_path' => null,
            'pdf_status' => 'READY',
            'penduduk_id' => $pendudukId,
            'kk_id' => $kkId,
            'rt_id' => $this->rtId,
            'rw_id' => $this->rwId,
            'desa_id' => $this->desaId,
            'masa_berlaku_hari' => null,
            'tanggal_kadaluarsa' => null,
            'status' => 'AKTIF',
            'alasan_batal' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'created_by' => $this->actorId,
            'updated_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ], $overrides));
    }
}
