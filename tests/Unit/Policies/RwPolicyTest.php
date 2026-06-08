<?php

namespace Tests\Unit\Policies;

use App\Models\Rw;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class RwPolicyTest extends TestCase
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
        $this->otherRw = $otherTerritory['rw'];

        $this->superAdmin = $this->superAdmin();
        $this->aDesa      = $this->adminDesa($this->desa);
        $this->aRw        = $this->adminRw($this->rw);
        $this->aRt        = $this->adminRt($this->rt);
        $this->aViewer    = $this->viewer($this->rt);
    }

    public function test_semua_role_dapat_melihat_daftar_rw(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', Rw::class));
        $this->assertTrue($this->aDesa->can('viewAny', Rw::class));
        $this->assertTrue($this->aRw->can('viewAny', Rw::class));
        $this->assertTrue($this->aRt->can('viewAny', Rw::class));
        $this->assertTrue($this->aViewer->can('viewAny', Rw::class));
    }

    public function test_super_admin_dapat_melihat_rw_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('view', $this->rw));
        $this->assertTrue($this->superAdmin->can('view', $this->otherRw));
    }

    public function test_admin_desa_dapat_melihat_rw_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('view', $this->rw));
    }

    public function test_admin_desa_tidak_dapat_melihat_rw_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('view', $this->otherRw));
    }

    public function test_admin_rw_dapat_melihat_rw_miliknya(): void
    {
        $this->assertTrue($this->aRw->can('view', $this->rw));
    }

    public function test_admin_rw_tidak_dapat_melihat_rw_lain(): void
    {
        $this->assertFalse($this->aRw->can('view', $this->otherRw));
    }

    public function test_admin_rt_dapat_melihat_rw_via_relasi(): void
    {
        $this->assertTrue($this->aRt->can('view', $this->rw));
    }

    // create — KRITIS: super_admin DENY
    public function test_super_admin_tidak_dapat_membuat_rw(): void
    {
        $this->assertFalse($this->superAdmin->can('create', Rw::class));
    }

    public function test_admin_desa_dapat_membuat_rw(): void
    {
        $this->assertTrue($this->aDesa->can('create', Rw::class));
    }

    public function test_admin_rw_tidak_dapat_membuat_rw(): void
    {
        $this->assertFalse($this->aRw->can('create', Rw::class));
    }

    public function test_admin_rt_tidak_dapat_membuat_rw(): void
    {
        $this->assertFalse($this->aRt->can('create', Rw::class));
    }

    public function test_viewer_tidak_dapat_membuat_rw(): void
    {
        $this->assertFalse($this->aViewer->can('create', Rw::class));
    }

    // update
    public function test_super_admin_tidak_dapat_mengubah_rw(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->rw));
    }

    public function test_admin_desa_dapat_mengubah_rw_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('update', $this->rw));
    }

    public function test_admin_desa_tidak_dapat_mengubah_rw_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->otherRw));
    }

    public function test_admin_rw_tidak_dapat_mengubah_rw_miliknya(): void
    {
        $this->assertFalse($this->aRw->can('update', $this->rw));
    }

    // delete
    public function test_super_admin_tidak_dapat_menghapus_rw(): void
    {
        $this->assertFalse($this->superAdmin->can('delete', $this->rw));
    }

    public function test_admin_desa_dapat_menghapus_rw_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('delete', $this->rw));
    }

    public function test_admin_desa_tidak_dapat_menghapus_rw_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('delete', $this->otherRw));
    }

    public function test_admin_rw_tidak_dapat_menghapus_rw(): void
    {
        $this->assertFalse($this->aRw->can('delete', $this->rw));
    }
}