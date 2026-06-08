<?php

declare(strict_types=1);

namespace Tests\Feature\Import;

use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class PendudukImportFeatureTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SeedMasterData::class);
    }

    // =========================================================================
    // ROUTE PROTECTION
    // =========================================================================

    public function test_admin_desa_bisa_akses_halaman_import(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $response = $this->actingAs($actor)->get(route('penduduk.import.index'));

        $response->assertStatus(200);
    }

    public function test_admin_rt_tidak_bisa_akses_halaman_import(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRt($territory['rt']);

        $response = $this->actingAs($actor)->get(route('penduduk.import.index'));

        $response->assertStatus(403);
    }

    public function test_viewer_tidak_bisa_akses_halaman_import(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->viewer($territory['rt']);

        $response = $this->actingAs($actor)->get(route('penduduk.import.index'));

        $response->assertStatus(403);
    }

    public function test_admin_rw_tidak_bisa_akses_halaman_import(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminRw($territory['rw']);

        $response = $this->actingAs($actor)->get(route('penduduk.import.index'));

        $response->assertStatus(403);
    }

    // =========================================================================
    // GET /penduduk/import
    // =========================================================================

    public function test_get_import_index_menampilkan_halaman_dengan_status_200(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $response = $this->actingAs($actor)->get(route('penduduk.import.index'));

        $response->assertStatus(200);
        $response->assertViewIs('penduduk.import.index');
    }

    // =========================================================================
    // POST /penduduk/import/validate — VALIDATION ERRORS
    // =========================================================================

    public function test_validate_tanpa_file_redirect_dengan_error_validasi(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $response = $this->actingAs($actor)
            ->post(route('penduduk.import.validate'), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file']);
    }

    public function test_validate_format_file_salah_redirect_dengan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($actor)
            ->post(route('penduduk.import.validate'), ['file' => $fakeFile]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['file']);
    }

    // =========================================================================
    // POST /penduduk/import/execute
    // =========================================================================

    public function test_execute_tanpa_session_redirect_ke_index_dengan_error(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $response = $this->actingAs($actor)
            ->post(route('penduduk.import.execute'));

        $response->assertRedirect(route('penduduk.import.index'));
        $response->assertSessionHas('error');
    }

    public function test_execute_dengan_session_valid_import_data_dan_redirect_success(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $rows = [
            [
                'no_kk'              => '1234567890123456',
                'rt_id'              => $territory['rt']->id,
                'hubungan_keluarga'  => 'KEPALA_KELUARGA',
                'nik'                => '1234567890123456',
                'nama_lengkap'       => 'Test Penduduk',
                'jenis_kelamin'      => 'L',
                'tempat_lahir'       => 'Bandung',
                'tgl_lahir'          => '1990-01-01',
                'agama'              => 'ISLAM',
                'status_perkawinan'  => 'BELUM_KAWIN',
                'alamat_asal'        => 'Jl. Asal',
                'alamat_kk'          => 'Jl. KK',
            ],
        ];

        $response = $this->actingAs($actor)
            ->withSession([
                'import_penduduk_rows' => $rows,
                'import_penduduk_hash' => md5(serialize($rows)),
            ])
            ->post(route('penduduk.import.execute'));

        $response->assertRedirect(route('penduduk.import.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('penduduks', ['nik' => '1234567890123456']);
        $this->assertDatabaseHas('kartu_keluargas', ['no_kk' => '1234567890123456']);
    }

    // =========================================================================
    // GET /penduduk/import/template
    // =========================================================================

    public function test_template_download_mengembalikan_file_excel(): void
    {
        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $response = $this->actingAs($actor)->get(route('penduduk.import.template'));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
    }
}
