<?php

namespace Tests\Unit\Policies;

use App\Models\KartuKeluarga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class KartuKeluargaPolicyTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $territory      = $this->createTerritory();
        $otherTerritory = $this->createOtherTerritory();

        $this->desa    = $territory['desa'];
        $this->rw      = $territory['rw'];
        $this->rt      = $territory['rt'];
        $this->otherRt = $otherTerritory['rt'];

        $this->superAdmin = $this->superAdmin();
        $this->aDesa      = $this->adminDesa($this->desa);
        $this->aRw        = $this->adminRw($this->rw);
        $this->aRt        = $this->adminRt($this->rt);
        $this->aViewer    = $this->viewer($this->rt);

        $this->kkAktif = KartuKeluarga::factory()->create([
            'rt_id'      => $this->rt->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $this->aDesa->id,
        ]);
        $this->kkAktif->load('rt.rw');

        $this->kkNonAktif = KartuKeluarga::factory()->create([
            'rt_id'      => $this->rt->id,
            'status_kk'  => 'NON_AKTIF',
            'created_by' => $this->aDesa->id,
        ]);
        $this->kkNonAktif->load('rt.rw');

        $this->kkOtherRt = KartuKeluarga::factory()->create([
            'rt_id'      => $this->otherRt->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $this->aDesa->id,
        ]);
        $this->kkOtherRt->load('rt.rw');
    }

    public function test_semua_role_dapat_melihat_daftar_kk(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', KartuKeluarga::class));
        $this->assertTrue($this->aDesa->can('viewAny', KartuKeluarga::class));
        $this->assertTrue($this->aRw->can('viewAny', KartuKeluarga::class));
        $this->assertTrue($this->aRt->can('viewAny', KartuKeluarga::class));
        $this->assertTrue($this->aViewer->can('viewAny', KartuKeluarga::class));
    }

    public function test_super_admin_dapat_melihat_kk_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('view', $this->kkAktif));
        $this->assertTrue($this->superAdmin->can('view', $this->kkOtherRt));
    }

    public function test_admin_desa_dapat_melihat_kk_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('view', $this->kkAktif));
    }

    public function test_admin_desa_tidak_dapat_melihat_kk_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('view', $this->kkOtherRt));
    }

    public function test_admin_rw_dapat_melihat_kk_dalam_territorinya(): void
    {
        $this->assertTrue($this->aRw->can('view', $this->kkAktif));
    }

    public function test_admin_rw_tidak_dapat_melihat_kk_di_luar_territorinya(): void
    {
        $this->assertFalse($this->aRw->can('view', $this->kkOtherRt));
    }

    public function test_admin_rt_dapat_melihat_kk_dalam_rtnya(): void
    {
        $this->assertTrue($this->aRt->can('view', $this->kkAktif));
    }

    public function test_viewer_dapat_melihat_kk_sesuai_territory(): void
    {
        $this->assertTrue($this->aViewer->can('view', $this->kkAktif));
    }

    public function test_super_admin_tidak_dapat_membuat_kk(): void
    {
        $this->assertFalse($this->superAdmin->can('create', KartuKeluarga::class));
    }

    public function test_admin_desa_dapat_membuat_kk(): void
    {
        $this->assertTrue($this->aDesa->can('create', KartuKeluarga::class));
    }

    public function test_admin_rw_dapat_membuat_kk(): void
    {
        $this->assertTrue($this->aRw->can('create', KartuKeluarga::class));
    }

    public function test_admin_rt_dapat_membuat_kk(): void
    {
        $this->assertTrue($this->aRt->can('create', KartuKeluarga::class));
    }

    public function test_viewer_tidak_dapat_membuat_kk(): void
    {
        $this->assertFalse($this->aViewer->can('create', KartuKeluarga::class));
    }

    public function test_super_admin_tidak_dapat_mengubah_kk(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->kkAktif));
    }

    public function test_admin_desa_dapat_mengubah_kk(): void
    {
        $this->assertTrue($this->aDesa->can('update', $this->kkAktif));
    }

    public function test_admin_rw_dapat_mengubah_kk_dalam_territorinya(): void
    {
        $this->assertTrue($this->aRw->can('update', $this->kkAktif));
    }

    public function test_admin_rw_tidak_dapat_mengubah_kk_di_luar_territorinya(): void
    {
        $this->assertFalse($this->aRw->can('update', $this->kkOtherRt));
    }

    public function test_admin_rt_dapat_mengubah_kk_dalam_rtnya(): void
    {
        $this->assertTrue($this->aRt->can('update', $this->kkAktif));
    }

    public function test_admin_desa_tidak_dapat_mengubah_kk_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->kkOtherRt));
    }

    public function test_viewer_tidak_dapat_mengubah_kk(): void
    {
        $this->assertFalse($this->aViewer->can('update', $this->kkAktif));
    }

    // delete — KRITIS: admin_rt ALLOW tapi hanya NON_AKTIF
    public function test_super_admin_tidak_dapat_menghapus_kk(): void
    {
        $this->assertFalse($this->superAdmin->can('delete', $this->kkAktif));
        $this->assertFalse($this->superAdmin->can('delete', $this->kkNonAktif));
    }

    public function test_admin_desa_dapat_menghapus_kk_apapun_statusnya(): void
    {
        $this->assertTrue($this->aDesa->can('delete', $this->kkAktif));
        $this->assertTrue($this->aDesa->can('delete', $this->kkNonAktif));
    }

    public function test_admin_rw_dapat_menghapus_kk_non_aktif_dalam_territorinya(): void
    {
        $this->assertTrue($this->aRw->can('delete', $this->kkNonAktif));
    }

    public function test_admin_rw_tidak_dapat_menghapus_kk_aktif(): void
    {
        $this->assertFalse($this->aRw->can('delete', $this->kkAktif));
    }

    public function test_admin_rw_tidak_dapat_menghapus_kk_di_luar_territorinya(): void
    {
        $this->assertFalse($this->aRw->can('delete', $this->kkOtherRt));
    }

    public function test_admin_rt_dapat_menghapus_kk_non_aktif_dalam_rtnya(): void
    {
        $this->assertTrue($this->aRt->can('delete', $this->kkNonAktif));
    }

    public function test_admin_rt_tidak_dapat_menghapus_kk_aktif(): void
    {
        $this->assertFalse($this->aRt->can('delete', $this->kkAktif));
    }

    public function test_viewer_tidak_dapat_menghapus_kk(): void
    {
        $this->assertFalse($this->aViewer->can('delete', $this->kkAktif));
    }

    // pindah — hanya admin_desa, PindahRtPolicy DIHAPUS
    public function test_admin_desa_dapat_memindahkan_kk(): void
    {
        $this->assertTrue($this->aDesa->can('pindah', $this->kkAktif));
    }

    public function test_super_admin_tidak_dapat_memindahkan_kk(): void
    {
        $this->assertFalse($this->superAdmin->can('pindah', $this->kkAktif));
    }

    public function test_admin_rw_tidak_dapat_memindahkan_kk(): void
    {
        $this->assertFalse($this->aRw->can('pindah', $this->kkAktif));
    }

    public function test_admin_rt_tidak_dapat_memindahkan_kk(): void
    {
        $this->assertFalse($this->aRt->can('pindah', $this->kkAktif));
    }

    public function test_pindah_rt_policy_tidak_lagi_digunakan(): void
    {
        $this->assertFalse(class_exists(\App\Policies\PindahRtPolicy::class));
    }
}