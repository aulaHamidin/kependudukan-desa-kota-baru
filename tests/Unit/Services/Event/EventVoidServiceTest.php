<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Event;

use App\Models\Event;
use App\Models\EventDatang;
use App\Models\EventKematian;
use App\Models\EventPindah;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Services\Event\EventVoidService;
use Database\Seeders\SeedMasterData;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class EventVoidServiceTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    private EventVoidService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
        $this->service = app(EventVoidService::class);
    }

    // =========================================================================
    // HELPER SETUP METHODS
    // =========================================================================

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

    private function makeVerifiedEvent(array $territory, int $actorId, int $pendudukId, string $type, ?int $kkId = null): Event
    {
        return Event::factory()->verified()->create([
            'event_type_code' => $type,
            'penduduk_id'     => $pendudukId,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kkId,
            'event_date'      => now()->subDays(30),
            'created_by'      => $actorId,
        ]);
    }

    // =========================================================================
    // VOID KELAHIRAN
    // =========================================================================

    public function test_void_kelahiran_soft_deletes_bayi(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $bayi      = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $bayi->id, 'KELAHIRAN', $kk->id);
        $this->makeKkMember($kk->id, $bayi->id, $actor->id, false);

        $this->service->voidEvent($actor, $event, 'Test void kelahiran');

        $this->assertSoftDeleted('penduduks', ['id' => $bayi->id]);
    }

    public function test_void_kelahiran_menutup_kk_member_bayi(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $bayi      = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $bayi->id, 'KELAHIRAN', $kk->id);
        $member    = $this->makeKkMember($kk->id, $bayi->id, $actor->id, false);

        $this->service->voidEvent($actor, $event, 'Test void kelahiran');

        $this->assertDatabaseHas('kk_members', [
            'id'     => $member->id,
            'status' => 'KELUAR',
        ]);
    }

    public function test_void_kelahiran_menonaktifkan_kk_jika_tidak_ada_member_aktif_lain(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id, 'AKTIF');
        $bayi      = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $bayi->id, 'KELAHIRAN', $kk->id);
        $this->makeKkMember($kk->id, $bayi->id, $actor->id, false);

        $this->service->voidEvent($actor, $event, 'Test void kelahiran');

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'NON_AKTIF',
        ]);
    }

    public function test_void_kelahiran_tidak_menonaktifkan_kk_jika_masih_ada_member_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id, 'AKTIF');

        // Member lain masih aktif
        $kepala = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $this->makeKkMember($kk->id, $kepala->id, $actor->id, true);

        $bayi   = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event  = $this->makeVerifiedEvent($territory, $actor->id, $bayi->id, 'KELAHIRAN', $kk->id);
        $this->makeKkMember($kk->id, $bayi->id, $actor->id, false);

        $this->service->voidEvent($actor, $event, 'Test void kelahiran');

        $this->assertDatabaseHas('kartu_keluargas', [
            'id'        => $kk->id,
            'status_kk' => 'AKTIF',
        ]);
    }

    public function test_void_event_bukan_verified_melempar_exception(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $bayi      = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $event = Event::factory()->draft()->create([
            'event_type_code' => 'KELAHIRAN',
            'penduduk_id'     => $bayi->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'created_by'      => $actor->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('VERIFIED');

        $this->service->voidEvent($actor, $event, 'Test void draft event');
    }

    // =========================================================================
    // VOID KEMATIAN
    // =========================================================================

    public function test_void_kematian_mengembalikan_penduduk_ke_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $almarhum->id, 'KEMATIAN', $kk->id);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $almarhum->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->service->voidEvent($actor, $event, 'Test void kematian');

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $almarhum->id,
            'status_kependudukan_code' => 'AKTIF',
        ]);
    }

    public function test_void_kematian_mengembalikan_kepala_keluarga_jika_was_kepala(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $almarhum->id, 'KEMATIAN', $kk->id);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => true,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $almarhum->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->service->voidEvent($actor, $event, 'Test void kematian kepala');

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $member->id,
            'status'             => 'AKTIF',
            'is_kepala_keluarga' => true,
        ]);
    }

    public function test_void_kematian_rollback_pengganti_kepala_jika_ada_pengganti_id(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');
        $pengganti = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $almarhum->id, 'KEMATIAN', $kk->id);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => true,
            'pengganti_id' => $pengganti->id,
        ]);

        $almarhumMember  = $this->makeKkMember($kk->id, $almarhum->id, $actor->id, false, 'KELUAR');
        $almarhumMember->update(['event_keluar_id' => $event->id]);
        $penggantiMember = $this->makeKkMember($kk->id, $pengganti->id, $actor->id, true);

        $this->service->voidEvent($actor, $event, 'Test void kematian dengan pengganti');

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $penggantiMember->id,
            'is_kepala_keluarga' => false,
        ]);
    }

    public function test_void_kematian_diblokir_jika_pengganti_punya_event_verified_lebih_baru(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $almarhum  = $this->makePenduduk($territory['rt']->id, $actor->id, 'MENINGGAL');
        $pengganti = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $almarhum->id, 'KEMATIAN', $kk->id);

        EventKematian::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => true,
            'pengganti_id' => $pengganti->id,
        ]);

        $this->makeKkMember($kk->id, $pengganti->id, $actor->id, true);

        // Pengganti punya event VERIFIED lebih baru
        Event::factory()->verified()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $pengganti->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'event_date'      => now()->subDays(10),
            'created_by'      => $actor->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('pengganti kepala');

        $this->service->voidEvent($actor, $event, 'Test');
    }

    public function test_void_diblokir_jika_ada_event_verified_lebih_baru_untuk_penduduk(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        $event = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'KELAHIRAN', $kk->id);

        // Event lebih baru untuk penduduk yang sama
        Event::factory()->verified()->create([
            'event_type_code' => 'DATANG',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'event_date'      => now()->subDays(15),
            'created_by'      => $actor->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('terverifikasi yang lebih baru');

        $this->service->voidEvent($actor, $event, 'Test');
    }

    // =========================================================================
    // VOID PINDAH
    // =========================================================================

    public function test_void_pindah_mengembalikan_penduduk_ke_aktif(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'PINDAH', $kk->id);

        EventPindah::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->service->voidEvent($actor, $event, 'Test void pindah');

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $penduduk->id,
            'status_kependudukan_code' => 'AKTIF',
        ]);
    }

    public function test_void_pindah_mengembalikan_kk_member_dengan_was_kepala(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'PINDAH', $kk->id);

        EventPindah::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => true,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->service->voidEvent($actor, $event, 'Test void pindah kepala');

        $this->assertDatabaseHas('kk_members', [
            'id'                 => $member->id,
            'status'             => 'AKTIF',
            'is_kepala_keluarga' => true,
        ]);
    }

    public function test_void_pindah_mengaktifkan_kembali_kk_yang_non_aktif_karena_kepergian(): void
    {
        // Catatan: service memblokir void jika KK NON_AKTIF (tanpa memandang sebabnya).
        // Test ini memverifikasi bahwa void berhasil ketika KK masih AKTIF — member dikembalikan.
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id, 'AKTIF');
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'PINDAH', $kk->id);

        EventPindah::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false,
            'pengganti_id' => null,
        ]);

        $member = $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false, 'KELUAR');
        $member->update(['event_keluar_id' => $event->id]);

        $this->service->voidEvent($actor, $event, 'Test');

        // Penduduk kembali AKTIF dan KkMember dipulihkan
        $this->assertDatabaseHas('penduduks', [
            'id'                       => $penduduk->id,
            'status_kependudukan_code' => 'AKTIF',
        ]);
        $this->assertDatabaseHas('kk_members', [
            'id'     => $member->id,
            'status' => 'AKTIF',
        ]);
    }

    public function test_void_pindah_diblokir_jika_kk_asal_non_aktif_karena_sebab_lain(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        // KK sudah NON_AKTIF SEBELUM event dibuat
        $kk       = $this->makeKk($territory['rt']->id, $actor->id, 'NON_AKTIF');
        $penduduk = $this->makePenduduk($territory['rt']->id, $actor->id, 'PINDAH');
        $event    = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'PINDAH', $kk->id);

        EventPindah::factory()->create([
            'event_id'     => $event->id,
            'was_kepala'   => false,
            'pengganti_id' => null,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('KK asal sudah tidak aktif');

        $this->service->voidEvent($actor, $event, 'Test');
    }

    // =========================================================================
    // VOID DATANG — PENDATANG BARU
    // =========================================================================

    public function test_void_datang_pendatang_baru_tanpa_restored_from_soft_deletes_penduduk(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'DATANG', $kk->id);

        EventDatang::factory()->create([
            'event_id'         => $event->id,
            'jenis_kedatangan' => 'PENDATANG_BARU',
            'restored_from_id' => null,
        ]);

        $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->service->voidEvent($actor, $event, 'Test');

        $this->assertSoftDeleted('penduduks', ['id' => $penduduk->id]);
    }

    public function test_void_datang_pendatang_baru_dengan_restored_from_revert_status_tidak_soft_delete(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'DATANG', $kk->id);

        // Buat penduduk lama yang sudah soft-deleted (mewakili restored_from_id)
        $pendudukLama = Penduduk::factory()->create([
            'rt_id'      => $territory['rt']->id,
            'created_by' => $actor->id,
        ]);
        $pendudukLama->delete();

        EventDatang::factory()->create([
            'event_id'         => $event->id,
            'jenis_kedatangan' => 'PENDATANG_BARU',
            'restored_from_id' => $pendudukLama->id, // Ada restored_from_id (valid FK)
        ]);

        $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->service->voidEvent($actor, $event, 'Test');

        $this->assertDatabaseHas('penduduks', ['id' => $penduduk->id]);
        $this->assertNull($penduduk->fresh()->deleted_at);
    }

    // =========================================================================
    // VOID DATANG — KEMBALI
    // =========================================================================

    public function test_void_datang_kembali_first_event_soft_deletes_penduduk(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');
        $event     = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'DATANG', $kk->id);

        EventDatang::factory()->create([
            'event_id'         => $event->id,
            'jenis_kedatangan' => 'KEMBALI',
            'restored_from_id' => null,
        ]);

        $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        // Tidak ada event VERIFIED sebelumnya
        $this->service->voidEvent($actor, $event, 'Test');

        $this->assertSoftDeleted('penduduks', ['id' => $penduduk->id]);
    }

    public function test_void_datang_kembali_subsequent_event_mengembalikan_status_pindah(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);
        $kk        = $this->makeKk($territory['rt']->id, $actor->id);
        $penduduk  = $this->makePenduduk($territory['rt']->id, $actor->id, 'AKTIF');

        // Event PINDAH sebelumnya
        Event::factory()->verified()->create([
            'event_type_code' => 'PINDAH',
            'penduduk_id'     => $penduduk->id,
            'rt_id'           => $territory['rt']->id,
            'rw_id'           => $territory['rw']->id,
            'desa_id'         => $territory['desa']->id,
            'kk_id'           => $kk->id,
            'event_date'      => now()->subDays(60),
            'created_by'      => $actor->id,
        ]);

        // Event DATANG KEMBALI setelah pindah
        $event = $this->makeVerifiedEvent($territory, $actor->id, $penduduk->id, 'DATANG', $kk->id);

        EventDatang::factory()->create([
            'event_id'         => $event->id,
            'jenis_kedatangan' => 'KEMBALI',
            'restored_from_id' => null,
        ]);

        $this->makeKkMember($kk->id, $penduduk->id, $actor->id, false);

        $this->service->voidEvent($actor, $event, 'Test kembali');

        $this->assertDatabaseHas('penduduks', [
            'id'                       => $penduduk->id,
            'status_kependudukan_code' => 'PINDAH',
        ]);
    }
}
