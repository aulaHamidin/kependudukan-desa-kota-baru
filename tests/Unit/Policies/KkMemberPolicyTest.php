<?php

namespace Tests\Unit\Policies;

use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class KkMemberPolicyTest extends TestCase
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

        // KK dalam territory
        $this->kk = KartuKeluarga::factory()->create([
            'rt_id'      => $this->rt->id,
            'created_by' => $this->aDesa->id,
        ]);

        // KK di luar territory
        $kkOther = KartuKeluarga::factory()->create([
            'rt_id'      => $this->otherRt->id,
            'created_by' => $this->aDesa->id,
        ]);

        // Penduduk untuk member
        $penduduk1 = Penduduk::factory()->create([
            'rt_id'      => $this->rt->id,
            'created_by' => $this->aDesa->id,
        ]);
        $penduduk2 = Penduduk::factory()->create([
            'rt_id'      => $this->otherRt->id,
            'created_by' => $this->aDesa->id,
        ]);

        // Member dalam territory — eager load relasi untuk canAccessMember()
        $this->member = KkMember::factory()->create([
            'kartu_keluarga_id' => $this->kk->id,
            'penduduk_id'       => $penduduk1->id,
            'created_by'        => $this->aDesa->id,
        ]);
        $this->member->load('kartuKeluarga.rt.rw');

        // Member di luar territory
        $this->memberOtherRt = KkMember::factory()->create([
            'kartu_keluarga_id' => $kkOther->id,
            'penduduk_id'       => $penduduk2->id,
            'created_by'        => $this->aDesa->id,
        ]);
        $this->memberOtherRt->load('kartuKeluarga.rt.rw');
    }

    // -------------------------------------------------------
    // viewAny
    // -------------------------------------------------------

    public function test_super_admin_dapat_melihat_anggota_kk_read_only(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', KkMember::class));
    }

    public function test_semua_role_operasional_dapat_melihat_anggota_kk(): void
    {
        $this->assertTrue($this->aDesa->can('viewAny', KkMember::class));
        $this->assertTrue($this->aRw->can('viewAny', KkMember::class));
        $this->assertTrue($this->aRt->can('viewAny', KkMember::class));
        $this->assertTrue($this->aViewer->can('viewAny', KkMember::class));
    }

    // -------------------------------------------------------
    // view
    // -------------------------------------------------------

    public function test_super_admin_dapat_melihat_detail_anggota_kk(): void
    {
        $this->assertTrue($this->superAdmin->can('view', $this->member));
    }

    public function test_admin_desa_dapat_melihat_anggota_kk_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('view', $this->member));
    }

    public function test_admin_desa_tidak_dapat_melihat_anggota_kk_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('view', $this->memberOtherRt));
    }

    public function test_admin_rw_dapat_melihat_anggota_kk_dalam_territorinya(): void
    {
        $this->assertTrue($this->aRw->can('view', $this->member));
    }

    public function test_admin_rw_tidak_dapat_melihat_anggota_kk_di_luar_territorinya(): void
    {
        $this->assertFalse($this->aRw->can('view', $this->memberOtherRt));
    }

    public function test_admin_rt_dapat_melihat_anggota_kk_dalam_rtnya(): void
    {
        $this->assertTrue($this->aRt->can('view', $this->member));
    }

    public function test_viewer_dapat_melihat_anggota_kk_sesuai_territory(): void
    {
        $this->assertTrue($this->aViewer->can('view', $this->member));
    }

    // -------------------------------------------------------
    // create — KRITIS: super_admin DENY (before() hook sudah dihapus)
    // -------------------------------------------------------

    public function test_super_admin_tidak_dapat_tambah_anggota_kk_before_hook_dihapus(): void
    {
        $this->assertFalse($this->superAdmin->can('create', KkMember::class));
    }

    public function test_admin_desa_dapat_tambah_anggota_kk(): void
    {
        $this->assertTrue($this->aDesa->can('create', KkMember::class));
    }

    public function test_admin_rw_dapat_tambah_anggota_kk_dalam_territorinya(): void
    {
        $this->assertTrue($this->aRw->can('create', KkMember::class));
    }

    public function test_admin_rt_dapat_tambah_anggota_kk_dalam_rtnya(): void
    {
        $this->assertTrue($this->aRt->can('create', KkMember::class));
    }

    public function test_viewer_tidak_dapat_tambah_anggota_kk(): void
    {
        $this->assertFalse($this->aViewer->can('create', KkMember::class));
    }

    // -------------------------------------------------------
    // update — KRITIS: super_admin DENY, territory check wajib
    // -------------------------------------------------------

    public function test_super_admin_tidak_dapat_mengubah_anggota_kk_manapun(): void
    {
        $this->assertFalse($this->superAdmin->can('update', $this->member));
        $this->assertFalse($this->superAdmin->can('update', $this->memberOtherRt));
    }

    public function test_admin_desa_dapat_mengubah_anggota_kk_dalam_desanya(): void
    {
        $this->assertTrue($this->aDesa->can('update', $this->member));
    }

    public function test_admin_desa_tidak_dapat_mengubah_anggota_kk_di_luar_desanya(): void
    {
        $this->assertFalse($this->aDesa->can('update', $this->memberOtherRt));
    }

    public function test_admin_rw_dapat_mengubah_anggota_kk_dalam_territorinya(): void
    {
        $this->assertTrue($this->aRw->can('update', $this->member));
    }

    public function test_admin_rw_tidak_dapat_mengubah_anggota_kk_di_luar_territorinya(): void
    {
        $this->assertFalse($this->aRw->can('update', $this->memberOtherRt));
    }

    public function test_admin_rt_dapat_mengubah_anggota_kk_dalam_rtnya(): void
    {
        $this->assertTrue($this->aRt->can('update', $this->member));
    }

    public function test_viewer_tidak_dapat_mengubah_anggota_kk(): void
    {
        $this->assertFalse($this->aViewer->can('update', $this->member));
    }

    // -------------------------------------------------------
    // delete — DENY SEMUA ROLE (anggota keluar hanya via event)
    // -------------------------------------------------------

    public function test_super_admin_tidak_dapat_hapus_anggota_kk_langsung(): void
    {
        $this->assertFalse($this->superAdmin->can('delete', $this->member));
    }

    public function test_admin_desa_tidak_dapat_hapus_anggota_kk_harus_via_event(): void
    {
        $this->assertFalse($this->aDesa->can('delete', $this->member));
    }

    public function test_admin_rw_tidak_dapat_hapus_anggota_kk(): void
    {
        $this->assertFalse($this->aRw->can('delete', $this->member));
    }

    public function test_admin_rt_tidak_dapat_hapus_anggota_kk(): void
    {
        $this->assertFalse($this->aRt->can('delete', $this->member));
    }

    public function test_viewer_tidak_dapat_hapus_anggota_kk(): void
    {
        $this->assertFalse($this->aViewer->can('delete', $this->member));
    }
}
