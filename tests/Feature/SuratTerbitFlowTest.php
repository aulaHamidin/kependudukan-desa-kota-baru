<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateSuratPdfJob;
use App\Models\JenisSurat;
use App\Models\KartuKeluarga;
use App\Models\KkMember;
use App\Models\Penduduk;
use App\Models\SuratTerbit;
use App\Models\User;
use App\Services\PdfGeneratorService;
use App\Services\SequenceGeneratorService;
use Carbon\Carbon;
use Database\Seeders\JenisSuratSeeder;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class SuratTerbitFlowTest extends TestCase
{
    use RefreshDatabase;
    use PolicyTestHelper;

    private array $territory;
    private array $otherTerritory;
    private User $adminDesa;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SeedMasterData::class);
        $this->seed(JenisSuratSeeder::class);

        $this->territory = $this->createTerritory();
        $this->otherTerritory = $this->createOtherTerritory();
        $this->adminDesa = $this->adminDesa($this->territory['desa']);
        $this->superAdmin = $this->superAdmin();
    }

    public function test_admin_desa_can_create_surat_with_dynamic_data_custom_expiry_and_queue(): void
    {
        Queue::fake();

        $penduduk = $this->createPendudukWithActiveKk($this->territory);

        $response = $this->actingAs($this->adminDesa)->post(route('surat.terbit.store'), $this->validPayload($penduduk, [
            'jenis_surat_kode' => 'SBALASAN',
            'masa_berlaku_khusus' => 45,
            'kepada' => 'Dinas Pendidikan',
            'alamat_tujuan' => 'Jl. Merdeka No. 1',
            'perihal' => 'Balasan Permohonan Data',
            'lampiran' => '1 berkas',
            'nomor_rujukan' => '421/001/DP',
            'keterangan_tambahan' => 'Data yang dimohon dapat diambil pada jam pelayanan kantor desa.',
        ]));

        $surat = SuratTerbit::firstOrFail();

        $response->assertRedirect(route('surat.terbit.show', $surat));
        $this->assertSame('AKTIF', $surat->status);
        $this->assertSame(45, $surat->masa_berlaku_hari);
        $this->assertSame(today()->addDays(45)->toDateString(), $surat->tanggal_kadaluarsa->toDateString());
        $this->assertSame('Dinas Pendidikan', $surat->data_surat['kepada']);
        $this->assertSame('Balasan Permohonan Data', $surat->data_surat['perihal']);
        $this->assertSame('Data yang dimohon dapat diambil pada jam pelayanan kantor desa.', $surat->keterangan_tambahan);

        Queue::assertPushed(GenerateSuratPdfJob::class);
    }

    public function test_create_surat_uses_unique_word_numbers_across_different_types(): void
    {
        Queue::fake();
        config(['app.desa.kode_surat' => '01.2009']);

        $firstPenduduk = $this->createPendudukWithActiveKk($this->territory);
        $secondPenduduk = $this->createPendudukWithActiveKk($this->territory);

        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.store'), $this->validPayload($firstPenduduk, [
                'jenis_surat_kode' => 'SKD',
                'tanggal_terbit' => '2026-06-12',
            ]))
            ->assertRedirect();

        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.store'), $this->validPayload($secondPenduduk, [
                'jenis_surat_kode' => 'SKBB',
                'tanggal_terbit' => '2026-06-12',
            ]))
            ->assertRedirect();

        $this->assertSame(
            [
                '145 / 001 / 01.2009 / 2026',
                '145 / 002 / 01.2009 / 2026',
            ],
            SuratTerbit::withoutGlobalScopes()
                ->orderBy('id')
                ->pluck('nomor_surat')
                ->all()
        );
        Queue::assertPushed(GenerateSuratPdfJob::class, 2);
    }

    public function test_word_number_sequence_bootstraps_from_existing_surat_numbers(): void
    {
        config(['app.desa.kode_surat' => '01.2009']);

        $this->createSuratRecord($this->territory, [
            'nomor_surat' => '145 / 007 / 01.2009 / 2026',
            'tanggal_terbit' => '2026-06-12',
        ]);

        $sequence = app(SequenceGeneratorService::class)
            ->generateSuratNumber('SKD', '3201012001', 2026, 6);

        $this->assertSame(8, $sequence['sequence']);
        $this->assertSame('145 / 008 / 01.2009 / 2026', $sequence['formatted']);
    }

    public function test_create_form_old_dynamic_data_does_not_break_alpine_x_data_attribute(): void
    {
        $this->actingAs($this->adminDesa)
            ->withSession([
                '_old_input' => [
                    'jenis_surat_kode' => 'SKU',
                    'data_surat' => [
                        'nama_usaha' => 'Toko "Maju" <script>alert(1)</script>',
                    ],
                ],
            ])
            ->get(route('surat.terbit.create'))
            ->assertOk()
            ->assertSee('x-data="suratCreateForm()"', false)
            ->assertSee('window.suratCreateFormDefaults', false)
            ->assertDontSee('oldDataSurat: {', false);
    }

    public function test_create_surat_rejects_invalid_population_rules(): void
    {
        Queue::fake();

        $outsidePenduduk = $this->createPendudukWithActiveKk($this->otherTerritory);
        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.store'), $this->validPayload($outsidePenduduk))
            ->assertSessionHasErrors('penduduk_id');

        $inactivePenduduk = $this->createPendudukWithActiveKk($this->territory, 'PINDAH');
        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.store'), $this->validPayload($inactivePenduduk))
            ->assertSessionHasErrors('penduduk_id');

        $withoutActiveKk = $this->createPendudukWithInactiveKk($this->territory);
        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.store'), $this->validPayload($withoutActiveKk))
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('surat_terbit', 0);
        Queue::assertNothingPushed();
    }

    public function test_sbalasan_requires_body_content_that_will_be_printed(): void
    {
        Queue::fake();

        $penduduk = $this->createPendudukWithActiveKk($this->territory);

        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.store'), $this->validPayload($penduduk, [
                'jenis_surat_kode' => 'SBALASAN',
                'kepada' => 'Dinas Pendidikan',
                'alamat_tujuan' => 'Jl. Merdeka No. 1',
                'perihal' => 'Balasan Permohonan Data',
            ]))
            ->assertSessionHasErrors('keterangan_tambahan');

        $this->assertDatabaseCount('surat_terbit', 0);
        Queue::assertNothingPushed();
    }

    public function test_surat_module_access_matrix_and_immutable_routes(): void
    {
        Storage::fake('surat');
        Storage::disk('surat')->put('surat/test.pdf', 'PDF');

        $surat = $this->createSuratRecord($this->territory, [
            'file_path' => 'surat/test.pdf',
            'pdf_status' => 'READY',
        ]);

        $this->actingAs($this->adminDesa)->get(route('surat.terbit.index'))->assertOk();
        $this->actingAs($this->adminDesa)->get(route('surat.terbit.create'))->assertOk();
        $this->actingAs($this->adminDesa)->get(route('surat.terbit.show', $surat))->assertOk();

        $this->actingAs($this->superAdmin)->get(route('surat.terbit.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('surat.terbit.show', $surat))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('surat.terbit.download', $surat))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('surat.terbit.create'))->assertForbidden();

        $this->actingAs($this->adminRw($this->territory['rw']))->get(route('surat.terbit.index'))->assertForbidden();
        $this->actingAs($this->adminRt($this->territory['rt']))->get(route('surat.terbit.index'))->assertForbidden();
        $this->actingAs($this->viewer($this->territory['rt']))->get(route('surat.terbit.index'))->assertForbidden();

        $this->assertFalse(Route::has('surat.terbit.edit'));
        $this->assertFalse(Route::has('surat.terbit.update'));
        $this->assertFalse(Route::has('surat.terbit.destroy'));
        $this->assertFalse($this->adminDesa->can('update', $surat));
        $this->assertFalse($this->adminDesa->can('delete', $surat));
        $this->assertFalse($this->adminDesa->can('forceDelete', $surat));
    }

    public function test_jenis_surat_detail_endpoint_returns_web_auth_dynamic_contract(): void
    {
        $this->actingAs($this->adminDesa)
            ->getJson(route('surat.jenis-surat.details', 'SBALASAN'))
            ->assertOk()
            ->assertJsonPath('kode', 'SBALASAN')
            ->assertJsonPath('template_category', 'internal')
            ->assertJsonPath('is_ready', true)
            ->assertJsonFragment(['name' => 'perihal']);

        $this->actingAs($this->adminDesa)
            ->getJson(route('surat.jenis-surat.details', 'SKN'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'nama_calon_pasangan',
                'label' => 'Nama Calon Pasangan',
                'required' => true,
            ]);

        $this->actingAs($this->adminDesa)
            ->getJson(route('surat.jenis-surat.details', 'SKU'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'nama_usaha',
                'label' => 'Nama Usaha',
                'required' => true,
            ])
            ->assertJsonFragment([
                'name' => 'ukuran_tempat_usaha',
                'label' => 'Ukuran Tempat Usaha',
                'required' => true,
            ]);

        $this->actingAs($this->adminDesa)
            ->getJson(route('surat.jenis-surat.details', 'SKD'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'alamat_domisili',
                'label' => 'Alamat Domisili',
                'required' => false,
            ]);

        $this->actingAs($this->adminDesa)
            ->getJson(route('surat.jenis-surat.details', 'SKTM'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'nama_anak',
                'label' => 'Nama Anak',
                'required' => true,
            ]);

        $this->actingAs($this->adminDesa)
            ->getJson(route('surat.jenis-surat.details', 'SBALASAN'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'nomor_surat_masuk',
                'label' => 'Nomor Surat Masuk',
                'required' => false,
            ]);

        $this->actingAs($this->adminDesa)
            ->getJson(route('surat.jenis-surat.details', 'SIK'))
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'jenis_kegiatan',
                'label' => 'Jenis Kegiatan',
                'required' => true,
            ]);
    }

    public function test_pdf_template_data_merges_dynamic_fields_without_overwriting_resident_data(): void
    {
        $surat = $this->createSuratRecord($this->territory, [
            'jenis_surat_kode' => 'SBALASAN',
            'keterangan_tambahan' => "Data yang dimohon tersedia di kantor desa.\nSilakan melakukan konfirmasi kepada petugas pelayanan.",
            'data_surat' => [
                'nama_lengkap' => 'Nama Palsu',
                'kepada' => 'Dinas Pendidikan',
                'alamat_tujuan' => 'Jl. Pendidikan No. 1',
                'perihal' => 'Balasan Permohonan Data',
                'nomor_rujukan' => '421/001/DP',
            ],
        ]);

        $service = app(PdfGeneratorService::class);
        $method = (new ReflectionClass($service))->getMethod('prepareTemplateData');
        $method->setAccessible(true);

        $data = $method->invoke($service, $surat);
        $rendered = $surat->jenisSurat->renderTemplate($data)->render();

        $this->assertSame('Balasan Permohonan Data', $data['perihal']);
        $this->assertSame($surat->penduduk->nama_lengkap, $data['nama_lengkap']);
        $this->assertNotSame('Nama Palsu', $data['nama_lengkap']);
        $this->assertStringContainsString('Balasan Permohonan Data', $rendered);
        $this->assertStringContainsString('Dinas Pendidikan', $rendered);
        $this->assertStringContainsString('Menjawab surat Saudara Nomor', $rendered);
        $this->assertStringContainsString('Data yang dimohon tersedia di kantor desa.', $rendered);
        $this->assertStringContainsString('Silakan melakukan konfirmasi kepada petugas pelayanan.', $rendered);
        $this->assertStringNotContainsString('Perihal/Subject', $rendered);
    }

    public function test_skn_pdf_template_uses_specific_content_and_clean_indonesian_layout(): void
    {
        config([
            'app.locale' => 'id',
            'app.desa.alamat' => 'Jl. Lintas Sumatera No. 10',
            'app.desa.kode_pos' => '32181',
        ]);
        Carbon::setLocale('id');

        $surat = $this->createSuratRecord($this->territory, [
            'jenis_surat_kode' => 'SKN',
            'tanggal_terbit' => '2026-06-09',
            'keperluan' => 'untuk keperluan persyaratan administrasi nikah',
            'data_surat' => [
                'nama_calon_pasangan' => 'Siti Aminah',
                'nik_calon_pasangan' => '3276010101010001',
                'alamat_calon_pasangan' => 'RT 002/RW 001',
            ],
        ]);

        $service = app(PdfGeneratorService::class);
        $method = (new ReflectionClass($service))->getMethod('prepareTemplateData');
        $method->setAccessible(true);

        $data = $method->invoke($service, $surat);
        $rendered = $surat->jenisSurat->renderTemplate($data)->render();

        $this->assertStringContainsString('SURAT KETERANGAN NIKAH', strtoupper($rendered));
        $this->assertStringContainsString('Siti Aminah', $rendered);
        $this->assertStringContainsString('09 Juni 2026', $rendered);
        $this->assertStringContainsString('Jl. Lintas Sumatera No. 10', $rendered);
        $this->assertStringNotContainsString('KECAMATAN KECAMATAN', strtoupper($rendered));
        $this->assertStringNotContainsString('Alamat Desa', $rendered);
        $this->assertStringNotContainsString('untuk keperluan untuk keperluan', strtolower($rendered));
    }

    public function test_skd_pdf_template_matches_word_style_kop_signer_and_domisili(): void
    {
        config([
            'app.locale' => 'id',
            'app.desa.email' => 'kotabaru1608012009@gmail.com',
            'app.desa.alamat' => 'Jalan Pertanian No.958 Desa Kota Baru Kecamatan Martapura Kabupaten OKU Timur',
            'app.desa.kode_pos' => '32311',
            'app.desa.kepala_desa.nik' => '1608010101010001',
            'app.desa.kepala_desa.alamat' => 'Desa Kota Baru Kecamatan Martapura',
            'app.desa.ttd_digital.enabled' => true,
            'app.desa.ttd_digital.kepala_desa_path' => 'images/logo-desa.png',
            'app.desa.ttd_digital.stempel_path' => null,
        ]);
        Carbon::setLocale('id');
        $this->territory['desa']->update([
            'nama' => 'Kota Baru',
            'kecamatan' => 'Kecamatan Martapura',
            'kabupaten' => 'Kabupaten Ogan Komering Ulu Timur',
            'provinsi' => 'Provinsi Sumatera Selatan',
        ]);
        $this->territory['rw']->update(['nomor_rw' => '001']);
        $this->territory['rt']->update(['nomor_rt' => '001']);

        $surat = $this->createSuratRecord($this->territory, [
            'jenis_surat_kode' => 'SKD',
            'tanggal_terbit' => '2026-06-10',
            'data_surat' => [],
        ]);
        $surat->penduduk->update([
            'nama_lengkap' => 'Hamidin',
            'nik' => '1608012009000001',
            'tempat_lahir' => 'Martapura',
            'tgl_lahir' => '1990-05-12',
            'nama_ayah' => 'Abdullah',
        ]);
        $surat->kartuKeluarga->update([
            'alamat' => 'Jalan Pertanian RT.001 Dusun III Desa Kotabaru',
        ]);

        $service = app(PdfGeneratorService::class);
        $method = (new ReflectionClass($service))->getMethod('prepareTemplateData');
        $method->setAccessible(true);

        $data = $method->invoke($service, $surat->refresh());
        $rendered = $surat->jenisSurat->renderTemplate($data)->render();
        $normalizedRendered = $this->normalizeWhitespace($rendered);

        $this->assertStringContainsString('logo-desa.png', $rendered);
        $this->assertStringContainsString('logo-atas.png', $rendered);
        $this->assertStringContainsString('logo-bawah.png', $rendered);
        $this->assertStringNotContainsString('logo-kabupaten.png', $rendered);
        $this->assertStringContainsString('e-mail : kotabaru1608012009@gmail.com', $rendered);
        $this->assertStringContainsString('KP.32311', $rendered);
        $this->assertStringContainsString('Yang bertanda tangan di bawah ini', $rendered);
        $this->assertStringContainsString('Alamat Domisili', $rendered);
        $this->assertStringContainsString('Jalan Pertanian RT.001 Dusun III Desa Kotabaru', $rendered);
        $this->assertStringContainsString('10 Juni 2026', $rendered);
        $this->assertStringNotContainsString('Tanda Tangan Digital', $rendered);
        $this->assertStringContainsString('ruang-ttd', $rendered);
        $this->assertStringNotContainsString('ttd-digital', $rendered);
        $this->assertStringNotContainsString('KECAMATAN KECAMATAN', strtoupper($rendered));
        $this->assertStringNotContainsString('KABUPATEN KABUPATEN', strtoupper($rendered));
        $this->assertStringContainsString(
            'Nama tersebut di atas adalah benar warga Desa Kota Baru, Kecamatan Martapura, Kabupaten Ogan Komering Ulu Timur, dan saat ini',
            $normalizedRendered
        );
        $this->assertStringContainsString(
            'bertempat tinggal/berdomisili di Jalan Pertanian RT.001 Dusun III Desa Kotabaru, RT 001/RW 001, Desa Kota Baru, Kecamatan Martapura, Kabupaten Ogan Komering Ulu Timur, Provinsi Sumatera Selatan.',
            $normalizedRendered
        );
        $this->assertStringNotContainsString('Provinsi Sumatera Selatan..', $normalizedRendered);
    }

    public function test_sktm_pdf_template_prints_parent_and_child_school_assistance_data(): void
    {
        $surat = $this->createSuratRecord($this->territory, [
            'jenis_surat_kode' => 'SKTM',
            'tanggal_terbit' => '2026-06-10',
            'data_surat' => [
                'nama_anak' => 'Aulia Rahma',
                'bin_binti_anak' => 'Hamidin',
                'tempat_lahir_anak' => 'Martapura',
                'tanggal_lahir_anak' => '2012-04-15',
                'nik_anak' => '1608011504120002',
                'no_kk_anak' => '1608010101010002',
                'kewarganegaraan_anak' => 'WNI',
                'agama_anak' => 'Islam',
                'jenis_kelamin_anak' => 'P',
                'pekerjaan_anak' => 'Pelajar',
                'alamat_anak' => 'Jalan Pertanian RT.001 Dusun III Desa Kotabaru',
                'alamat_domisili_anak' => 'Jalan Pertanian RT.001 Dusun III Desa Kotabaru',
                'keperluan_program' => 'KELENGKAPAN ADMINISTRASI PENERIMA BANTUAN PROGRAM INDONESIA PINTAR (PIP)',
            ],
        ]);
        $surat->penduduk->update([
            'nama_lengkap' => 'Hamidin',
            'nama_ayah' => 'Abdullah',
        ]);
        $surat->kartuKeluarga->update([
            'alamat' => 'Jalan Pertanian RT.001 Dusun III Desa Kotabaru',
        ]);

        $service = app(PdfGeneratorService::class);
        $method = (new ReflectionClass($service))->getMethod('prepareTemplateData');
        $method->setAccessible(true);

        $data = $method->invoke($service, $surat->refresh());
        $rendered = $surat->jenisSurat->renderTemplate($data)->render();

        $this->assertStringContainsString('SURAT KETERANGAN TIDAK MAMPU', strtoupper($rendered));
        $this->assertStringContainsString('Orang Tua/Wali', $rendered);
        $this->assertStringContainsString('Aulia Rahma', $rendered);
        $this->assertStringContainsString('15 April 2012', $rendered);
        $this->assertStringContainsString('PROGRAM INDONESIA PINTAR (PIP)', $rendered);
        $this->assertStringNotContainsString('Adalah benar warga Desa kami yang tergolong', $rendered);
    }

    public function test_sku_pdf_template_prints_business_detail_and_ho_note(): void
    {
        $surat = $this->createSuratRecord($this->territory, [
            'jenis_surat_kode' => 'SKU',
            'tanggal_terbit' => '2026-06-10',
            'data_surat' => [
                'alamat_domisili' => 'Jalan Pertanian RT.001 Dusun III Desa Kotabaru',
                'nama_usaha' => 'Counter Handphone dan Jual Pulsa',
                'jenis_usaha' => 'Perdagangan',
                'alamat_usaha' => 'Jalan Pertanian Desa Kotabaru',
                'ukuran_tempat_usaha' => '4 M x 9 M',
                'jumlah_tenaga_pembantu' => '2 (Dua) Orang',
            ],
        ]);
        $surat->penduduk->update([
            'nama_lengkap' => 'Hamidin',
            'status_perkawinan' => 'Kawin',
        ]);
        $surat->kartuKeluarga->update([
            'alamat' => 'Jalan Pertanian RT.001 Dusun III Desa Kotabaru',
        ]);

        $service = app(PdfGeneratorService::class);
        $method = (new ReflectionClass($service))->getMethod('prepareTemplateData');
        $method->setAccessible(true);

        $data = $method->invoke($service, $surat->refresh());
        $rendered = $surat->jenisSurat->renderTemplate($data)->render();

        $this->assertStringContainsString('SURAT KETERANGAN USAHA', strtoupper($rendered));
        $this->assertStringContainsString('COUNTER HANDPHONE DAN JUAL PULSA', $rendered);
        $this->assertStringContainsString('Jalan Pertanian Desa Kotabaru', $rendered);
        $this->assertStringContainsString('4 M x 9 M', $rendered);
        $this->assertStringContainsString('2 (Dua) Orang', $rendered);
        $this->assertStringContainsString('bukan Surat Izin Usaha (HO)', $rendered);
    }

    public function test_compact_pdf_layout_css_and_sku_normal_content_stays_one_page(): void
    {
        config([
            'app.locale' => 'id',
            'app.desa.email' => 'desa@example.com',
            'app.desa.alamat' => 'Jl. Desa No. 1',
            'app.desa.kode_pos' => '32181',
        ]);

        $surat = $this->createSuratRecord($this->territory, [
            'jenis_surat_kode' => 'SKU',
            'nomor_surat' => '145 / 002 / 01.2009 / 2026',
            'tanggal_terbit' => '2026-06-12',
            'data_surat' => [
                'alamat_domisili' => 'Martapura',
                'nama_usaha' => 'Aceng Gile',
                'jenis_usaha' => 'Acengan',
                'alamat_usaha' => 'Martapura',
                'ukuran_tempat_usaha' => '10x10',
                'jumlah_tenaga_pembantu' => '2',
            ],
        ]);
        $surat->penduduk->update([
            'nama_lengkap' => 'Halim Ardianto',
            'nik' => '4850230412587320',
            'tempat_lahir' => 'Kotamobagu',
            'tgl_lahir' => '2010-06-20',
            'status_perkawinan' => 'Belum Kawin',
        ]);
        $surat->kartuKeluarga->update([
            'alamat' => 'Psr. Baan No. 831',
        ]);

        $service = app(PdfGeneratorService::class);
        $templateDataMethod = (new ReflectionClass($service))->getMethod('prepareTemplateData');
        $templateDataMethod->setAccessible(true);
        $data = $templateDataMethod->invoke($service, $surat->refresh());
        $rendered = $surat->jenisSurat->renderTemplate($data)->render();

        $this->assertStringContainsString('margin: 1.15cm 1.25cm 1.15cm 1.25cm;', $rendered);
        $this->assertStringContainsString('font-size: 11pt;', $rendered);
        $this->assertStringContainsString('line-height: 1.24;', $rendered);
        $this->assertStringContainsString('max-width: 18.9cm;', $rendered);
        $this->assertStringContainsString('padding: 0.15cm 0.2cm;', $rendered);
        $this->assertStringContainsString('height: 62px;', $rendered);
        $this->assertStringContainsString('margin-top: 10px;', $rendered);
        $this->assertStringContainsString('height: 48px;', $rendered);
        $this->assertStringContainsString('surat-container surat-sku', $rendered);

        $generatePdfMethod = (new ReflectionClass($service))->getMethod('generatePdf');
        $generatePdfMethod->setAccessible(true);
        $pdf = $generatePdfMethod->invoke($service, $surat->refresh(), []);
        $pdf->output();

        $this->assertSame(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_admin_desa_can_regenerate_pdf_without_changing_surat_data(): void
    {
        Queue::fake();
        Storage::fake('surat');
        Storage::disk('surat')->put('surat/old.pdf', 'OLD PDF');

        $surat = $this->createSuratRecord($this->territory, [
            'file_path' => 'surat/old.pdf',
            'pdf_status' => 'READY',
            'pdf_generated_at' => now(),
            'data_surat' => [
                'nama_calon_pasangan' => 'Siti Aminah',
                'nik_calon_pasangan' => '3276010101010001',
            ],
        ]);
        $originalDataSurat = $surat->data_surat;

        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.regenerate-pdf', $surat))
            ->assertRedirect(route('surat.terbit.show', $surat));

        $surat->refresh();

        $this->assertSame('PROCESSING', $surat->pdf_status);
        $this->assertNull($surat->file_path);
        $this->assertNull($surat->pdf_generated_at);
        $this->assertSame($originalDataSurat, $surat->data_surat);
        Storage::disk('surat')->assertMissing('surat/old.pdf');
        Queue::assertPushed(GenerateSuratPdfJob::class);
    }

    public function test_regenerate_pdf_button_uses_sweetalert_confirmation(): void
    {
        $surat = $this->createSuratRecord($this->territory, [
            'file_path' => 'surat/current.pdf',
            'pdf_status' => 'READY',
        ]);

        $this->actingAs($this->adminDesa)
            ->get(route('surat.terbit.show', $surat))
            ->assertOk()
            ->assertSee('Buat Ulang PDF')
            ->assertSee('x-data="swalConfirm', false)
            ->assertSee('Buat Ulang PDF?', false)
            ->assertSee('@submit.prevent="submit($event)"', false)
            ->assertDontSee('return confirm(', false);
    }

    public function test_regenerate_pdf_denies_unauthorized_roles_or_invalid_status(): void
    {
        Queue::fake();

        $surat = $this->createSuratRecord($this->territory);
        $otherSurat = $this->createSuratRecord($this->otherTerritory);
        $batalSurat = $this->createSuratRecord($this->territory, ['status' => 'BATAL']);

        $this->actingAs($this->superAdmin)
            ->post(route('surat.terbit.regenerate-pdf', $surat))
            ->assertForbidden();

        $this->actingAs($this->adminRw($this->territory['rw']))
            ->post(route('surat.terbit.regenerate-pdf', $surat))
            ->assertForbidden();

        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.regenerate-pdf', $otherSurat))
            ->assertNotFound();

        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.regenerate-pdf', $batalSurat))
            ->assertForbidden();

        Queue::assertNothingPushed();
    }

    public function test_batalkan_changes_status_and_deletes_private_pdf(): void
    {
        Storage::fake('surat');
        Storage::disk('surat')->put('surat/to-delete.pdf', 'PDF');

        $surat = $this->createSuratRecord($this->territory, [
            'file_path' => 'surat/to-delete.pdf',
            'pdf_status' => 'READY',
        ]);

        $this->actingAs($this->adminDesa)
            ->post(route('surat.terbit.batalkan', $surat), [
                'alasan_batal' => 'Terdapat kesalahan data pada surat yang sudah diterbitkan.',
                'konfirmasi_batal' => '1',
            ])
            ->assertRedirect(route('surat.terbit.show', $surat));

        $surat->refresh();

        $this->assertSame('BATAL', $surat->status);
        $this->assertSame($this->adminDesa->id, $surat->cancelled_by);
        $this->assertNotNull($surat->cancelled_at);
        Storage::disk('surat')->assertMissing('surat/to-delete.pdf');
    }

    private function validPayload(Penduduk $penduduk, array $overrides = []): array
    {
        return array_merge([
            'jenis_surat_kode' => 'SKD',
            'penduduk_id' => $penduduk->id,
            'keperluan' => 'Keperluan administrasi untuk pengujian fitur surat',
            'tanggal_terbit' => today()->toDateString(),
        ], $overrides);
    }

    private function createPendudukWithActiveKk(array $territory, string $status = 'AKTIF'): Penduduk
    {
        return $this->createPendudukWithKkMember($territory, $status, 'AKTIF');
    }

    private function createPendudukWithInactiveKk(array $territory): Penduduk
    {
        return $this->createPendudukWithKkMember($territory, 'AKTIF', 'KELUAR');
    }

    private function createPendudukWithKkMember(array $territory, string $pendudukStatus, string $memberStatus): Penduduk
    {
        $penduduk = Penduduk::factory()->create([
            'rt_id' => $territory['rt']->id,
            'status_kependudukan_code' => $pendudukStatus,
            'created_by' => $this->adminDesa->id,
        ]);

        $kk = KartuKeluarga::factory()->aktif()->create([
            'rt_id' => $territory['rt']->id,
            'created_by' => $this->adminDesa->id,
        ]);

        KkMember::factory()->create([
            'kartu_keluarga_id' => $kk->id,
            'penduduk_id' => $penduduk->id,
            'status' => $memberStatus,
            'created_by' => $this->adminDesa->id,
        ]);

        return $penduduk;
    }

    private function createSuratRecord(array $territory, array $overrides = []): SuratTerbit
    {
        $penduduk = $this->createPendudukWithActiveKk($territory);
        $kkId = $penduduk->kkMembers()->where('status', 'AKTIF')->firstOrFail()->kartu_keluarga_id;

        return SuratTerbit::factory()->create(array_merge([
            'jenis_surat_kode' => 'SKD',
            'penduduk_id' => $penduduk->id,
            'kk_id' => $kkId,
            'rt_id' => $territory['rt']->id,
            'rw_id' => $territory['rw']->id,
            'desa_id' => $territory['desa']->id,
            'created_by' => $this->adminDesa->id,
            'status' => 'AKTIF',
        ], $overrides));
    }

    private function normalizeWhitespace(string $value): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $value));
    }
}
