<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Event\CreateEventDatangAction;
use App\DTOs\Event\DatangDTO;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use Carbon\Carbon;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class CreateEventDatangActionTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private CreateEventDatangAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->action = app(CreateEventDatangAction::class);
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

    private function makePendatangBaruDto(int $rtId, int $kkId, int $actorId, array $extra = []): DatangDTO
    {
        return new DatangDTO(
            jenisKedatangan: 'PENDATANG_BARU',
            tanggalDatang: Carbon::now()->subDays(1),
            alamatAsal: 'Jl. Asal No. 1',
            kkTujuanId: $kkId,
            alasanDatang: 'Pekerjaan',
            nik: '1234567890123456',
            namaLengkap: 'Test Penduduk',
            jenisKelamin: 'L',
            tempatLahir: 'Bandung',
            tglLahir: Carbon::parse('1990-01-01'),
            agamaId: 'ISLAM',
            statusPerkawinan: 'Belum Kawin',
            rtId: $rtId,
            hubunganKeluargaCode: 'LAINNYA',
            createdBy: $actorId,
            payload: $extra,
        );
    }

    private function makeKembaliDto(int $rtId, int $pendudukId, int $kkId, int $actorId): DatangDTO
    {
        return new DatangDTO(
            jenisKedatangan: 'KEMBALI',
            tanggalDatang: Carbon::now()->subDays(1),
            alamatAsal: 'Jl. Asal No. 1',
            kkTujuanId: $kkId,
            alasanDatang: 'Kembali ke kampung',
            rtId: $rtId,
            pendudukId: $pendudukId,
            hubunganKeluargaCode: 'LAINNYA',
            createdBy: $actorId,
        );
    }

    // =========================================================================
    // PENDATANG BARU — ALUR SUKSES
    // =========================================================================

    public function test_pendatang_baru_membuat_penduduk_aktif_dan_kk_member(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $this->actingAs($actor);
        $this->action->execute($this->makePendatangBaruDto($territory['rt']->id, $kk->id, $actor->id));

        $pendudukBaru = Penduduk::where('nik', '1234567890123456')->first();
        $this->assertNotNull($pendudukBaru);
        $this->assertEquals('AKTIF', $pendudukBaru->status_kependudukan_code);

        $this->assertDatabaseHas('kk_members', [
            'kartu_keluarga_id' => $kk->id,
            'penduduk_id'       => $pendudukBaru->id,
            'status'            => 'AKTIF',
        ]);
    }

    public function test_pendatang_baru_dengan_nik_di_soft_deleted_me_restore_record_lama(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        // Buat penduduk lama yang sudah soft-deleted
        $pendudukLama = Penduduk::factory()->create([
            'nik'                      => '1234567890123456',
            'rt_id'                    => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);
        $pendudukLamaId = $pendudukLama->id;
        $pendudukLama->delete();

        $dto = $this->makePendatangBaruDto($territory['rt']->id, $kk->id, $actor->id, [
            '_restore_penduduk_id' => $pendudukLamaId,
        ]);

        $this->actingAs($actor);
        $this->action->execute($dto);

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $pendudukLamaId,
            'status_kependudukan_code' => 'AKTIF',
        ]);
        $this->assertEquals(1, Penduduk::where('nik', '1234567890123456')->count());
    }

    // =========================================================================
    // KEMBALI — ALUR SUKSES
    // =========================================================================

    public function test_kembali_mengaktifkan_penduduk_pindah_menjadi_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');

        $this->actingAs($actor);
        $this->action->execute($this->makeKembaliDto($territory['rt']->id, $penduduk->id, $kk->id, $actor->id));

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $penduduk->id,
            'status_kependudukan_code' => 'AKTIF',
        ]);
        $this->assertDatabaseHas('kk_members', [
            'kartu_keluarga_id' => $kk->id,
            'penduduk_id'       => $penduduk->id,
            'status'            => 'AKTIF',
        ]);
    }

    // =========================================================================
    // VALIDATION EXCEPTIONS
    // =========================================================================

    public function test_kembali_untuk_penduduk_bukan_pindah_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('PINDAH');

        $this->action->execute($this->makeKembaliDto($territory['rt']->id, $penduduk->id, $kk->id, $actor->id));
    }

    public function test_pendatang_baru_kk_tanpa_kepala_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        // Tidak ada kepala keluarga di KK ini

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('kepala keluarga aktif');

        $this->action->execute($this->makePendatangBaruDto($territory['rt']->id, $kk->id, $actor->id));
    }
}
