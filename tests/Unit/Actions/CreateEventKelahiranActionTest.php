<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Event\CreateEventKelahiranAction;
use App\DTOs\Event\KelahiranDTO;
use App\Enums\StatusKelahiran;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\Rt;
use App\Models\Rw;
use Carbon\Carbon;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class CreateEventKelahiranActionTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private CreateEventKelahiranAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->action = app(CreateEventKelahiranAction::class);
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

    private function makeDto(
        int $rtId,
        int $kkId,
        int $actorId,
        StatusKelahiran $status = StatusKelahiran::HIDUP,
        ?int $ayahId = null,
        ?int $ibuId = null,
        ?string $namaAyah = null,
        ?string $namaIbu = null
    ): KelahiranDTO {
        return new KelahiranDTO(
            eventTypeCode: 'KELAHIRAN',
            rtId: $rtId,
            eventDate: Carbon::now()->subDays(1),
            keterangan: null,
            namaBayi: 'Bayi Test',
            jenisKelamin: 'L',
            statusKelahiran: $status,
            agamaId: 'ISLAM',
            ayahId: $ayahId,
            ibuId: $ibuId,
            namaAyah: $namaAyah ?? ($ayahId ? null : 'Bapak Test'),
            namaIbu: $namaIbu ?? ($ibuId ? null : 'Ibu Test'),
            tempatLahir: 'Bandung',
            jamLahir: null,
            anakKe: null,
            beratBadanKg: null,
            panjangBadanCm: null,
            penolongKelahiran: null,
            namaPenolong: null,
            kkTujuanId: $kkId,
            createdBy: $actorId,
        );
    }

    // =========================================================================
    // HAPPY PATH
    // =========================================================================

    public function test_alur_sukses_event_dibuat_bayi_aktif_dan_kk_member_anak(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $this->actingAs($actor);
        $event = $this->action->execute($this->makeDto($territory['rt']->id, $kk->id, $actor->id));

        $bayi = Penduduk::find($event->penduduk_id);
        $this->assertNotNull($bayi);
        $this->assertEquals('AKTIF', $bayi->status_kependudukan_code);

        $this->assertDatabaseHas('kk_members', [
            'kartu_keluarga_id'      => $kk->id,
            'penduduk_id'            => $bayi->id,
            'hubungan_keluarga_code' => 'ANAK',
            'is_kepala_keluarga'     => false,
            'status'                 => 'AKTIF',
        ]);
    }

    public function test_lahir_mati_bayi_dibuat_tapi_kk_member_tidak_dibuat(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $this->actingAs($actor);
        $event = $this->action->execute($this->makeDto($territory['rt']->id, $kk->id, $actor->id, StatusKelahiran::MATI));

        $bayi = Penduduk::find($event->penduduk_id);
        $this->assertNotNull($bayi);

        $this->assertDatabaseMissing('kk_members', [
            'penduduk_id'       => $bayi->id,
            'kartu_keluarga_id' => $kk->id,
        ]);
    }

    // =========================================================================
    // VALIDATION EXCEPTIONS
    // =========================================================================

    public function test_ayah_by_id_bukan_rt_yang_sama_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk             = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala         = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        // Ayah dari RT lain dalam desa yang SAMA (agar TerritoryScope tidak menyaring ayah)
        $otherRw    = Rw::factory()->create(['desa_id' => $territory['desa']->id]);
        $otherRt    = Rt::factory()->create(['rw_id' => $otherRw->id]);
        $ayah       = $this->makePenduduk($otherRt->id, $actor->id, 'AKTIF');
        $kkLain     = $this->makeKk($otherRt->id, $actor->id);
        $kepalaLain = $this->makePenduduk($otherRt->id, $actor->id);
        $this->makeKkMember($kkLain->id, $kepalaLain->id, $actor->id, true);
        $this->makeKkMember($kkLain->id, $ayah->id, $actor->id, false);

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('RT ayah');

        $this->action->execute($this->makeDto($territory['rt']->id, $kk->id, $actor->id, ayahId: $ayah->id));
    }

    public function test_ibu_by_id_bukan_kk_yang_sama_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kkLain    = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $ibu       = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $kepalaDua = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kkLain->id, $kepalaDua->id, $actor->id, true);
        $this->makeKkMember($kkLain->id, $ibu->id, $actor->id, false);

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('KK yang sama');

        $this->action->execute($this->makeDto($territory['rt']->id, $kk->id, $actor->id, ibuId: $ibu->id));
    }

    public function test_kk_tujuan_tanpa_kepala_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('kepala keluarga aktif');

        $this->action->execute($this->makeDto($territory['rt']->id, $kk->id, $actor->id));
    }

    public function test_kk_tujuan_bukan_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id, 'NON_AKTIF');

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('tidak aktif');

        $this->action->execute($this->makeDto($territory['rt']->id, $kk->id, $actor->id));
    }
}
