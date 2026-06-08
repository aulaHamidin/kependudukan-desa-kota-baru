<?php

namespace Tests\Feature\Event;

use App\Models\Event;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
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

    private function makeEvent(array $overrides = []): Event
    {
        return Event::factory()->create(array_merge([
            'status_data' => 'DRAFT',
            'rt_id'       => $this->territory['rt']->id,
            'rw_id'       => $this->territory['rw']->id,
            'desa_id'     => $this->territory['desa']->id,
        ], $overrides));
    }

    public function test_admin_desa_can_approve_event_via_http(): void
    {
        $admin = $this->adminDesa($this->territory['desa']);
        $event = $this->makeEvent(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $event))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('events', [
            'id'          => $event->id,
            'status_data' => 'VERIFIED',
        ]);
    }

    public function test_viewer_cannot_see_draft_event_returns_404(): void
    {
        // Viewer hanya bisa lihat event VERIFIED (via TerritoryScope)
        // → mencoba approve event DRAFT milik desa sendiri → 404
        $admin  = $this->adminDesa($this->territory['desa']);
        $viewer = $this->viewer($this->territory['rt']);
        $event  = $this->makeEvent(['created_by' => $admin->id]);

        $this->actingAs($viewer)
            ->post(route('approvals.approve', $event))
            ->assertNotFound(); // TerritoryScope memfilter DRAFT untuk viewer
    }

    public function test_admin_rw_can_approve_event_admin_rt_in_rw_via_http(): void
    {
        $adminRw = $this->adminRw($this->territory['rw']);
        $adminRt = $this->adminRt($this->territory['rt']);
        $event   = $this->makeEvent(['created_by' => $adminRt->id]);

        $this->actingAs($adminRw)
            ->post(route('approvals.approve', $event))
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id'          => $event->id,
            'status_data' => 'VERIFIED',
        ]);
    }

    public function test_admin_rw_cannot_see_event_from_other_rw_returns_404(): void
    {
        // Event di territory berbeda → TerritoryScope memfilter → 404
        $adminRw1 = $this->adminRw($this->territory['rw']);
        $adminRw2 = $this->adminRw($this->otherTerritory['rw']);
        $event    = $this->makeEvent([
            'created_by' => $adminRw2->id,
            'rt_id'      => $this->otherTerritory['rt']->id,
            'rw_id'      => $this->otherTerritory['rw']->id,
            'desa_id'    => $this->otherTerritory['desa']->id,
        ]);

        $this->actingAs($adminRw1)
            ->post(route('approvals.approve', $event))
            ->assertNotFound();
    }

    public function test_admin_rt_cannot_approve_event_via_http(): void
    {
        $adminRt = $this->adminRt($this->territory['rt']);
        $event   = $this->makeEvent(['created_by' => $adminRt->id]);

        $this->actingAs($adminRt)
            ->post(route('approvals.approve', $event))
            ->assertForbidden();
    }

    public function test_admin_desa_cannot_see_event_outside_desa_returns_404(): void
    {
        // Event di desa lain → TerritoryScope memfilter → 404
        $admin = $this->adminDesa($this->territory['desa']);
        $event = $this->makeEvent([
            'rt_id'   => $this->otherTerritory['rt']->id,
            'rw_id'   => $this->otherTerritory['rw']->id,
            'desa_id' => $this->otherTerritory['desa']->id,
        ]);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $event))
            ->assertNotFound();
    }

    public function test_cannot_approve_verified_event_returns_forbidden(): void
    {
        // EventPolicy::approve() menolak event VERIFIED → 403
        $admin = $this->adminDesa($this->territory['desa']);
        $event = $this->makeEvent([
            'status_data' => 'VERIFIED',
            'created_by'  => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $event))
            ->assertForbidden();
    }

    public function test_cannot_approve_void_event_returns_forbidden(): void
    {
        // EventPolicy::approve() menolak event VOID → 403
        $admin = $this->adminDesa($this->territory['desa']);
        $event = $this->makeEvent([
            'status_data' => 'VOID',
            'created_by'  => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $event))
            ->assertForbidden();
    }

    public function test_status_flow_draft_to_verified_via_http(): void
    {
        // Void memerlukan detail record (EventKematian/EventPindah/etc.)
        // yang kompleks untuk feature test — void dicover di EventVoidServiceTest.
        // Test ini memverifikasi DRAFT → VERIFIED flow HTTP.
        $admin = $this->adminDesa($this->territory['desa']);
        $event = $this->makeEvent(['created_by' => $admin->id]);

        $this->actingAs($admin)
            ->post(route('approvals.approve', $event))
            ->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id'          => $event->id,
            'status_data' => 'VERIFIED',
        ]);
    }
}
