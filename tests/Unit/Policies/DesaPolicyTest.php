<?php

namespace Tests\Unit\Policies;

use App\Models\Desa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class DesaPolicyTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    // -------------------------------------------------------
    // viewAny
    // -------------------------------------------------------

    public function test_super_admin_dapat_melihat_daftar_desa(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', Desa::class));
    }

    public function test_admin_desa_dapat_melihat_daftar_desa(): void
    {
        $this->assertTrue($this->aDesa->can('viewAny', Desa::class));
    }

    public function test_admin_rw_dapat_melihat_daftar_desa(): void
    {
        $this->assertTrue($this->aRw->can('viewAny', Desa::class));
    }

    public function test_admin_rt_dapat_melihat_daftar_desa(): void
    {
        $this->assertTrue($this->aRt->can('viewAny', Desa::class));
    }

    public function test_viewer_dapat_melihat_daftar_desa(): void
    {
        $this->assertTrue($this->aViewer->can('viewAny', Desa::class));
    }

    // -------------------------------------------------------
    // view
    // -------------------------------------------------------

    public function test_super_admin_dapat_melihat_detail_desa_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('view', $this->desa));
        $this->assertTrue($this->superAdmin->can('view', $this->otherDesa));
    }

    public function test_admin_desa_dapat_melihat_desanya_sendiri(): void
    {
        $this->assertTrue($this->aDesa->can('view', $this->desa));
    }

    public function test_admin_desa_tidak_dapat_melihat_desa_lain(): void
    {
        $this->assertFalse($this->aDesa->can('view', $this->otherDesa));
    }

    public function test_admin_rw_dapat_melihat_desa_via_relasi(): void
    {
        $this->assertTrue($this->aRw->can('view', $this->desa));
    }

    public function test_admin_rw_tidak_dapat_melihat_desa_di_luar_wilayahnya(): void
    {
        $this->assertFalse($this->aRw->can('view', $this->otherDesa));
    }

    public function test_admin_rt_dapat_melihat_desa_via_relasi(): void
    {
        $this->assertTrue($this->aRt->can('view', $this->desa));
    }

    public function test_viewer_dapat_melihat_desa_sesuai_territory(): void
    {
        $this->assertTrue($this->aViewer->can('view', $this->desa));
    }

    // -------------------------------------------------------
    // create
    // -------------------------------------------------------

    public function test_super_admin_dapat_membuat_desa_baru(): void
    {
        $this->assertTrue($this->superAdmin->can('create', Desa::class));
    }

    public function test_admin_desa_tidak_dapat_membuat_desa(): void
    {
        $this->assertFalse($this->aDesa->can('create', Desa::class));
    }

    public function test_admin_rw_tidak_dapat_membuat_desa(): void
    {
        $this->assertFalse($this->aRw->can('create', Desa::class));
    }

    public function test_admin_rt_tidak_dapat_membuat_desa(): void
    {
        $this->assertFalse($this->aRt->can('create', Desa::class));
    }

    public function test_viewer_tidak_dapat_membuat_desa(): void
    {
        $this->assertFalse($this->aViewer->can('create', Desa::class));
    }

    // -------------------------------------------------------
    // update
    // -------------------------------------------------------

    public function test_super_admin_dapat_mengubah_desa_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('update', $this->desa));
    }

    public function test_admin_desa_tidak_dapat_mengubah_desa(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->desa));
    }

    public function test_admin_rw_tidak_dapat_mengubah_desa(): void
    {
        $this->assertFalse($this->aRw->can('update', $this->desa));
    }

    public function test_admin_rt_tidak_dapat_mengubah_desa(): void
    {
        $this->assertFalse($this->aRt->can('update', $this->desa));
    }

    public function test_viewer_tidak_dapat_mengubah_desa(): void
    {
        $this->assertFalse($this->aViewer->can('update', $this->desa));
    }

    // -------------------------------------------------------
    // delete
    // -------------------------------------------------------

    public function test_super_admin_dapat_menghapus_desa(): void
    {
        $this->assertTrue($this->superAdmin->can('delete', $this->desa));
    }

    public function test_admin_desa_tidak_dapat_menghapus_desa(): void
    {
        $this->assertFalse($this->aDesa->can('delete', $this->desa));
    }

    public function test_admin_rw_tidak_dapat_menghapus_desa(): void
    {
        $this->assertFalse($this->aRw->can('delete', $this->desa));
    }

    public function test_admin_rt_tidak_dapat_menghapus_desa(): void
    {
        $this->assertFalse($this->aRt->can('delete', $this->desa));
    }

    public function test_viewer_tidak_dapat_menghapus_desa(): void
    {
        $this->assertFalse($this->aViewer->can('delete', $this->desa));
    }
}