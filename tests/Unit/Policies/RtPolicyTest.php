<?php

namespace Tests\Unit\Policies;

use App\Models\Rt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class RtPolicyTest extends TestCase
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

        // RT lain dalam RW yang sama
        $this->rtSameRw = Rt::factory()->create(['rw_id' => $this->rw->id]);

        $this->superAdmin = $this->superAdmin();
        $this->aDesa      = $this->adminDesa($this->desa);
        $this->aRw        = $this->adminRw($this->rw);
        $this->aRt        = $this->adminRt($this->rt);
        $this->aViewer    = $this->viewer($this->rt);
    }

    public function test_semua_role_dapat_melihat_daftar_rt(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', Rt::class));
        $this->assertTrue($this->aDesa->can('viewAny', Rt::class));
        $this->assertTrue($this->aRw->can('viewAny', Rt::class));
        $this->assertTrue($this->aRt->can('viewAny', Rt::class));
        $this->assertTrue($this->aViewer->can('viewAny', Rt::class));
    }

    // view — sebelumnya MISSING di policy, sekarang sudah ditambah
    public function test_super_admin_dapat_melihat_rt_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('view', $this->rt));
        $this->assertTrue($this->superAdmin->can('view', $this->otherRt));
    }

    public function test_admin_desa_dapat_melihat_rt_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('view', $this->rt));
    }

    public function test_admin_rw_dapat_melihat_rt_dalam_rwnya(): void
    {
        $this->assertTrue($this->aRw->can('view', $this->rt));
        $this->assertTrue($this->aRw->can('view', $this->rtSameRw));
    }

    public function test_admin_rw_tidak_dapat_melihat_rt_di_luar_rwnya(): void
    {
        $this->assertFalse($this->aRw->can('view', $this->otherRt));
    }

    public function test_admin_rt_dapat_melihat_rt_miliknya(): void
    {
        $this->assertTrue($this->aRt->can('view', $this->rt));
    }

    public function test_admin_rt_tidak_dapat_melihat_rt_lain_dalam_rw_yang_sama(): void
    {
        $this->assertFalse($this->aRt->can('view', $this->rtSameRw));
    }

    public function test_viewer_dapat_melihat_rt_sesuai_territory(): void
    {
        $this->assertTrue($this->aViewer->can('view', $this->rt));
    }

    // create — KRITIS: super_admin DENY
    public function test_super_admin_tidak_dapat_membuat_rt(): void
    {
        $this->assertFalse($this->superAdmin->can('create', Rt::class));
    }

    public function test_admin_desa_dapat_membuat_rt(): void
    {
        $this->assertTrue($this->aDesa->can('create', Rt::class));
    }

    public function test_admin_rw_dapat_membuat_rt_dalam_rwnya(): void
    {
        $this->assertTrue($this->aRw->can('create', Rt::class));
    }

    public function test_admin_rt_tidak_dapat_membuat_rt(): void
    {
        $this->assertFalse($this->aRt->can('create', Rt::class));
    }

    public function test_viewer_tidak_dapat_membuat_rt(): void
    {
        $this->assertFalse($this->aViewer->can('create', Rt::class));
    }

    // update
    public function test_super_admin_tidak_dapat_mengubah_rt(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->rt));
    }

    public function test_admin_desa_dapat_mengubah_rt_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('update', $this->rt));
    }

    public function test_admin_rw_dapat_mengubah_rt_dalam_rwnya(): void
    {
        $this->assertTrue($this->aRw->can('update', $this->rt));
    }

    public function test_admin_rw_tidak_dapat_mengubah_rt_di_luar_rwnya(): void
    {
        $this->assertFalse($this->aRw->can('update', $this->otherRt));
    }

    public function test_admin_rt_tidak_dapat_mengubah_rt(): void
    {
        $this->assertFalse($this->aRt->can('update', $this->rt));
    }

    // delete
    public function test_super_admin_tidak_dapat_menghapus_rt(): void
    {
        $this->assertFalse($this->superAdmin->can('delete', $this->rt));
    }

    public function test_admin_desa_dapat_menghapus_rt_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('delete', $this->rt));
    }

    public function test_admin_rw_dapat_menghapus_rt_dalam_rwnya(): void
    {
        $this->assertTrue($this->aRw->can('delete', $this->rt));
    }

    public function test_admin_rw_tidak_dapat_menghapus_rt_di_luar_rwnya(): void
    {
        $this->assertFalse($this->aRw->can('delete', $this->otherRt));
    }

    public function test_admin_rt_tidak_dapat_menghapus_rt(): void
    {
        $this->assertFalse($this->aRt->can('delete', $this->rt));
    }
}