<?php

namespace Tests\Unit\Policies;

use App\Models\KartuKeluarga;
use App\Models\Penduduk;
use App\Models\SuratTerbit;
use Database\Seeders\JenisSuratSeeder;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class SuratTerbitPolicyTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SeedMasterData::class);
        $this->seed(JenisSuratSeeder::class);

        $territory      = $this->createTerritory();
        $otherTerritory = $this->createOtherTerritory();

        $this->desa      = $territory['desa'];
        $this->rw        = $territory['rw'];
        $this->rt        = $territory['rt'];
        $this->otherDesa = $otherTerritory['desa'];

        $this->superAdmin = $this->superAdmin();
        $this->aDesa      = $this->adminDesa($this->desa);
        $this->aRw        = $this->adminRw($this->rw);
        $this->aRt        = $this->adminRt($this->rt);
        $this->aViewer    = $this->viewer($this->rt);

        $penduduk = Penduduk::factory()->create([
            'rt_id'      => $this->rt->id,
            'created_by' => $this->aDesa->id,
        ]);
        $kk = KartuKeluarga::factory()->create([
            'rt_id'      => $this->rt->id,
            'created_by' => $this->aDesa->id,
        ]);

        $this->surat = SuratTerbit::factory()->create([
            'desa_id'     => $this->desa->id,
            'rw_id'       => $this->rw->id,
            'rt_id'       => $this->rt->id,
            'penduduk_id' => $penduduk->id,
            'kk_id'       => $kk->id,
            'created_by'  => $this->aDesa->id,
            'pdf_status'  => 'READY',
            'status'      => 'AKTIF',
        ]);

        $otherPenduduk = Penduduk::factory()->create([
            'rt_id'      => $otherTerritory['rt']->id,
            'created_by' => $this->aDesa->id,
        ]);
        $otherKk = KartuKeluarga::factory()->create([
            'rt_id'      => $otherTerritory['rt']->id,
            'created_by' => $this->aDesa->id,
        ]);

        $this->suratOtherDesa = SuratTerbit::factory()->create([
            'desa_id'     => $this->otherDesa->id,
            'rw_id'       => $otherTerritory['rw']->id,
            'rt_id'       => $otherTerritory['rt']->id,
            'penduduk_id' => $otherPenduduk->id,
            'kk_id'       => $otherKk->id,
            'created_by'  => $this->aDesa->id,
            'pdf_status'  => 'READY',
            'status'      => 'AKTIF',
        ]);
    }

    public function test_super_admin_dapat_melihat_daftar_surat(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', SuratTerbit::class));
    }

    public function test_admin_desa_dapat_melihat_daftar_surat(): void
    {
        $this->assertTrue($this->aDesa->can('viewAny', SuratTerbit::class));
    }

    public function test_admin_rw_tidak_dapat_melihat_daftar_surat_modul_internal(): void
    {
        $this->assertFalse($this->aRw->can('viewAny', SuratTerbit::class));
    }

    public function test_admin_rt_tidak_dapat_melihat_daftar_surat(): void
    {
        $this->assertFalse($this->aRt->can('viewAny', SuratTerbit::class));
    }

    public function test_viewer_tidak_dapat_melihat_daftar_surat(): void
    {
        $this->assertFalse($this->aViewer->can('viewAny', SuratTerbit::class));
    }

    public function test_super_admin_dapat_melihat_detail_surat_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('view', $this->surat));
        $this->assertTrue($this->superAdmin->can('view', $this->suratOtherDesa));
    }

    public function test_admin_desa_dapat_melihat_detail_surat_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('view', $this->surat));
    }

    public function test_admin_desa_tidak_dapat_melihat_surat_desa_lain(): void
    {
        $this->assertFalse($this->aDesa->can('view', $this->suratOtherDesa));
    }

    public function test_admin_rw_tidak_dapat_melihat_detail_surat(): void
    {
        $this->assertFalse($this->aRw->can('view', $this->surat));
    }

    public function test_viewer_tidak_dapat_melihat_detail_surat(): void
    {
        $this->assertFalse($this->aViewer->can('view', $this->surat));
    }

    public function test_super_admin_tidak_dapat_membuat_surat(): void
    {
        $this->assertFalse($this->superAdmin->can('create', SuratTerbit::class));
    }

    public function test_admin_desa_dapat_membuat_surat(): void
    {
        $this->assertTrue($this->aDesa->can('create', SuratTerbit::class));
    }

    public function test_admin_rw_tidak_dapat_membuat_surat(): void
    {
        $this->assertFalse($this->aRw->can('create', SuratTerbit::class));
    }

    public function test_admin_rt_tidak_dapat_membuat_surat(): void
    {
        $this->assertFalse($this->aRt->can('create', SuratTerbit::class));
    }

    public function test_admin_desa_tidak_dapat_mengubah_surat_dalam_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->surat));
    }

    public function test_admin_desa_tidak_dapat_mengubah_surat_desa_lain(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->suratOtherDesa));
    }

    public function test_super_admin_tidak_dapat_mengubah_surat(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->surat));
    }

    public function test_super_admin_dapat_download_surat_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('download', $this->surat));
        $this->assertTrue($this->superAdmin->can('download', $this->suratOtherDesa));
    }

    public function test_admin_desa_dapat_download_surat_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('download', $this->surat));
    }

    public function test_admin_rw_tidak_dapat_download_surat(): void
    {
        $this->assertFalse($this->aRw->can('download', $this->surat));
    }

    public function test_admin_rt_tidak_dapat_download_surat(): void
    {
        $this->assertFalse($this->aRt->can('download', $this->surat));
    }

    public function test_viewer_tidak_dapat_download_surat(): void
    {
        $this->assertFalse($this->aViewer->can('download', $this->surat));
    }

    public function test_admin_desa_dapat_membatalkan_surat(): void
    {
        $this->assertTrue($this->aDesa->can('batalkan', $this->surat));
    }

    public function test_super_admin_tidak_dapat_membatalkan_surat(): void
    {
        $this->assertFalse($this->superAdmin->can('batalkan', $this->surat));
    }

    public function test_admin_rw_tidak_dapat_membatalkan_surat(): void
    {
        $this->assertFalse($this->aRw->can('batalkan', $this->surat));
    }

    // forceDelete — semua role DENY karena surat immutable
    public function test_admin_desa_dapat_regenerate_pdf_surat_aktif_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('regeneratePdf', $this->surat));
    }

    public function test_regenerate_pdf_ditolak_untuk_super_admin_rt_rw_viewer_dan_desa_lain(): void
    {
        $this->assertFalse($this->superAdmin->can('regeneratePdf', $this->surat));
        $this->assertFalse($this->aRw->can('regeneratePdf', $this->surat));
        $this->assertFalse($this->aRt->can('regeneratePdf', $this->surat));
        $this->assertFalse($this->aViewer->can('regeneratePdf', $this->surat));
        $this->assertFalse($this->aDesa->can('regeneratePdf', $this->suratOtherDesa));
    }

    public function test_regenerate_pdf_ditolak_untuk_surat_batal(): void
    {
        $this->surat->update(['status' => 'BATAL']);

        $this->assertFalse($this->aDesa->can('regeneratePdf', $this->surat));
    }

    public function test_admin_desa_tidak_dapat_hapus_permanen_surat(): void
    {
        $this->assertFalse($this->aDesa->can('forceDelete', $this->surat));
    }

    public function test_super_admin_tidak_dapat_hapus_permanen_surat(): void
    {
        $this->assertFalse($this->superAdmin->can('forceDelete', $this->surat));
    }

    public function test_admin_rw_tidak_dapat_hapus_permanen_surat(): void
    {
        $this->assertFalse($this->aRw->can('forceDelete', $this->surat));
    }
}
