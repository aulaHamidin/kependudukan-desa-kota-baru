<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Event;

use App\Models\Event;
use App\Models\EventDatang;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Services\Event\DatangService;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class DatangServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private DatangService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(DatangService::class);
    }

    private function makeKk(int $rtId, int $actorId, string $status = 'AKTIF'): KartuKeluarga
    {
        return KartuKeluarga::factory()->create([
            'rt_id'      => $rtId,
            'status_kk'  => $status,
            'created_by' => $actorId,
        ]);
    }

    private function makePenduduk(int $rtId, int $actorId, string $status = 'AKTIF'): Penduduk
    {
        return Penduduk::factory()->create([
            'rt_id'                    => $rtId,
            'status_kependudukan_code' => $status,
            'created_by'               => $actorId,
        ]);
    }

    private function makeKkMember(int $kkId, int $pendudukId, int $actorId, bool $isKepala = false): KkMember
    {
        return KkMember::factory()->create([
            'kartu_keluarga_id'  => $kkId,
            'penduduk_id'        => $pendudukId,
            'is_kepala_keluarga' => $isKepala,
            'status'             => 'AKTIF',
            'created_by'         => $actorId,
        ]);
    }

    // =========================================================================
    // deleteEventDatang
    // =========================================================================

    public function test_delete_datang_pendatang_baru_soft_deletes_penduduk(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventDatang::factory()->create([
            'event_id'         => $event->id,
            'jenis_kedatangan' => 'PENDATANG_BARU',
        ]);

        $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->actingAs($actor);
        $this->service->deleteEventDatang($actor, $event);

        $this->assertSoftDeleted('penduduks', ['id' => $penduduk->id]);
    }

    public function test_delete_datang_kembali_mengembalikan_status_penduduk_ke_pindah(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventDatang::factory()->create([
            'event_id'         => $event->id,
            'jenis_kedatangan' => 'KEMBALI',
        ]);

        $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->actingAs($actor);
        $this->service->deleteEventDatang($actor, $event);

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $penduduk->id,
            'status_kependudukan_code' => 'PINDAH',
        ]);
        $this->assertNull($penduduk->fresh()->deleted_at);
    }

    public function test_delete_datang_menutup_kk_member_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventDatang::factory()->create([
            'event_id'         => $event->id,
            'jenis_kedatangan' => 'PENDATANG_BARU',
        ]);

        $member = $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->actingAs($actor);
        $this->service->deleteEventDatang($actor, $event);

        $this->assertDatabaseHas('kk_members', [
            'id'     => $member->id,
            'status' => 'KELUAR',
        ]);
    }

    public function test_delete_datang_bukan_draft_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        $event = Event::factory()->verified()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $actor->id,
        ]);

        $this->actingAs($actor);
        $this->expectException(\Exception::class); // AuthorizationException atau DomainException

        $this->service->deleteEventDatang($actor, $event);
    }
}
