<?php

namespace Tests\Unit\Policies;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class EventPolicyTest extends TestCase
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
    }

    // -------------------------------------------------------
    // viewAny
    // -------------------------------------------------------

    public function test_super_admin_dapat_melihat_daftar_event(): void
    {
        $this->assertTrue($this->superAdmin->can('viewAny', Event::class));
    }

    public function test_admin_desa_dapat_melihat_daftar_event(): void
    {
        $this->assertTrue($this->aDesa->can('viewAny', Event::class));
    }

    public function test_admin_rw_dapat_melihat_daftar_event(): void
    {
        $this->assertTrue($this->aRw->can('viewAny', Event::class));
    }

    public function test_admin_rt_dapat_melihat_daftar_event(): void
    {
        $this->assertTrue($this->aRt->can('viewAny', Event::class));
    }

    public function test_viewer_dapat_melihat_daftar_event(): void
    {
        $this->assertTrue($this->aViewer->can('viewAny', Event::class));
    }

    // -------------------------------------------------------
    // view — viewer hanya VERIFIED
    // -------------------------------------------------------

    public function test_super_admin_dapat_melihat_event_status_apapun(): void
    {
        $draft    = Event::factory()->create(['status_data' => 'DRAFT',    'rt_id' => $this->rt->id]);
        $verified = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $void     = Event::factory()->create(['status_data' => 'VOID',     'rt_id' => $this->rt->id]);

        $this->assertTrue($this->superAdmin->can('view', $draft));
        $this->assertTrue($this->superAdmin->can('view', $verified));
        $this->assertTrue($this->superAdmin->can('view', $void));
    }

    public function test_admin_desa_dapat_melihat_event_status_apapun_dalam_desanya(): void
    {
        $draft    = Event::factory()->create(['status_data' => 'DRAFT',    'rt_id' => $this->rt->id]);
        $verified = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $void     = Event::factory()->create(['status_data' => 'VOID',     'rt_id' => $this->rt->id]);

        $this->assertTrue($this->aDesa->can('view', $draft));
        $this->assertTrue($this->aDesa->can('view', $verified));
        $this->assertTrue($this->aDesa->can('view', $void));
    }

    public function test_viewer_hanya_dapat_melihat_event_verified(): void
    {
        $verified = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $this->assertTrue($this->aViewer->can('view', $verified));
    }

    public function test_viewer_tidak_dapat_melihat_event_draft(): void
    {
        $draft = Event::factory()->create(['status_data' => 'DRAFT', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aViewer->can('view', $draft));
    }

    public function test_viewer_tidak_dapat_melihat_event_void(): void
    {
        $void = Event::factory()->create(['status_data' => 'VOID', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aViewer->can('view', $void));
    }

    public function test_admin_rt_dapat_melihat_event_draft_buatan_admin_desa_dalam_rtnya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $this->assertTrue($this->aRt->can('view', $event));
    }

    public function test_admin_rw_dapat_melihat_event_draft_buatan_admin_desa_dalam_rwnya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rw_id'       => $this->rw->id,
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $this->assertTrue($this->aRw->can('view', $event));
    }

    public function test_admin_rw_dapat_melihat_event_status_apapun_dalam_rwnya(): void
    {
        $draft    = Event::factory()->create(['status_data' => 'DRAFT',    'rw_id' => $this->rw->id, 'rt_id' => $this->rt->id]);
        $verified = Event::factory()->create(['status_data' => 'VERIFIED', 'rw_id' => $this->rw->id, 'rt_id' => $this->rt->id]);
        $void     = Event::factory()->create(['status_data' => 'VOID',     'rw_id' => $this->rw->id, 'rt_id' => $this->rt->id]);

        $this->assertTrue($this->aRw->can('view', $draft));
        $this->assertTrue($this->aRw->can('view', $verified));
        $this->assertTrue($this->aRw->can('view', $void));
    }

    public function test_admin_rt_dapat_melihat_event_status_apapun_dalam_rtnya(): void
    {
        $draft    = Event::factory()->create(['status_data' => 'DRAFT',    'rt_id' => $this->rt->id]);
        $verified = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $void     = Event::factory()->create(['status_data' => 'VOID',     'rt_id' => $this->rt->id]);

        $this->assertTrue($this->aRt->can('view', $draft));
        $this->assertTrue($this->aRt->can('view', $verified));
        $this->assertTrue($this->aRt->can('view', $void));
    }

    // -------------------------------------------------------
    // create — super_admin DENY (explicitly diblock)
    // -------------------------------------------------------

    public function test_super_admin_tidak_dapat_membuat_event(): void
    {
        $this->assertFalse($this->superAdmin->can('create', Event::class));
    }

    public function test_admin_desa_dapat_membuat_event(): void
    {
        $this->assertTrue($this->aDesa->can('create', Event::class));
    }

    public function test_admin_rw_dapat_membuat_event(): void
    {
        $this->assertTrue($this->aRw->can('create', Event::class));
    }

    public function test_admin_rt_dapat_membuat_event(): void
    {
        $this->assertTrue($this->aRt->can('create', Event::class));
    }

    public function test_viewer_tidak_dapat_membuat_event(): void
    {
        $this->assertFalse($this->aViewer->can('create', Event::class));
    }

    // -------------------------------------------------------
    // update — hanya DRAFT, hanya creator
    // -------------------------------------------------------

    public function test_admin_desa_dapat_mengubah_event_draft_buatannya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $this->assertTrue($this->aDesa->can('update', $event));
    }

    public function test_admin_desa_tidak_dapat_mengubah_event_verified(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'VERIFIED',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $this->assertFalse($this->aDesa->can('update', $event));
    }

    public function test_admin_rw_dapat_mengubah_event_draft_buatannya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rw_id'       => $this->rw->id,
            'created_by'  => $this->aRw->id,
        ]);
        $this->assertTrue($this->aRw->can('update', $event));
    }

    public function test_admin_rw_tidak_dapat_mengubah_event_draft_buatan_orang_lain(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rw_id'       => $this->rw->id,
            'created_by'  => $this->aRt->id,
        ]);
        $this->assertFalse($this->aRw->can('update', $event));
    }

    public function test_admin_rt_dapat_mengubah_event_draft_buatannya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aRt->id,
        ]);
        $this->assertTrue($this->aRt->can('update', $event));
    }

    public function test_admin_rt_tidak_dapat_mengubah_event_draft_buatan_orang_lain(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $this->assertFalse($this->aRt->can('update', $event));
    }

    public function test_admin_desa_tidak_dapat_mengubah_event_draft_buatan_orang_lain(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aRt->id,
        ]);
        $this->assertFalse($this->aDesa->can('update', $event));
    }

    public function test_viewer_tidak_dapat_mengubah_event(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aViewer->id,
        ]);
        $this->assertFalse($this->aViewer->can('update', $event));
    }

    public function test_super_admin_tidak_dapat_mengubah_event(): void
    {
        $event = Event::factory()->create(['status_data' => 'DRAFT', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->superAdmin->can('update', $event));
    }

    // -------------------------------------------------------
    // delete — hanya DRAFT
    // -------------------------------------------------------

    public function test_admin_desa_dapat_menghapus_event_draft_dalam_desanya(): void
    {
        $event = Event::factory()->create(['status_data' => 'DRAFT', 'rt_id' => $this->rt->id]);
        $this->assertTrue($this->aDesa->can('delete', $event));
    }

    public function test_admin_desa_tidak_dapat_menghapus_event_verified(): void
    {
        $event = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aDesa->can('delete', $event));
    }

    public function test_admin_rw_dapat_menghapus_event_draft_dari_rt_dalam_rwnya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'rw_id'       => $this->rw->id,
        ]);
        $this->assertTrue($this->aRw->can('delete', $event));
    }

    public function test_admin_rw_dapat_menghapus_event_draft_buatan_admin_desa_dalam_rwnya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'rw_id'       => $this->rw->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $this->assertTrue($this->aRw->can('delete', $event));
    }

    public function test_admin_rw_tidak_dapat_menghapus_event_dari_rt_di_luar_rwnya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->otherRt->id,
        ]);
        $this->assertFalse($this->aRw->can('delete', $event));
    }

    public function test_admin_rt_dapat_menghapus_event_draft_buatannya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aRt->id,
        ]);
        $this->assertTrue($this->aRt->can('delete', $event));
    }

    public function test_admin_rt_tidak_dapat_menghapus_event_buatan_orang_lain(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $this->assertFalse($this->aRt->can('delete', $event));
    }

    // -------------------------------------------------------
    // verify — approve() DIHAPUS, self-verify admin_desa by design
    // -------------------------------------------------------

    public function test_admin_desa_dapat_verify_event_draft_termasuk_buatannya_sendiri(): void
    {
        $ownEvent = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aDesa->id,
        ]);
        $otherEvent = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'created_by'  => $this->aRt->id,
        ]);

        $this->assertTrue($this->aDesa->can('verify', $ownEvent));
        $this->assertTrue($this->aDesa->can('verify', $otherEvent));
    }

    public function test_admin_desa_tidak_dapat_verify_event_bukan_draft(): void
    {
        $event = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aDesa->can('verify', $event));
    }

    public function test_admin_rw_dapat_verify_event_dari_admin_rt_dalam_rwnya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->rt->id,
            'rw_id'       => $this->rw->id,
            'created_by'  => $this->aRt->id,
        ]);
        $this->assertTrue($this->aRw->can('verify', $event));
    }

    public function test_admin_rw_tidak_dapat_verify_event_buatannya_sendiri(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rw_id'       => $this->rw->id,
            'created_by'  => $this->aRw->id,
        ]);
        $this->assertFalse($this->aRw->can('verify', $event));
    }

    public function test_admin_rw_tidak_dapat_verify_event_dari_rt_di_luar_rwnya(): void
    {
        $event = Event::factory()->create([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->otherRt->id,
            'created_by'  => $this->aRt->id,
        ]);
        $this->assertFalse($this->aRw->can('verify', $event));
    }

    public function test_super_admin_tidak_dapat_verify_event(): void
    {
        $event = Event::factory()->create(['status_data' => 'DRAFT', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->superAdmin->can('verify', $event));
    }

    public function test_admin_rt_tidak_dapat_verify_event(): void
    {
        $event = Event::factory()->create(['status_data' => 'DRAFT', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aRt->can('verify', $event));
    }

    public function test_method_approve_sudah_dihapus_tidak_ada_yang_bisa_approve(): void
    {
        $event = Event::factory()->create(['status_data' => 'DRAFT', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aDesa->can('approve', $event));
        $this->assertFalse($this->aRw->can('approve', $event));
        $this->assertFalse($this->superAdmin->can('approve', $event));
    }

    // -------------------------------------------------------
    // void — VERIFIED → VOID (final, tidak ada unvoid)
    // -------------------------------------------------------

    public function test_admin_desa_dapat_void_event_verified_dalam_desanya(): void
    {
        $event = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $this->assertTrue($this->aDesa->can('void', $event));
    }

    public function test_admin_desa_tidak_dapat_void_event_draft(): void
    {
        $event = Event::factory()->create(['status_data' => 'DRAFT', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aDesa->can('void', $event));
    }

    public function test_admin_desa_tidak_dapat_void_event_yang_sudah_void(): void
    {
        $event = Event::factory()->create(['status_data' => 'VOID', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aDesa->can('void', $event));
    }

    public function test_super_admin_tidak_dapat_void_event(): void
    {
        $event = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->superAdmin->can('void', $event));
    }

    public function test_admin_rw_tidak_dapat_void_event(): void
    {
        $event = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aRw->can('void', $event));
    }

    public function test_admin_rt_tidak_dapat_void_event(): void
    {
        $event = Event::factory()->create(['status_data' => 'VERIFIED', 'rt_id' => $this->rt->id]);
        $this->assertFalse($this->aRt->can('void', $event));
    }

    public function test_void_bersifat_final_tidak_ada_yang_bisa_unvoid(): void
    {
        $event = Event::factory()->create(['status_data' => 'VOID', 'rt_id' => $this->rt->id]);

        $this->assertFalse($this->superAdmin->can('unvoid', $event));
        $this->assertFalse($this->aDesa->can('unvoid', $event));
        $this->assertFalse($this->aRw->can('unvoid', $event));
        $this->assertFalse($this->aRt->can('unvoid', $event));
        $this->assertFalse($this->aViewer->can('unvoid', $event));
    }
}