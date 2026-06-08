<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Event;
use App\Models\Penduduk;
use App\Services\ApprovalService;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class ApprovalServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private ApprovalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(ApprovalService::class);
    }

    private function makePenduduk(int $rtId, int $actorId): Penduduk
    {
        return Penduduk::factory()->create([
            'rt_id'                    => $rtId,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actorId,
        ]);
    }

    // =========================================================================
    // approveEvent
    // =========================================================================

    public function test_approve_event_draft_menjadi_verified(): void
    {
        $territory = $this->createTerritory();
        $adminRt   = $this->adminRt($territory['rt']);
        $adminDesa = $this->adminDesa($territory['desa']);

        $penduduk = $this->makePenduduk($territory['rt']->id, $adminRt->id);

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $adminRt->id,
        ]);

        $result = $this->service->approveEvent($adminDesa, $event);

        $this->assertEquals('VERIFIED', $result->status_data);
        $this->assertEquals($adminDesa->id, $result->verified_by);
    }

    public function test_approve_event_sudah_verified_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $adminDesa = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $adminDesa->id);

        $event = Event::factory()->verified()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $adminDesa->id,
        ]);

        $this->expectException(\Exception::class); // AuthorizationException atau DomainException

        $this->service->approveEvent($adminDesa, $event);
    }

    public function test_admin_rw_tidak_bisa_approve_event_dari_admin_rw_lain(): void
    {
        $territory  = $this->createTerritory();
        $territory2 = $this->createTerritory();
        $adminRw1   = $this->adminRw($territory['rw']);
        $adminRw2   = $this->adminRw($territory2['rw']);

        $penduduk = $this->makePenduduk($territory2['rt']->id, $adminRw2->id);

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory2['rt']->id,
            'rw_id'           => $territory2['rw']->id,
            'desa_id'         => $territory2['desa']->id,
            'created_by'      => $adminRw2->id, // Dibuat oleh RW lain
        ]);

        $this->expectException(\Exception::class); // AuthorizationException atau DomainException

        // Admin RW dari territory berbeda tidak bisa approve event RW lain
        $this->service->approveEvent($adminRw1, $event);
    }

    // =========================================================================
    // rejectEvent
    // =========================================================================

    public function test_reject_event_menambahkan_catatan_penolakan_di_keterangan(): void
    {
        $territory = $this->createTerritory();
        $adminRt   = $this->adminRt($territory['rt']);
        $adminDesa = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $adminRt->id);

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $adminRt->id,
            'keterangan'      => 'Keterangan asal',
        ]);

        $result = $this->service->rejectEvent($adminDesa, $event, 'Data tidak lengkap');

        $this->assertEquals('DRAFT', $result->status_data);
        $this->assertStringContainsString('[DITOLAK]', $result->keterangan);
        $this->assertStringContainsString('Data tidak lengkap', $result->keterangan);
    }

    public function test_reject_event_tanpa_alasan_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $adminRt   = $this->adminRt($territory['rt']);
        $adminDesa = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $adminRt->id);

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $adminRt->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Alasan penolakan');

        $this->service->rejectEvent($adminDesa, $event, '');
    }
}
