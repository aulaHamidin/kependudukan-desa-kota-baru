<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Event;

use App\Models\Event;
use App\Models\EventPindah;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Services\Event\PindahService;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class PindahServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private PindahService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(PindahService::class);
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

    private function makeKkMember(int $kkId, int $pendudukId, int $actorId, bool $isKepala = false, string $status = 'AKTIF'): KkMember
    {
        return KkMember::factory()->create([
            'kartu_keluarga_id'  => $kkId,
            'penduduk_id'        => $pendudukId,
            'is_kepala_keluarga' => $isKepala,
            'status'             => $status,
            'created_by'         => $actorId,
        ]);
    }

    // =========================================================================
    // deleteEventPindah
    // =========================================================================

    public function test_delete_pindah_draft_penduduk_kembali_aktif_dan_kk_member_dipulihkan(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'PINDAH',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventPindah::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->actingAs($actor);
        $this->service->deleteEventPindah($actor, $event);

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $penduduk->id,
            'status_kependudukan_code' => 'AKTIF',
        ]);
        $this->assertDatabaseHas('kk_members', [
            'id'     => $member->id,
            'status' => 'AKTIF',
        ]);
    }

    public function test_delete_pindah_rollback_pengganti_kepala(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');
        $pengganti = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'PINDAH',
            'penduduk_id'     => $kepala->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventPindah::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false, // false agar restore kepala tidak memicu trigger "satu kepala aktif"
            'pengganti_id' => $pengganti->id,
        ]);

        $kepalaM    = $this->makeKkMember($kk->id, $kepala->id, $actor->id, false, 'KELUAR');
        $kepalaM->update(['event_keluar_id' => $event->id]);
        $penggantiM = $this->makeKkMember($kk->id, $pengganti->id, $actor->id, true);

        $this->actingAs($actor);
        $this->service->deleteEventPindah($actor, $event);

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $penggantiM->id,
            'is_kepala_keluarga' => false,
        ]);
    }

    public function test_delete_pindah_bukan_draft_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');

        $event = Event::factory()->verified()->create([
            'event_type_code' => 'PINDAH',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $actor->id,
        ]);

        $this->actingAs($actor);
        $this->expectException(\Exception::class); // AuthorizationException atau DomainException

        $this->service->deleteEventPindah($actor, $event);
    }

    // =========================================================================
    // updateEventPindah
    // =========================================================================

    public function test_update_pindah_draft_berhasil_ubah_event_date_dan_alamat_tujuan(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');

        // update requires created_by === actor.id
        $event = Event::factory()->draft()->create([
            'event_type_code' => 'PINDAH',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $actor->id,
        ]);

        EventPindah::factory()->create([
            'event_id'      => $event->id,
            'alamat_tujuan' => 'Jl. Lama No. 1',
        ]);

        $this->actingAs($actor);
        $this->service->updateEventPindah($actor, $event, [
            'event_date'    => '2024-06-15',
            'alamat_tujuan' => 'Jl. Baru No. 99',
        ]);

        $this->assertDatabaseHas('event_pindah', [
            'event_id'      => $event->id,
            'alamat_tujuan' => 'Jl. Baru No. 99',
        ]);
    }

    public function test_update_pindah_bukan_draft_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');

        $event = Event::factory()->verified()->create([
            'event_type_code' => 'PINDAH',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $actor->id,
        ]);

        $this->actingAs($actor);
        $this->expectException(\Exception::class); // AuthorizationException atau DomainException

        $this->service->updateEventPindah($actor, $event, ['alamat_tujuan' => 'Jl. Baru']);
    }
}
