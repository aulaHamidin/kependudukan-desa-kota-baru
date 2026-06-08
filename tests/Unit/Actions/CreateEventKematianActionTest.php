<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Event\CreateEventKematianAction;
use App\DTOs\Event\KematianDTO;
use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use Carbon\Carbon;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class CreateEventKematianActionTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private CreateEventKematianAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->action = app(CreateEventKematianAction::class);
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

    private function makeDto(int $rtId, int $pendudukId, int $actorId, ?int $kkId = null, ?int $penggantiId = null): KematianDTO
    {
        return new KematianDTO(
            rtId: $rtId,
            pendudukId: $pendudukId,
            eventDate: Carbon::now()->subDays(1),
            tempatMeninggal: 'Rumah Sakit',
            kkId: $kkId,
            penggantiKepalaId: $penggantiId,
            createdBy: $actorId,
        );
    }

    // =========================================================================
    // HAPPY PATH
    // =========================================================================

    public function test_alur_sukses_penduduk_menjadi_meninggal_dan_kk_member_ditutup(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->actingAs($actor);
        $event = $this->action->execute($this->makeDto($territory['rt']->id, $penduduk->id, $actor->id, $kk->id));

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $penduduk->id,
            'status_kependudukan_code' => 'MENINGGAL',
        ]);
        $this->assertDatabaseHas('kk_members', [
            'penduduk_id'       => $penduduk->id,
            'kartu_keluarga_id' => $kk->id,
            'status'            => 'KELUAR',
        ]);
        $this->assertNotNull($event->id);
        $this->assertEquals('KEMATIAN', $event->event_type_code);
    }

    public function test_alur_sukses_was_kepala_tersimpan_di_event_kematian(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $this->actingAs($actor);
        $event = $this->action->execute($this->makeDto($territory['rt']->id, $kepala->id, $actor->id, $kk->id));

        $this->assertDatabaseHas('event_kematian', [
            'event_id'   => $event->id,
            'was_kepala' => true,
        ]);
    }

    public function test_kepala_wafat_dengan_pengganti_menjadi_kepala_baru(): void
    {
        $territory  = $this->createTerritory();
        $actor      = $this->adminDesa($territory['desa']);
        $kk         = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala     = $this->makePenduduk($territory['rt']->id, $actor->id);
        $pengganti  = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);
        $penggantiM = $this->makeKkMember($kk->id, $pengganti->id, $actor->id, false);

        $this->actingAs($actor);
        $this->action->execute($this->makeDto($territory['rt']->id, $kepala->id, $actor->id, $kk->id, $pengganti->id));

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $penggantiM->id,
            'is_kepala_keluarga' => true,
        ]);
    }

    public function test_kepala_wafat_tanpa_pengganti_kk_menjadi_non_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $this->actingAs($actor);
        $this->action->execute($this->makeDto($territory['rt']->id, $kepala->id, $actor->id, $kk->id));

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'NON_AKTIF',
        ]);
    }

    public function test_anggota_terakhir_wafat_kk_menjadi_non_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $anggota   = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $anggota->id, $actor->id, false);

        $this->actingAs($actor);
        $this->action->execute($this->makeDto($territory['rt']->id, $anggota->id, $actor->id, $kk->id));

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'NON_AKTIF',
        ]);
    }

    // =========================================================================
    // VALIDATION EXCEPTIONS
    // =========================================================================

    public function test_penduduk_bukan_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('bukan AKTIF');

        $this->action->execute($this->makeDto($territory['rt']->id, $penduduk->id, $actor->id));
    }

    public function test_penduduk_punya_event_draft_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        Event::factory()->draft()->create([
            'event_type_code' => 'PINDAH',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'created_by'      => $actor->id,
        ]);

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('event aktif');

        $this->action->execute($this->makeDto($territory['rt']->id, $penduduk->id, $actor->id));
    }

    public function test_tanggal_event_sebelum_event_verified_terakhir_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        Event::factory()->verified()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'event_date'      => now()->subDays(5),
            'created_by'      => $actor->id,
        ]);

        $dto = new KematianDTO(
            rtId: $territory['rt']->id,
            pendudukId: $penduduk->id,
            eventDate: Carbon::now()->subDays(10), // Sebelum event verified
            tempatMeninggal: 'Rumah Sakit',
            createdBy: $actor->id,
        );

        $this->actingAs($actor);
        $this->expectException(DomainException::class);

        $this->action->execute($dto);
    }
}
