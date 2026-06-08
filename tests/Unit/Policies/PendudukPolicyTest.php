<?php

namespace Tests\Unit\Policies;

use App\Models\Penduduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class PendudukPolicyTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\SeedMasterData::class);

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

        // Penduduk dalam territory
        $this->penduduk = Penduduk::factory()->create([
            'rt_id'      => $this->rt->id,
            'created_by' => $this->aDesa->id,
        ]);
        $this->penduduk->load('rt.rw');

        // Penduduk di luar territory
        $this->pendudukOtherRt = Penduduk::factory()->create([
            'rt_id'      => $this->otherRt->id,
            'created_by' => $this->aDesa->id,
        ]);
        $this->pendudukOtherRt->load('rt.rw');
    }

    // -------------------------------------------------------
    // viewAny
    // -------------------------------------------------------

    public function test_semua_role_dapat_melihat_daftar_penduduk(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', Penduduk::class));
        $this->assertTrue($this->aDesa->can('viewAny', Penduduk::class));
        $this->assertTrue($this->aRw->can('viewAny', Penduduk::class));
        $this->assertTrue($this->aRt->can('viewAny', Penduduk::class));
        $this->assertTrue($this->aViewer->can('viewAny', Penduduk::class));
    }

    // -------------------------------------------------------
    // view
    // -------------------------------------------------------

    public function test_super_admin_dapat_melihat_penduduk_manapun(): void
    {
        $this->assertTrue($this->superAdmin->can('view', $this->penduduk));
        $this->assertTrue($this->superAdmin->can('view', $this->pendudukOtherRt));
    }

    public function test_admin_desa_dapat_melihat_penduduk_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('view', $this->penduduk));
    }

    public function test_admin_desa_tidak_dapat_melihat_penduduk_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('view', $this->pendudukOtherRt));
    }

    public function test_admin_rw_dapat_melihat_penduduk_dalam_territorinya(): void
    {
        $this->assertTrue($this->aRw->can('view', $this->penduduk));
    }

    public function test_admin_rw_tidak_dapat_melihat_penduduk_di_luar_territorinya(): void
    {
        $this->assertFalse($this->aRw->can('view', $this->pendudukOtherRt));
    }

    public function test_admin_rt_dapat_melihat_penduduk_dalam_rtnya(): void
    {
        $this->assertTrue($this->aRt->can('view', $this->penduduk));
    }

    public function test_admin_rt_tidak_dapat_melihat_penduduk_di_luar_rtnya(): void
    {
        $this->assertFalse($this->aRt->can('view', $this->pendudukOtherRt));
    }

    public function test_viewer_dapat_melihat_penduduk_sesuai_territory(): void
    {
        $this->assertTrue($this->aViewer->can('view', $this->penduduk));
    }

    // -------------------------------------------------------
    // create — ALL DENY (penduduk dibuat hanya via event)
    // -------------------------------------------------------

    public function test_tidak_ada_role_yang_dapat_membuat_penduduk(): void
    {
        $this->assertFalse($this->superAdmin->can('create', Penduduk::class));
        $this->assertFalse($this->aDesa->can('create', Penduduk::class));
        $this->assertFalse($this->aRw->can('create', Penduduk::class));
        $this->assertFalse($this->aRt->can('create', Penduduk::class));
        $this->assertFalse($this->aViewer->can('create', Penduduk::class));
    }

    // -------------------------------------------------------
    // update — hanya admin_desa dalam territory
    // -------------------------------------------------------

    public function test_super_admin_tidak_dapat_mengubah_penduduk(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->penduduk));
    }

    public function test_admin_desa_dapat_mengubah_penduduk_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('update', $this->penduduk));
    }

    public function test_admin_desa_tidak_dapat_mengubah_penduduk_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->pendudukOtherRt));
    }

    public function test_admin_rw_tidak_dapat_mengubah_penduduk(): void
    {
        $this->assertFalse($this->aRw->can('update', $this->penduduk));
    }

    public function test_admin_rt_tidak_dapat_mengubah_penduduk(): void
    {
        $this->assertFalse($this->aRt->can('update', $this->penduduk));
    }

    public function test_viewer_tidak_dapat_mengubah_penduduk(): void
    {
        $this->assertFalse($this->aViewer->can('update', $this->penduduk));
    }

    // -------------------------------------------------------
    // delete — ALL DENY (penduduk dihapus hanya via event)
    // -------------------------------------------------------

    public function test_tidak_ada_role_yang_dapat_menghapus_penduduk(): void
    {
        $this->assertFalse($this->superAdmin->can('delete', $this->penduduk));
        $this->assertFalse($this->aDesa->can('delete', $this->penduduk));
        $this->assertFalse($this->aRw->can('delete', $this->penduduk));
        $this->assertFalse($this->aRt->can('delete', $this->penduduk));
        $this->assertFalse($this->aViewer->can('delete', $this->penduduk));
    }
}
