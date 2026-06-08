<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Import;

use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Services\Import\PendudukImportService;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class PendudukImportServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private PendudukImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(PendudukImportService::class);
    }

    // =========================================================================
    // HELPER: Build row data
    // =========================================================================

    private function validRow(int $rtId, string $noKk, string $nik, string $hubungan = 'KEPALA_KELUARGA', array $override = []): array
    {
        return array_merge([
            'no_kk'             => $noKk,
            'rt_id'             => $rtId,
            'hubungan_keluarga' => $hubungan,
            'nik'               => $nik,
            'nama_lengkap'      => 'Nama Test',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Bandung',
            'tgl_lahir'         => '1990-01-01',
            'agama'             => 'ISLAM',
            'status_perkawinan' => 'BELUM_KAWIN',
            'alamat_asal'       => 'Jl. Asal No. 1',
            'alamat_kk'         => 'Jl. KK No. 1',
        ], $override);
    }

    // =========================================================================
    // ROW FILTERING
    // =========================================================================

    public function test_baris_tanpa_nik_dan_no_kk_dilewati_tidak_dihitung_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            ['nik' => '', 'no_kk' => '', 'nama_lengkap' => 'kosong'],
            ['nik' => null, 'no_kk' => null, 'nama_lengkap' => ''],
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertEquals(0, $result['summary']['total_rows']);
        $this->assertEmpty($result['errors']);
    }

    public function test_baris_dengan_salah_satu_nik_atau_no_kk_diproses_dan_error_muncul(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        // Hanya ada NIK tanpa no_kk → baris ini harus diproses
        $rows = collect([
            ['nik' => '1234567890123456', 'no_kk' => '', 'nama_lengkap' => 'Test'],
        ]);

        $result = $this->service->validate($rows, $actor);

        // Baris diproses (total_rows = 1), meskipun ada error
        $this->assertEquals(1, $result['summary']['total_rows']);
    }

    // =========================================================================
    // PER-ROW VALIDATION
    // =========================================================================

    public function test_nik_tidak_16_digit_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '12345', 'KEPALA_KELUARGA'),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $this->assertContains('nik', array_column($result['errors'][7] ?? [], 'column'));
    }

    public function test_nik_yang_sudah_ada_di_db_aktif_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        // Buat penduduk aktif dengan NIK tertentu
        Penduduk::factory()->create([
            'nik'                      => '1234567890123456',
            'rt_id'                    => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456'),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $nikErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'nik' && str_contains($e['message'], 'sudah terdaftar')
        );
        $this->assertNotEmpty($nikErrors);
    }

    public function test_nik_duplikat_dalam_file_menghasilkan_error_dengan_nomor_baris_referensi(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'KEPALA_KELUARGA'),
            $this->validRow($territory['rt']->id, '2222222222222222', '1234567890123456', 'KEPALA_KELUARGA'), // Duplikat NIK
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        // Row 8 (second row) should have duplicate NIK error
        $nikErrors = array_filter(
            $result['errors'][8] ?? [],
            fn($e) => $e['column'] === 'nik' && str_contains($e['message'], 'duplikat')
        );
        $this->assertNotEmpty($nikErrors);
    }

    public function test_rt_tidak_termasuk_wilayah_admin_desa_menghasilkan_error(): void
    {
        $territory      = $this->createTerritory();
        $otherTerritory = $this->createTerritory();
        $actor          = $this->adminDesa($territory['desa']);

        // RT dari desa lain
        $rows = collect([
            $this->validRow($otherTerritory['rt']->id, '1111111111111111', '1234567890123456'),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $rtErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'rt_id'
        );
        $this->assertNotEmpty($rtErrors);
    }

    public function test_jenis_kelamin_tidak_valid_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'KEPALA_KELUARGA', [
                'jenis_kelamin' => 'X', // Tidak valid
            ]),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $jkErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'jenis_kelamin'
        );
        $this->assertNotEmpty($jkErrors);
    }

    public function test_tanggal_lahir_masa_depan_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'KEPALA_KELUARGA', [
                'tgl_lahir' => now()->addYear()->format('Y-m-d'),
            ]),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $tglErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'tgl_lahir'
        );
        $this->assertNotEmpty($tglErrors);
    }

    public function test_no_hp_duplikat_di_db_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        Penduduk::factory()->create([
            'rt_id'                    => $territory['rt']->id,
            'no_hp'                    => '08123456789',
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'KEPALA_KELUARGA', [
                'no_hp' => '08123456789',
            ]),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $hpErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'no_hp'
        );
        $this->assertNotEmpty($hpErrors);
    }

    // =========================================================================
    // KK GROUP VALIDATION — NEW KK
    // =========================================================================

    public function test_kk_baru_tanpa_kepala_keluarga_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'ANAK'), // Bukan kepala
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $kepalaErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'hubungan_keluarga'
        );
        $this->assertNotEmpty($kepalaErrors);
    }

    public function test_kk_baru_lebih_dari_satu_kepala_keluarga_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'KEPALA_KELUARGA'),
            $this->validRow($territory['rt']->id, '1111111111111111', '6543210987654321', 'KEPALA_KELUARGA'), // Duplikat kepala
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
    }

    public function test_kk_baru_tanpa_alamat_kk_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'KEPALA_KELUARGA', [
                'alamat_kk' => '', // Kosong
            ]),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $alamatErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'alamat_kk'
        );
        $this->assertNotEmpty($alamatErrors);
    }

    // =========================================================================
    // KK GROUP VALIDATION — EXISTING AKTIF KK
    // =========================================================================

    public function test_kk_existing_aktif_rt_berbeda_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $existingKk = KartuKeluarga::factory()->create([
            'no_kk'      => '1111111111111111',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $actor->id,
        ]);

        // Buat kepala KK yang sudah ada
        $kepala = Penduduk::factory()->create([
            'rt_id'                    => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);
        KkMember::factory()->create([
            'kartu_keluarga_id'  => $existingKk->id,
            'penduduk_id'        => $kepala->id,
            'is_kepala_keluarga' => true,
            'status'             => 'AKTIF',
            'created_by'         => $actor->id,
        ]);

        // Buat RT lain dalam desa yang sama untuk allowed list
        $rw2 = \App\Models\Rw::factory()->create(['desa_id' => $territory['desa']->id]);
        $rt2 = \App\Models\Rt::factory()->create(['rw_id' => $rw2->id]);

        $rows = collect([
            $this->validRow($rt2->id, '1111111111111111', '1234567890123456', 'ANAK'), // RT berbeda dari KK
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $rtErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'rt_id'
        );
        $this->assertNotEmpty($rtErrors);
    }

    public function test_kk_existing_aktif_sudah_punya_kepala_dan_file_kirim_kepala_menghasilkan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $existingKk = KartuKeluarga::factory()->create([
            'no_kk'      => '1111111111111111',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $actor->id,
        ]);
        $kepala = Penduduk::factory()->create([
            'rt_id'                    => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);
        KkMember::factory()->create([
            'kartu_keluarga_id'  => $existingKk->id,
            'penduduk_id'        => $kepala->id,
            'is_kepala_keluarga' => true,
            'status'             => 'AKTIF',
            'created_by'         => $actor->id,
        ]);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456', 'KEPALA_KELUARGA'),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertFalse($result['valid']);
        $kepalaErrors = array_filter(
            $result['errors'][7] ?? [],
            fn($e) => $e['column'] === 'hubungan_keluarga' && str_contains($e['message'], 'sudah memiliki kepala')
        );
        $this->assertNotEmpty($kepalaErrors);
    }

    // =========================================================================
    // VALID CASE
    // =========================================================================

    public function test_satu_baris_valid_menghasilkan_result_valid_dan_zero_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456'),
        ]);

        $result = $this->service->validate($rows, $actor);

        $this->assertTrue($result['valid']);
        $this->assertEquals(0, $result['summary']['error_count']);
        $this->assertEquals(1, $result['summary']['total_rows']);
        $this->assertEquals(1, $result['summary']['new_kk_count']);
    }

    // =========================================================================
    // EXECUTE TESTS
    // =========================================================================

    public function test_execute_membuat_kk_penduduk_event_datang_dan_kk_member(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456'),
        ]);

        $result = $this->service->execute($rows, $actor);

        $this->assertEquals(1, $result['imported_count']);

        $this->assertDatabaseHas('kartu_keluargas', ['no_kk' => '1111111111111111', 'status_kk' => 'AKTIF']);
        $this->assertDatabaseHas('penduduks', ['nik' => '1234567890123456', 'status_kependudukan_code' => 'AKTIF']);

        $penduduk = Penduduk::where('nik', '1234567890123456')->first();
        $kk       = KartuKeluarga::where('no_kk', '1111111111111111')->first();

        $this->assertDatabaseHas('kk_members', [
            'kartu_keluarga_id'  => $kk->id,
            'penduduk_id'        => $penduduk->id,
            'is_kepala_keluarga' => true,
            'status'             => 'AKTIF',
        ]);

        $this->assertDatabaseHas('events', [
            'penduduk_id'     => $penduduk->id,
            'event_type_code' => 'DATANG',
            'status_data'     => 'DRAFT',
        ]);
    }

    public function test_execute_kk_existing_non_aktif_direaktivasi(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        // KK yang sudah ada tapi NON_AKTIF tanpa member
        $existingKk = KartuKeluarga::factory()->create([
            'no_kk'      => '1111111111111111',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'NON_AKTIF',
            'created_by' => $actor->id,
        ]);

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456'),
        ]);

        $this->service->execute($rows, $actor);

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $existingKk->id,
            'status_kk' => 'AKTIF',
        ]);
    }

    public function test_execute_nik_di_soft_deleted_direstore_tidak_dibuat_baru(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        // Buat penduduk yang sudah soft-deleted dengan NIK ini
        $pendudukLama = Penduduk::factory()->create([
            'nik'                      => '1234567890123456',
            'rt_id'                    => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);
        $pendudukLamaId = $pendudukLama->id;
        $pendudukLama->delete();

        $rows = collect([
            $this->validRow($territory['rt']->id, '1111111111111111', '1234567890123456'),
        ]);

        $this->service->execute($rows, $actor);

        // Record lama di-restore
        $this->assertDatabaseHas('penduduks', [
            'id'                       => $pendudukLamaId,
            'status_kependudukan_code' => 'AKTIF',
        ]);

        // Tidak ada duplikat NIK
        $this->assertEquals(1, Penduduk::where('nik', '1234567890123456')->count());
    }
}
