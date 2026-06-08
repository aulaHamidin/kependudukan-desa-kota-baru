<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class UserPolicyTest extends TestCase
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

        $this->targetAdminDesa = $this->adminDesa($this->desa);
        $this->targetAdminRw   = $this->adminRw($this->rw);
        $this->targetAdminRt   = $this->adminRt($this->rt);
        $this->targetViewer    = $this->viewer($this->rt);
    }

    public function test_super_admin_dapat_melihat_semua_user(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', User::class));
    }

    public function test_admin_desa_dapat_melihat_user_dalam_territorinya(): void
    {
        $this->assertTrue($this->aDesa->can('viewAny', User::class));
    }

    public function test_viewer_tidak_dapat_melihat_daftar_user(): void
    {
        $this->assertFalse($this->aViewer->can('viewAny', User::class));
    }

    public function test_super_admin_dapat_membuat_user(): void
    {
        $this->assertTrue($this->superAdmin->can('create', User::class));
    }

    public function test_admin_desa_dapat_membuat_user(): void
    {
        $this->assertTrue($this->aDesa->can('create', User::class));
    }

    public function test_admin_rw_dapat_membuat_user(): void
    {
        $this->assertTrue($this->aRw->can('create', User::class));
    }

    public function test_admin_rt_tidak_dapat_membuat_user(): void
    {
        $this->assertFalse($this->aRt->can('create', User::class));
    }

    public function test_viewer_tidak_dapat_membuat_user(): void
    {
        $this->assertFalse($this->aViewer->can('create', User::class));
    }

    // update — KRITIS: super_admin hanya untuk admin_desa
    public function test_super_admin_dapat_mengubah_user_admin_desa(): void
    {
        $this->assertTrue($this->superAdmin->can('update', $this->targetAdminDesa));
    }

    public function test_super_admin_tidak_dapat_mengubah_user_admin_rw(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->targetAdminRw));
    }

    public function test_super_admin_tidak_dapat_mengubah_user_admin_rt(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->targetAdminRt));
    }

    public function test_super_admin_tidak_dapat_mengubah_dirinya_sendiri(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->superAdmin));
    }

    public function test_admin_desa_dapat_mengubah_user_admin_rw_dalam_territorinya(): void
    {
        $this->assertTrue($this->aDesa->can('update', $this->targetAdminRw));
    }

    public function test_admin_desa_tidak_dapat_mengubah_dirinya_sendiri(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->aDesa));
    }

    public function test_admin_rw_dapat_mengubah_user_admin_rt_dalam_rwnya(): void
    {
        $this->assertTrue($this->aRw->can('update', $this->targetAdminRt));
    }

    public function test_admin_rw_tidak_dapat_mengubah_user_di_luar_rwnya(): void
    {
        $outsideRt = $this->adminRt($this->otherRt);
        $this->assertFalse($this->aRw->can('update', $outsideRt));
    }

    // delete
    public function test_super_admin_dapat_menghapus_user_admin_desa(): void
    {
        $this->assertTrue($this->superAdmin->can('delete', $this->targetAdminDesa));
    }

    public function test_super_admin_tidak_dapat_menghapus_user_admin_rw(): void
    {
        $this->assertFalse($this->superAdmin->can('delete', $this->targetAdminRw));
    }

    public function test_super_admin_tidak_dapat_menghapus_dirinya_sendiri(): void
    {
        $this->assertFalse($this->superAdmin->can('delete', $this->superAdmin));
    }

    public function test_admin_desa_dapat_menghapus_user_dalam_territorinya(): void
    {
        $this->assertTrue($this->aDesa->can('delete', $this->targetAdminRw));
    }

    public function test_admin_desa_tidak_dapat_menghapus_dirinya_sendiri(): void
    {
        $this->assertFalse($this->aDesa->can('delete', $this->aDesa));
    }

    public function test_admin_rw_dapat_menghapus_user_dalam_rwnya(): void
    {
        $this->assertTrue($this->aRw->can('delete', $this->targetAdminRt));
    }

    public function test_admin_rw_tidak_dapat_menghapus_dirinya_sendiri(): void
    {
        $this->assertFalse($this->aRw->can('delete', $this->aRw));
    }

    // restore — KRITIS: admin_rw DENY (hindari privilege escalation)
    public function test_super_admin_dapat_restore_user(): void
    {
        $deleted = $this->adminDesa($this->desa);
        $deleted->delete();
        $this->assertTrue($this->superAdmin->can('restore', $deleted));
    }

    public function test_admin_desa_dapat_restore_user_dalam_territorinya(): void
    {
        $deleted = $this->adminRw($this->rw);
        $deleted->delete();
        $this->assertTrue($this->aDesa->can('restore', $deleted));
    }

    public function test_admin_rw_tidak_dapat_restore_user_hindari_privilege_escalation(): void
    {
        $deleted = $this->adminRt($this->rt);
        $deleted->delete();
        $this->assertFalse($this->aRw->can('restore', $deleted));
    }

    public function test_admin_rt_tidak_dapat_restore_user(): void
    {
        $deleted = $this->viewer($this->rt);
        $deleted->delete();
        $this->assertFalse($this->aRt->can('restore', $deleted));
    }

    public function test_viewer_tidak_dapat_restore_user(): void
    {
        $deleted = $this->viewer($this->rt);
        $deleted->delete();
        $this->assertFalse($this->aViewer->can('restore', $deleted));
    }
}