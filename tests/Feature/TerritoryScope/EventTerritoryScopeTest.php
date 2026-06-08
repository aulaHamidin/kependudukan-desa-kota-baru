<?php

declare(strict_types=1);

namespace Tests\Feature\TerritoryScope;

use App\Models\Event;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

/**
 * Memverifikasi bahwa TerritoryScope pada model Event benar-benar
 * membatasi akses data berdasarkan wilayah user yang login.
 *
 * Setiap role harus hanya bisa melihat Event dari wilayahnya sendiri —
 * tidak boleh ada data leakage lintas RT, RW, atau Desa.
 */
class EventTerritoryScopeTest extends TestCase
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

    private function makeEvent(array $territory, array $overrides = []): Event
    {
        $creator = $this->adminDesa($territory['desa']);

        return Event::factory()->create(array_merge([
            'status_data' => 'VERIFIED',
            'rt_id'       => $territory['rt']->id,
            'rw_id'       => $territory['rw']->id,
            'desa_id'     => $territory['desa']->id,
            'created_by'  => $creator->id,
        ], $overrides));
    }

    // ─── admin_rt ────────────────────────────────────────────────────────────

    public function test_admin_rt_hanya_melihat_event_dari_rt_sendiri(): void
    {
        $eventMilikSaya   = $this->makeEvent($this->territory);
        $eventMilikOtherRt = $this->makeEvent($this->otherTerritory);

        $this->actingAs($this->adminRt($this->territory['rt']));

        $events = Event::all();

        $this->assertCount(1, $events);
        $this->assertEquals($eventMilikSaya->id, $events->first()->id);
        $this->assertFalse($events->contains('id', $eventMilikOtherRt->id));
    }

    public function test_admin_rt_tidak_bisa_lihat_event_rt_lain_dalam_rw_sama(): void
    {
        // Buat RT kedua dalam RW yang sama
        $rtLain = \App\Models\Rt::factory()->create(['rw_id' => $this->territory['rw']->id]);

        $eventRtSaya  = $this->makeEvent($this->territory);
        $eventRtLain  = Event::factory()->create([
            'status_data' => 'VERIFIED',
            'rt_id'       => $rtLain->id,
            'rw_id'       => $this->territory['rw']->id,
            'desa_id'     => $this->territory['desa']->id,
            'created_by'  => $this->adminDesa($this->territory['desa'])->id,
        ]);

        $this->actingAs($this->adminRt($this->territory['rt']));

        $events = Event::all();

        $this->assertTrue($events->contains('id', $eventRtSaya->id));
        $this->assertFalse($events->contains('id', $eventRtLain->id));
    }

    // ─── admin_rw ────────────────────────────────────────────────────────────

    public function test_admin_rw_hanya_melihat_event_dari_rw_sendiri(): void
    {
        $eventDesaSaya    = $this->makeEvent($this->territory);
        $eventDesaLain    = $this->makeEvent($this->otherTerritory);

        $this->actingAs($this->adminRw($this->territory['rw']));

        $events = Event::all();

        $this->assertTrue($events->contains('id', $eventDesaSaya->id));
        $this->assertFalse($events->contains('id', $eventDesaLain->id));
    }

    // ─── admin_desa ───────────────────────────────────────────────────────────

    public function test_admin_desa_hanya_melihat_event_dari_desa_sendiri(): void
    {
        $eventDesaSaya = $this->makeEvent($this->territory);
        $eventDesaLain = $this->makeEvent($this->otherTerritory);

        $this->actingAs($this->adminDesa($this->territory['desa']));

        $events = Event::all();

        $this->assertTrue($events->contains('id', $eventDesaSaya->id));
        $this->assertFalse($events->contains('id', $eventDesaLain->id));
    }

    public function test_admin_desa_melihat_semua_event_dari_rt_berbeda_dalam_desa_sendiri(): void
    {
        // RT kedua dalam desa yang sama
        $rwLain = \App\Models\Rw::factory()->create(['desa_id' => $this->territory['desa']->id]);
        $rtLain = \App\Models\Rt::factory()->create(['rw_id' => $rwLain->id]);

        $eventRt1 = $this->makeEvent($this->territory);
        $eventRt2 = Event::factory()->create([
            'status_data' => 'VERIFIED',
            'rt_id'       => $rtLain->id,
            'rw_id'       => $rwLain->id,
            'desa_id'     => $this->territory['desa']->id,
            'created_by'  => $this->adminDesa($this->territory['desa'])->id,
        ]);

        $this->actingAs($this->adminDesa($this->territory['desa']));

        $events = Event::all();

        // admin_desa bisa lihat KEDUA event dalam desanya
        $this->assertTrue($events->contains('id', $eventRt1->id));
        $this->assertTrue($events->contains('id', $eventRt2->id));
    }

    // ─── super_admin ──────────────────────────────────────────────────────────

    public function test_super_admin_melihat_semua_event_lintas_desa(): void
    {
        $eventDesaA = $this->makeEvent($this->territory);
        $eventDesaB = $this->makeEvent($this->otherTerritory);

        $this->actingAs($this->superAdmin());

        $events = Event::all();

        // super_admin tidak difilter — lihat semua
        $this->assertTrue($events->contains('id', $eventDesaA->id));
        $this->assertTrue($events->contains('id', $eventDesaB->id));
    }
}
