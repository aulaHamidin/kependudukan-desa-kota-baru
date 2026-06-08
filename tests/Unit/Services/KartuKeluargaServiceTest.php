<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTOs\KartuKeluargaDTO;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Services\KartuKeluargaService;
use Carbon\Carbon;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class KartuKeluargaServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private KartuKeluargaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(KartuKeluargaService::class);
    }

    private function makeDto(int $rtId, string $noKk = '1234567890123456', string $status = 'AKTIF'): KartuKeluargaDTO
    {
        return new KartuKeluargaDTO(
            noKk: $noKk,
            alamat: 'Jl. Test No. 1',
            rtId: $rtId,
            statusKk: $status,
            tanggalTerbentuk: Carbon::now(),
        );
    }

    // =========================================================================
    // createKartuKeluarga
    // =========================================================================

    public function test_create_kk_baru_normal(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $kk = $this->service->createKartuKeluarga($this->makeDto($territory['rt']->id));

        $this->assertNotNull($kk->id);
        $this->assertDatabaseHas('kartu_keluargas', [
            'no_kk'     => '1234567890123456',
            'status_kk' => 'AKTIF',
        ]);
    }

    public function test_create_kk_no_kk_ada_di_soft_deleted_me_restore_record_lama(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        // Buat KK lama yang sudah soft-deleted
        $kkLama = KartuKeluarga::factory()->create([
            'no_kk'      => '1234567890123456',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $actor->id,
        ]);
        $kkLamaId = $kkLama->id;
        $kkLama->delete();

        $kk = $this->service->createKartuKeluarga($this->makeDto($territory['rt']->id));

        // Record lama di-restore
        $this->assertEquals($kkLamaId, $kk->id);
        $this->assertDatabaseHas('kartu_keluargas', [
            'id'    => $kkLamaId,
            'no_kk' => '1234567890123456',
        ]);
    }

    public function test_create_kk_no_kk_sudah_ada_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        KartuKeluarga::factory()->create([
            'no_kk'      => '1234567890123456',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $actor->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('sudah terdaftar');

        $this->service->createKartuKeluarga($this->makeDto($territory['rt']->id));
    }

    // =========================================================================
    // updateKartuKeluarga
    // =========================================================================

    public function test_update_kk_alamat_berhasil(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $kk = KartuKeluarga::factory()->create([
            'no_kk'      => '1234567890123456',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $actor->id,
        ]);

        $dto = new KartuKeluargaDTO(
            noKk: '1234567890123456',
            alamat: 'Jl. Baru No. 99',
            rtId: $territory['rt']->id,
            statusKk: 'AKTIF',
            tanggalTerbentuk: Carbon::now(),
        );

        $this->service->updateKartuKeluarga($kk->id, $dto);

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'    => $kk->id,
            'alamat' => 'Jl. Baru No. 99',
        ]);
    }

    public function test_update_kk_set_non_aktif_saat_masih_ada_member_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $kk = KartuKeluarga::factory()->create([
            'no_kk'      => '1234567890123456',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $actor->id,
        ]);

        $penduduk = Penduduk::factory()->create([
            'rt_id'                    => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);
        KkMember::factory()->create([
            'kartu_keluarga_id'  => $kk->id,
            'penduduk_id'        => $penduduk->id,
            'is_kepala_keluarga' => true,
            'status'             => 'AKTIF',
            'created_by'         => $actor->id,
        ]);

        $dto = new KartuKeluargaDTO(
            noKk: '1234567890123456',
            alamat: 'Jl. Test',
            rtId: $territory['rt']->id,
            statusKk: 'NON_AKTIF', // Coba set non-aktif
            tanggalTerbentuk: Carbon::now(),
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('anggota aktif');

        $this->service->updateKartuKeluarga($kk->id, $dto);
    }

    // =========================================================================
    // deleteKartuKeluarga
    // =========================================================================

    public function test_delete_kk_tanpa_member_aktif_berhasil(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $kk = KartuKeluarga::factory()->create([
            'no_kk'      => '1234567890123456',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'NON_AKTIF',
            'created_by' => $actor->id,
        ]);

        $result = $this->service->deleteKartuKeluarga($kk->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('kartu_keluargas', ['id' => $kk->id]);
    }

    public function test_delete_kk_dengan_member_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $this->actingAs($actor);

        $kk = KartuKeluarga::factory()->create([
            'no_kk'      => '1234567890123456',
            'rt_id'      => $territory['rt']->id,
            'status_kk'  => 'AKTIF',
            'created_by' => $actor->id,
        ]);

        $penduduk = Penduduk::factory()->create([
            'rt_id'                    => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);
        KkMember::factory()->create([
            'kartu_keluarga_id' => $kk->id,
            'penduduk_id'       => $penduduk->id,
            'status'            => 'AKTIF',
            'created_by'        => $actor->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('anggota aktif');

        $this->service->deleteKartuKeluarga($kk->id);
    }
}
