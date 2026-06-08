<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Event\CreateEventPindahAction;
use App\DTOs\Event\PindahDTO;
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

class CreateEventPindahActionTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private CreateEventPindahAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->action = app(CreateEventPindahAction::class);
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

    private function makeDto(int $rtId, int $pendudukId, int $actorId, ?int $kkId = null, ?int $penggantiId = null): PindahDTO
    {
        return new PindahDTO(
            rtId: $rtId,
            eventDate: Carbon::now()->subDays(1),
            pendudukId: $pendudukId,
            alamatTujuan: 'Jl. Tujuan No. 1',
            desaTujuan: 'Desa Tujuan',
            kecamatanTujuan: 'Kecamatan A',
            kabupatenTujuan: 'Kabupaten B',
            provinsiTujuan: 'Jawa Barat',
            alasanPindah: 'Pekerjaan',
            jenisKepindahan: 'INDIVIDU',
            kkId: $kkId,
            penggantiKepalaId: $penggantiId,
            createdBy: $actorId,
        );
    }

    // =========================================================================
    // HAPPY PATH
    // =========================================================================

    public function test_alur_sukses_penduduk_menjadi_pindah_dan_kk_member_ditutup(): void
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
            'status_kependudukan_code' => 'PINDAH',
        ]);
        $this->assertDatabaseHas('kk_members', [
            'penduduk_id'       => $penduduk->id,
            'kartu_keluarga_id' => $kk->id,
            'status'            => 'KELUAR',
        ]);
        $this->assertEquals('PINDAH', $event->event_type_code);
    }

    public function test_was_kepala_tersimpan_di_event_pindah(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $this->actingAs($actor);
        $event = $this->action->execute($this->makeDto($territory['rt']->id, $kepala->id, $actor->id, $kk->id));

        $this->assertDatabaseHas('event_pindah', [
            'event_id'   => $event->id,
            'was_kepala' => true,
        ]);
    }

    public function test_kepala_pindah_dengan_pengganti_menjadi_kepala_baru(): void
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

    public function test_kepala_pindah_tanpa_pengganti_kk_menjadi_non_aktif(): void
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

    public function test_anggota_terakhir_pindah_kk_menjadi_non_aktif(): void
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
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('bukan AKTIF');

        $this->action->execute($this->makeDto($territory['rt']->id, $penduduk->id, $actor->id));
    }

    public function test_penduduk_punya_event_draft_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        Event::factory()->draft()->create([
            'event_type_code' => 'KEMATIAN',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'created_by'      => $actor->id,
        ]);

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('DRAFT');

        $this->action->execute($this->makeDto($territory['rt']->id, $penduduk->id, $actor->id));
    }
}
