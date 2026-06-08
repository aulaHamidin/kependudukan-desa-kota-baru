<?php

declare(strict_types=1);

namespace Tests\Feature\TerritoryScope;

use App\Models\SuratTerbit;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

/**
 * Memverifikasi bahwa TerritoryScope pada model SuratTerbit benar-benar
 * membatasi akses data berdasarkan wilayah user yang login.
 *
 * SuratTerbit menggunakan applyTerritoryFilter() yang di-override
 * (direct column comparison: desa_id / rw_id / rt_id) berbeda dari
 * implementasi default HasTerritory yang memakai whereHas('rt.rw').
 * Test ini memverifikasi bahwa override tersebut bekerja dengan benar.
 */
class SuratTerbitTerritoryScopeTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private array $territory;
    private array $otherTerritory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->territory      = $this->createTerritory();
        $this->otherTerritory = $this->createOtherTerritory();
    }

    private function makeSurat(array $territory, array $overrides = []): SuratTerbit
    {
        $creator = $this->adminDesa($territory['desa']);

        return SuratTerbit::factory()->create(array_merge([
            'status'     => 'AKTIF',
            'rt_id'      => $territory['rt']->id,
            'rw_id'      => $territory['rw']->id,
            'desa_id'    => $territory['desa']->id,
            'created_by' => $creator->id,
        ], $overrides));
    }

    // ─── admin_rt ────────────────────────────────────────────────────────────

    public function test_admin_rt_hanya_melihat_surat_dari_rt_sendiri(): void
    {
        $suratMilikSaya  = $this->makeSurat($this->territory);
        $suratMilikLain  = $this->makeSurat($this->otherTerritory);

        $this->actingAs($this->adminRt($this->territory['rt']));

        $surat = SuratTerbit::all();

        $this->assertCount(1, $surat);
        $this->assertEquals($suratMilikSaya->id, $surat->first()->id);
        $this->assertFalse($surat->contains('id', $suratMilikLain->id));
    }

    public function test_admin_rt_tidak_bisa_lihat_surat_rt_lain_dalam_rw_sama(): void
    {
        $rtLain = \App\Models\Rt::factory()->create(['rw_id' => $this->territory['rw']->id]);

        $suratRtSaya = $this->makeSurat($this->territory);
        $suratRtLain = SuratTerbit::factory()->create([
            'status'     => 'AKTIF',
            'rt_id'      => $rtLain->id,
            'rw_id'      => $this->territory['rw']->id,
            'desa_id'    => $this->territory['desa']->id,
            'created_by' => $this->adminDesa($this->territory['desa'])->id,
        ]);

        $this->actingAs($this->adminRt($this->territory['rt']));

        $surat = SuratTerbit::all();

        $this->assertTrue($surat->contains('id', $suratRtSaya->id));
        $this->assertFalse($surat->contains('id', $suratRtLain->id));
    }

    // ─── admin_rw ────────────────────────────────────────────────────────────

    public function test_admin_rw_hanya_melihat_surat_dari_rw_sendiri(): void
    {
        $suratDesaSaya = $this->makeSurat($this->territory);
        $suratDesaLain = $this->makeSurat($this->otherTerritory);

        $this->actingAs($this->adminRw($this->territory['rw']));

        $surat = SuratTerbit::all();

        $this->assertTrue($surat->contains('id', $suratDesaSaya->id));
        $this->assertFalse($surat->contains('id', $suratDesaLain->id));
    }

    // ─── admin_desa ───────────────────────────────────────────────────────────

    public function test_admin_desa_hanya_melihat_surat_dari_desa_sendiri(): void
    {
        $suratDesaSaya = $this->makeSurat($this->territory);
        $suratDesaLain = $this->makeSurat($this->otherTerritory);

        $this->actingAs($this->adminDesa($this->territory['desa']));

        $surat = SuratTerbit::all();

        $this->assertTrue($surat->contains('id', $suratDesaSaya->id));
        $this->assertFalse($surat->contains('id', $suratDesaLain->id));
    }

    public function test_admin_desa_melihat_semua_surat_dari_rt_berbeda_dalam_desa_sendiri(): void
    {
        $rwLain = \App\Models\Rw::factory()->create(['desa_id' => $this->territory['desa']->id]);
        $rtLain = \App\Models\Rt::factory()->create(['rw_id' => $rwLain->id]);

        $suratRt1 = $this->makeSurat($this->territory);
        $suratRt2 = SuratTerbit::factory()->create([
            'status'     => 'AKTIF',
            'rt_id'      => $rtLain->id,
            'rw_id'      => $rwLain->id,
            'desa_id'    => $this->territory['desa']->id,
            'created_by' => $this->adminDesa($this->territory['desa'])->id,
        ]);

        $this->actingAs($this->adminDesa($this->territory['desa']));

        $surat = SuratTerbit::all();

        $this->assertTrue($surat->contains('id', $suratRt1->id));
        $this->assertTrue($surat->contains('id', $suratRt2->id));
    }

    // ─── super_admin ──────────────────────────────────────────────────────────

    public function test_super_admin_melihat_semua_surat_lintas_desa(): void
    {
        $suratDesaA = $this->makeSurat($this->territory);
        $suratDesaB = $this->makeSurat($this->otherTerritory);

        $this->actingAs($this->superAdmin());

        $surat = SuratTerbit::all();

        $this->assertTrue($surat->contains('id', $suratDesaA->id));
        $this->assertTrue($surat->contains('id', $suratDesaB->id));
    }

    // ─── scopeBenarBenarAktif ─────────────────────────────────────────────────

    public function test_scope_benar_benar_aktif_mengecualikan_surat_yang_sudah_lewat_kadaluarsa(): void
    {
        $this->actingAs($this->adminDesa($this->territory['desa']));

        // Surat aktif dengan masa berlaku belum habis
        $suratAktif = $this->makeSurat($this->territory, [
            'tanggal_kadaluarsa' => today()->addDays(30),
        ]);

        // Surat aktif tapi secara aktual sudah kadaluarsa (scheduler belum jalan)
        $suratKadaluarsa = $this->makeSurat($this->territory, [
            'tanggal_kadaluarsa' => today()->subDay(),
        ]);

        // Surat tanpa masa berlaku (berlaku selamanya)
        $suratTanpaMasaBerlaku = $this->makeSurat($this->territory, [
            'tanggal_kadaluarsa' => null,
        ]);

        $result = SuratTerbit::benarBenarAktif()->get();

        $this->assertTrue($result->contains('id', $suratAktif->id));
        $this->assertFalse($result->contains('id', $suratKadaluarsa->id));
        $this->assertTrue($result->contains('id', $suratTanpaMasaBerlaku->id));
    }
}
