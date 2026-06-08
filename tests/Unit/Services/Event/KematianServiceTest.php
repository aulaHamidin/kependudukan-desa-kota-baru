<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Event;

use App\Models\Event;
use App\Models\EventKematian;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Services\Event\KematianService;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class KematianServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private KematianService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(KematianService::class);
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
    // deleteEventKematian
    // =========================================================================

    public function test_delete_kematian_draft_penduduk_kembali_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $almarhum->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $almarhum->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->actingAs($actor);
        $this->service->deleteEventKematian($actor, $event);

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $almarhum->id,
            'status_kependudukan_code' => 'AKTIF',
        ]);
    }

    public function test_delete_kematian_draft_kk_member_dipulihkan_dengan_was_kepala(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $almarhum->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => true,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $almarhum->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->actingAs($actor);
        $this->service->deleteEventKematian($actor, $event);

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $member->id,
            'status'             => 'AKTIF',
            'is_kepala_keluarga' => true,
        ]);
    }

    public function test_delete_kematian_rollback_pengganti_kepala(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');
        $pengganti = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $almarhum->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false, // false agar restore almarhum tidak memicu trigger "satu kepala aktif"
            'pengganti_id' => $pengganti->id,
        ]);

        $almarhumMember  = $this->makeKkMember($kk->id, $almarhum->id, $actor->id, false, 'KELUAR');
        $almarhumMember->update(['event_keluar_id' => $event->id]);
        $penggantiMember = $this->makeKkMember($kk->id, $pengganti->id, $actor->id, true);

        $this->actingAs($actor);
        $this->service->deleteEventKematian($actor, $event);

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $penggantiMember->id,
            'is_kepala_keluarga' => false,
        ]);
    }

    public function test_delete_kematian_kk_direaktivasi_jika_sempat_non_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id, 'NON_AKTIF');
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $almarhum->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $almarhum->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->actingAs($actor);
        $this->service->deleteEventKematian($actor, $event);

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'AKTIF',
        ]);
    }

    public function test_delete_kematian_bukan_draft_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        $event = Event::factory()->verified()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $actor->id,
        ]);

        $this->actingAs($actor);
        $this->expectException(\Exception::class); // AuthorizationException atau DomainException

        $this->service->deleteEventKematian($actor, $event);
    }

    // =========================================================================
    // updateEventKematian
    // =========================================================================

    public function test_update_kematian_draft_berhasil_ubah_event_date_dan_tempat(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        // update requires created_by === actor.id
        $event = Event::factory()->draft()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $actor->id,
        ]);

        EventKematian::factory()->create([
            'event_id'         => $event->id,
            'tempat_meninggal' => 'Rumah',
        ]);

        $this->actingAs($actor);
        $this->service->updateEventKematian($actor, $event, [
            'event_date'       => '2024-06-15',
            'tempat_meninggal' => 'Rumah Sakit',
        ]);

        $this->assertDatabaseHas('event_kematian', [
            'event_id'         => $event->id,
            'tempat_meninggal' => 'Rumah Sakit',
        ]);
    }

    public function test_update_kematian_bukan_draft_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        $event = Event::factory()->verified()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'created_by'      => $actor->id,
        ]);

        $this->actingAs($actor);
        $this->expectException(\Exception::class); // AuthorizationException atau DomainException

        $this->service->updateEventKematian($actor, $event, ['event_date' => '2024-01-01']);
    }
}
