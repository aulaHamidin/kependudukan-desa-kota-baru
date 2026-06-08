<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Services\KkMemberService;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class KkMemberServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private KkMemberService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(KkMemberService::class);
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
    // addMember
    // =========================================================================

    public function test_add_member_sukses_menambahkan_anggota_baru(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        $this->actingAs($actor);

        $member = $this->service->addMember($actor, [
            'kartu_keluarga_id'      => $kk->id,
            'penduduk_id'            => $penduduk->id,
            'hubungan_keluarga_code' => 'ANAK',
            'is_kepala_keluarga'     => false,
            'tanggal_masuk'          => now()->toDateString(),
        ]);

        $this->assertNotNull($member->id);
        $this->assertEquals('AKTIF', $member->status);
        $this->assertDatabaseHas('kk_members', [
            'kartu_keluarga_id' => $kk->id,
            'penduduk_id'       => $penduduk->id,
            'status'            => 'AKTIF',
        ]);
    }

    public function test_add_member_mengaktifkan_kk_non_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id, 'NON_AKTIF');
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        $this->actingAs($actor);

        $this->service->addMember($actor, [
            'kartu_keluarga_id'      => $kk->id,
            'penduduk_id'            => $penduduk->id,
            'hubungan_keluarga_code' => 'KEPALA_KELUARGA',
            'is_kepala_keluarga'     => false,
            'tanggal_masuk'          => now()->toDateString(),
            'status'                 => 'AKTIF',
        ]);

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'AKTIF',
        ]);
    }

    public function test_add_member_penduduk_meninggal_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');

        $this->actingAs($actor);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('tidak aktif');

        $this->service->addMember($actor, [
            'kartu_keluarga_id'      => $kk->id,
            'penduduk_id'            => $penduduk->id,
            'hubungan_keluarga_code' => 'ANAK',
            'is_kepala_keluarga'     => false,
            'tanggal_masuk'          => now()->toDateString(),
        ]);
    }

    public function test_add_member_penduduk_sudah_punya_keanggotaan_aktif_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk1       = $this->makeKk($territory['rt']->id, $actor->id);
        $kk2       = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        $this->makeKkMember($kk1->id, $penduduk->id, $actor->id);
        $this->actingAs($actor);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('sudah terdaftar');

        $this->service->addMember($actor, [
            'kartu_keluarga_id'      => $kk2->id,
            'penduduk_id'            => $penduduk->id,
            'hubungan_keluarga_code' => 'ANAK',
            'is_kepala_keluarga'     => false,
            'tanggal_masuk'          => now()->toDateString(),
        ]);
    }

    public function test_add_member_is_kepala_saat_kk_sudah_punya_kepala_melempar_exception(): void
    {
        $territory    = $this->createTerritory();
        $actor        = $this->adminRt($territory['rt']);
        $kk           = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala       = $this->makePenduduk($territory['rt']->id, $actor->id);
        $pendudukBaru = $this->makePenduduk($territory['rt']->id, $actor->id);

        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);
        $this->actingAs($actor);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('kepala keluarga aktif');

        $this->service->addMember($actor, [
            'kartu_keluarga_id'      => $kk->id,
            'penduduk_id'            => $pendudukBaru->id,
            'hubungan_keluarga_code' => 'KEPALA_KELUARGA',
            'is_kepala_keluarga'     => true,
            'tanggal_masuk'          => now()->toDateString(),
        ]);
    }

    public function test_add_member_is_kepala_saat_kk_belum_punya_kepala_sukses(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);

        $this->actingAs($actor);

        $member = $this->service->addMember($actor, [
            'kartu_keluarga_id'      => $kk->id,
            'penduduk_id'            => $penduduk->id,
            'hubungan_keluarga_code' => 'KEPALA_KELUARGA',
            'is_kepala_keluarga'     => true,
            'tanggal_masuk'          => now()->toDateString(),
        ]);

        $this->assertTrue((bool) $member->is_kepala_keluarga);
    }

    // =========================================================================
    // removeMember
    // =========================================================================

    public function test_remove_member_mengubah_status_jadi_keluar(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $anggota   = $this->makePenduduk($territory['rt']->id, $actor->id);

        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);
        $member = $this->makeKkMember($kk->id, $anggota->id, $actor->id, false);

        $this->actingAs($actor);

        $this->service->removeMember($actor, $member, [
            'status'         => 'KELUAR',
            'tanggal_keluar' => now()->toDateString(),
            'alasan_keluar'  => 'Test keluar',
        ]);

        $this->assertDatabaseHas('kk_members', [
            'id'     => $member->id,
            'status' => 'KELUAR',
        ]);
    }

    public function test_remove_member_terakhir_menonaktifkan_kk(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);
        $member    = $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->actingAs($actor);

        $this->service->removeMember($actor, $member, [
            'status'         => 'KELUAR',
            'tanggal_keluar' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'NON_AKTIF',
        ]);
    }

    public function test_remove_member_bukan_terakhir_kk_tetap_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $kepala    = $this->makePenduduk($territory['rt']->id, $actor->id);
        $anggota   = $this->makePenduduk($territory['rt']->id, $actor->id);

        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);
        $member = $this->makeKkMember($kk->id, $anggota->id, $actor->id, false);

        $this->actingAs($actor);

        $this->service->removeMember($actor, $member, [
            'status'         => 'KELUAR',
            'tanggal_keluar' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'AKTIF',
        ]);
    }

    // =========================================================================
    // setKepalaKeluarga
    // =========================================================================

    public function test_set_kepala_keluarga_menggantikan_kepala_lama(): void
    {
        $territory         = $this->createTerritory();
        $actor             = $this->adminRt($territory['rt']);
        $kk                = $this->makeKk($territory['rt']->id, $actor->id);
        $kepalaLama        = $this->makePenduduk($territory['rt']->id, $actor->id);
        $kepalaBaruPenduduk = $this->makePenduduk($territory['rt']->id, $actor->id);

        $kepalaLamaMember = $this->makeKkMember($kk->id, $kepalaLama->id, $actor->id, true);
        $kepalaBaru       = $this->makeKkMember($kk->id, $kepalaBaruPenduduk->id, $actor->id, false);

        $this->actingAs($actor);

        $this->service->setKepalaKeluarga($actor, $kepalaBaru);

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $kepalaLamaMember->id,
            'is_kepala_keluarga' => false,
        ]);
        $this->assertDatabaseHas('kk_members', [
            'id'                 => $kepalaBaru->id,
            'is_kepala_keluarga' => true,
        ]);
    }

    public function test_set_kepala_keluarga_sudah_kepala_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id);
        $member    = $this->makeKkMember($kk->id, $penduduk->id, $actor->id, true);

        $this->actingAs($actor);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('sudah menjadi kepala');

        $this->service->setKepalaKeluarga($actor, $member);
    }
}
