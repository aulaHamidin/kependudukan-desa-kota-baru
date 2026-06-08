<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Penduduk;
use App\Services\PendudukService;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class PendudukServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private PendudukService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(PendudukService::class);
    }

    // =========================================================================
    // updatePenduduk
    // =========================================================================

    public function test_update_penduduk_nik_baru_ada_di_penduduk_aktif_lain_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $penduduk1 = Penduduk::factory()->create([
            'rt_id'      => $territory['rt']->id,
            'nik'        => '1111111111111111',
            'created_by' => $actor->id,
        ]);

        $penduduk2 = Penduduk::factory()->create([
            'rt_id'      => $territory['rt']->id,
            'nik'        => '2222222222222222',
            'created_by' => $actor->id,
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('NIK sudah terdaftar');

        $this->service->updatePenduduk($penduduk2->id, [
            'nik' => '1111111111111111',
        ]);
    }

    public function test_update_penduduk_nik_baru_ada_di_soft_deleted_me_restore_record_lama(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        // NIK lama yang sudah soft-deleted
        $pendudukLama = Penduduk::factory()->create([
            'rt_id'      => $territory['rt']->id,
            'nik'        => '1111111111111111',
            'created_by' => $actor->id,
        ]);
        $pendudukLamaId = $pendudukLama->id;
        $pendudukLama->delete();

        // Penduduk aktif yang akan di-update NIK-nya
        $pendudukAktif = Penduduk::factory()->create([
            'rt_id'      => $territory['rt']->id,
            'nik'        => '2222222222222222',
            'created_by' => $actor->id,
        ]);

        $result = $this->service->updatePenduduk($pendudukAktif->id, [
            'nik' => '1111111111111111',
        ]);

        // Harus mengembalikan record lama yang di-restore
        $this->assertEquals($pendudukLamaId, $result->id);
        $this->assertDatabaseHas('penduduks', [
            'id'  => $pendudukLamaId,
            'nik' => '1111111111111111',
        ]);
        // Record aktif lama harus dihapus (soft-deleted)
        $this->assertSoftDeleted('penduduks', ['id' => $pendudukAktif->id]);
    }

    public function test_update_penduduk_nama_lengkap_berhasil(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $penduduk = Penduduk::factory()->create([
            'rt_id'        => $territory['rt']->id,
            'nama_lengkap' => 'Nama Lama',
            'created_by'   => $actor->id,
        ]);

        $result = $this->service->updatePenduduk($penduduk->id, [
            'nama_lengkap' => 'Nama Baru',
        ]);

        $this->assertEquals('Nama Baru', $result->nama_lengkap);
        $this->assertDatabaseHas('penduduks', [
            'id'           => $penduduk->id,
            'nama_lengkap' => 'Nama Baru',
        ]);
    }

    public function test_update_penduduk_tidak_ditemukan_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Penduduk tidak ditemukan');

        $this->service->updatePenduduk(99999, ['nama_lengkap' => 'Test']);
    }

    // =========================================================================
    // calculateDataCompleteness
    // =========================================================================

    public function test_calculate_data_completeness_semua_field_diisi_mengembalikan_100_persen(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $penduduk = Penduduk::factory()->create([
            'rt_id'      => $territory['rt']->id,
            'nama_ayah'  => 'Ayah Test',
            'nama_ibu'   => 'Ibu Test',
            'no_hp'      => '081234567890',
            'email'      => 'test@example.com',
            'created_by' => $actor->id,
        ]);

        // Pastikan semua field wajib & opsional terisi
        // (Factory sudah mengisi field wajib; pastikan optional juga ada)
        $penduduk->pendidikan_id     = \App\Models\Pendidikan::value('kode');
        $penduduk->pekerjaan_id      = \App\Models\Pekerjaan::value('kode');
        $penduduk->golongan_darah_id = \App\Models\GolonganDarah::value('kode');
        $penduduk->save();

        $result = $this->service->calculateDataCompleteness($penduduk->fresh());

        $this->assertEquals(100, $result['percentage']);
        $this->assertEmpty($result['missing']);
    }

    public function test_calculate_data_completeness_hanya_required_field_mengembalikan_70_persen(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        // Buat penduduk tanpa semua field optional
        $penduduk = Penduduk::factory()->create([
            'rt_id'             => $territory['rt']->id,
            'nama_ayah'         => null,
            'nama_ibu'          => null,
            'no_hp'             => null,
            'email'             => null,
            'pendidikan_id'     => null,
            'pekerjaan_id'      => null,
            'golongan_darah_id' => null,
            'created_by'        => $actor->id,
        ]);

        $result = $this->service->calculateDataCompleteness($penduduk->fresh());

        // 6 required terisi → 70%, 0 optional → 0%
        $this->assertEquals(70, $result['percentage']);
        $this->assertEquals(6, $result['required_filled']);
        $this->assertEquals(0, $result['optional_filled']);
    }

    public function test_calculate_data_completeness_required_plus_sebagian_optional_antara_70_dan_100(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $penduduk = Penduduk::factory()->create([
            'rt_id'             => $territory['rt']->id,
            'nama_ayah'         => 'Ayah',
            'nama_ibu'          => 'Ibu',
            'no_hp'             => '082222222222',
            // 4 optional lainnya null
            'email'             => null,
            'pendidikan_id'     => null,
            'pekerjaan_id'      => null,
            'golongan_darah_id' => null,
            'created_by'        => $actor->id,
        ]);

        $result = $this->service->calculateDataCompleteness($penduduk->fresh());

        // 6 required (70%) + 3/7 optional → tambahan ~(3/7)*30 ≈ 12.8 → ≈83%
        $this->assertGreaterThan(70, $result['percentage']);
        $this->assertLessThan(100, $result['percentage']);
        $this->assertEquals(6, $result['required_filled']);
        $this->assertEquals(3, $result['optional_filled']);
    }

    public function test_calculate_data_completeness_mengembalikan_struktur_lengkap(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $penduduk = Penduduk::factory()->create([
            'rt_id'      => $territory['rt']->id,
            'created_by' => $actor->id,
        ]);

        $result = $this->service->calculateDataCompleteness($penduduk);

        $this->assertArrayHasKey('percentage', $result);
        $this->assertArrayHasKey('filled', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('required_filled', $result);
        $this->assertArrayHasKey('required_total', $result);
        $this->assertArrayHasKey('optional_filled', $result);
        $this->assertArrayHasKey('optional_total', $result);
        $this->assertArrayHasKey('missing', $result);
        $this->assertEquals(6, $result['required_total']);
        $this->assertEquals(7, $result['optional_total']);
        $this->assertEquals(13, $result['total']);
    }
}
