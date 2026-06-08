<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Services\{SuratTerbitService, SequenceGeneratorService, PdfGeneratorService};
use App\Jobs\GenerateSuratPdfJob;
use App\Models\{SuratTerbit, User, Penduduk, JenisSurat, Desa, Rw, Rt};
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Queue, Storage, Auth};
use Spatie\Permission\Models\Role;

/**
 * 🔄 INTEGRATION TEST: End-to-end surat management flow validation
 * 
 * Tests complete workflow from surat creation through PDF generation,
 * ensuring all services integrate properly and maintain data consistency.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class SuratManagementIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private SuratTerbitService $suratService;
    private SequenceGeneratorService $sequenceService;
    private PdfGeneratorService $pdfService;

    private User $adminDesa;
    private Desa $desa;
    private Rt $rt;
    private Penduduk $penduduk;
    private JenisSurat $jenisSurat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suratService = app(SuratTerbitService::class);
        $this->sequenceService = app(SequenceGeneratorService::class);
        $this->pdfService = app(PdfGeneratorService::class);

        $this->setupTestEnvironment();
        Storage::fake('public');
    }

    /**
     * 🔄 INTEGRATION: Complete surat creation to PDF generation flow
     */
    public function test_complete_surat_lifecycle(): void
    {
        Queue::fake();

        $this->actingAs($this->adminDesa);

        // Step 1: Create surat with automatic sequence generation
        $suratData = [
            'jenis_surat_kode' => $this->jenisSurat->kode,
            'penduduk_id' => $this->penduduk->id,
            'keperluan' => 'Integration test surat creation',
            'tanggal_terbit' => now()->toDateString(),
            'keterangan_tambahan' => 'Full lifecycle test',
        ];

        $surat = $this->suratService->createSurat($suratData);

        // Verify surat creation
        $this->assertInstanceOf(SuratTerbit::class, $surat);
        $this->assertEquals('aktif', $surat->status);
        $this->assertEquals('pending', $surat->pdf_status);
        $this->assertNotNull($surat->nomor_surat);
        $this->assertEquals(1, $surat->sequence_number);

        // Verify sequence generation format
        $expectedFormat = '001/' . $this->jenisSurat->kode . '/' . $this->desa->kode . '/02/2026';
        $this->assertEquals($expectedFormat, $surat->nomor_surat);

        // Step 2: Test surat retrieval with territory filtering
        $retrievedSurat = $this->suratService->getSuratById($surat->id);
        $this->assertEquals($surat->id, $retrievedSurat->id);
        $this->assertEquals($suratData['keperluan'], $retrievedSurat->keperluan);

        // Step 3: Test surat update
        $updateData = [
            'keperluan' => 'Updated integration test purpose',
            'keterangan_tambahan' => 'Updated during integration test',
        ];

        $updatedSurat = $this->suratService->updateSurat($surat->id, $updateData);
        $this->assertEquals($updateData['keperluan'], $updatedSurat->keperluan);
        $this->assertEquals('pending', $updatedSurat->pdf_status); // Should reset to pending

        // Step 4: Test PDF generation dispatch
        GenerateSuratPdfJob::dispatch($updatedSurat);

        Queue::assertPushed(GenerateSuratPdfJob::class, function ($job) use ($updatedSurat) {
            return $job->suratId === $updatedSurat->id;
        });

        // Step 5: Test paginated listing includes created surat
        $paginatedResults = $this->suratService->getPaginatedSuratList();
        $this->assertTrue($paginatedResults->contains('id', $surat->id));
    }

    /**
     * 🔄 INTEGRATION: Sequence generation across multiple surat types
     */
    public function test_sequence_generation_integration(): void
    {
        $this->actingAs($this->adminDesa);

        // Create second jenis surat
        $jenisSurat2 = JenisSurat::factory()->create([
            'kode' => 'DOMISILI',
            'nama' => 'Surat Domisili',
        ]);

        // Create multiple surat of different types
        $suratKK = $this->suratService->createSurat([
            'jenis_surat_kode' => $this->jenisSurat->kode, // KK
            'penduduk_id' => $this->penduduk->id,
            'keperluan' => 'Kartu Keluarga baru',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $suratDomisili = $this->suratService->createSurat([
            'jenis_surat_kode' => $jenisSurat2->kode, // DOMISILI
            'penduduk_id' => $this->penduduk->id,
            'keperluan' => 'Surat keterangan domisili',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        $suratKK2 = $this->suratService->createSurat([
            'jenis_surat_kode' => $this->jenisSurat->kode, // KK again
            'penduduk_id' => $this->penduduk->id,
            'keperluan' => 'Kartu Keluarga kedua',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        // Verify sequence isolation between jenis surat
        $this->assertEquals(1, $suratKK->sequence_number);
        $this->assertEquals(1, $suratDomisili->sequence_number); // Independent sequence
        $this->assertEquals(2, $suratKK2->sequence_number); // Continues KK sequence

        // Verify formatted numbers
        $this->assertStringContains('001/KK/', $suratKK->nomor_surat);
        $this->assertStringContains('001/DOMISILI/', $suratDomisili->nomor_surat);
        $this->assertStringContains('002/KK/', $suratKK2->nomor_surat);
    }

    /**
     * 🔄 INTEGRATION: PDF generation workflow validation
     */
    public function test_pdf_generation_workflow(): void
    {
        $this->actingAs($this->adminDesa);

        // Create surat
        $surat = $this->suratService->createSurat([
            'jenis_surat_kode' => $this->jenisSurat->kode,
            'penduduk_id' => $this->penduduk->id,
            'keperluan' => 'PDF generation test',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        // Test PDF generation (simulate job execution)
        $this->assertEquals('pending', $surat->fresh()->pdf_status);

        // Simulate processing status update
        $surat->update(['pdf_status' => 'processing']);
        $this->assertEquals('processing', $surat->fresh()->pdf_status);

        // Generate PDF using service
        $pdfPath = $this->pdfService->generateAndSavePdf($surat);

        // Verify PDF was created
        Storage::disk('public')->assertExists($pdfPath);

        // Simulate successful generation
        $surat->update([
            'pdf_status' => 'generated',
            'pdf_path' => $pdfPath,
            'pdf_generated_at' => now(),
        ]);

        $updatedSurat = $surat->fresh();
        $this->assertEquals('generated', $updatedSurat->pdf_status);
        $this->assertEquals($pdfPath, $updatedSurat->pdf_path);
        $this->assertNotNull($updatedSurat->pdf_generated_at);

        // Test PDF retrieval
        $pdfContent = $this->pdfService->getPdfContent($pdfPath);
        $this->assertNotEmpty($pdfContent);

        // Test PDF download URL generation (private disk uses auth-guarded route)
        $downloadUrl = $this->pdfService->getPdfDownloadUrl($updatedSurat);
        $this->assertStringContainsString('/download', $downloadUrl);
        $this->assertStringContainsString((string) $updatedSurat->id, $downloadUrl);
    }

    /**
     * 🔄 INTEGRATION: Territory filtering across all operations
     */
    public function test_territory_filtering_integration(): void
    {
        // Create second desa and admin
        $desa2 = Desa::factory()->create(['kode' => 'DESA2']);
        $rw2 = Rw::factory()->create(['desa_id' => $desa2->id]);
        $rt2 = Rt::factory()->create(['rw_id' => $rw2->id]);
        $penduduk2 = Penduduk::factory()->create(['rt_id' => $rt2->id]);

        $adminDesa2 = User::factory()->create(['desa_id' => $desa2->id]);
        $adminDesa2->assignRole('admin_desa');

        // Admin Desa 1 creates surat in their territory
        $this->actingAs($this->adminDesa);
        $suratDesa1 = $this->suratService->createSurat([
            'jenis_surat_kode' => $this->jenisSurat->kode,
            'penduduk_id' => $this->penduduk->id,
            'keperluan' => 'Surat Desa 1',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        // Admin Desa 2 creates surat in their territory
        $this->actingAs($adminDesa2);
        $suratDesa2 = $this->suratService->createSurat([
            'jenis_surat_kode' => $this->jenisSurat->kode,
            'penduduk_id' => $penduduk2->id,
            'keperluan' => 'Surat Desa 2',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        // Test territory isolation in listing
        $this->actingAs($this->adminDesa);
        $desa1Results = $this->suratService->getPaginatedSuratList();
        $desa1Ids = $desa1Results->pluck('id')->toArray();

        $this->assertContains($suratDesa1->id, $desa1Ids);
        $this->assertNotContains($suratDesa2->id, $desa1Ids);

        $this->actingAs($adminDesa2);
        $desa2Results = $this->suratService->getPaginatedSuratList();
        $desa2Ids = $desa2Results->pluck('id')->toArray();

        $this->assertNotContains($suratDesa1->id, $desa2Ids);
        $this->assertContains($suratDesa2->id, $desa2Ids);
    }

    /**
     * 🔄 INTEGRATION: Error handling and data consistency
     */
    public function test_error_handling_data_consistency(): void
    {
        $this->actingAs($this->adminDesa);

        // Test invalid jenis surat
        $this->expectException(\Exception::class);
        $this->suratService->createSurat([
            'jenis_surat_kode' => 'INVALID',
            'penduduk_id' => $this->penduduk->id,
            'keperluan' => 'Should fail',
            'tanggal_terbit' => now()->toDateString(),
        ]);

        // Verify no partial data was created
        $this->assertDatabaseCount('surat_terbit', 0);
        $this->assertDatabaseCount('surat_sequences', 0);
    }

    /**
     * 🔄 INTEGRATION: Performance under load simulation
     */
    public function test_performance_integration(): void
    {
        $this->actingAs($this->adminDesa);

        $startTime = microtime(true);
        $numberOfSurat = 10;

        // Create multiple surat rapidly
        for ($i = 1; $i <= $numberOfSurat; $i++) {
            $this->suratService->createSurat([
                'jenis_surat_kode' => $this->jenisSurat->kode,
                'penduduk_id' => $this->penduduk->id,
                'keperluan' => "Performance test surat #$i",
                'tanggal_terbit' => now()->toDateString(),
            ]);
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Verify all surat were created
        $this->assertDatabaseCount('surat_terbit', $numberOfSurat);

        // Verify sequences are sequential
        $sequences = SuratTerbit::orderBy('sequence_number')->pluck('sequence_number')->toArray();
        $expectedSequences = range(1, $numberOfSurat);
        $this->assertEquals($expectedSequences, $sequences);

        // Performance assertion (should complete within reasonable time)
        $this->assertLessThan(5.0, $executionTime, 'Bulk creation should complete within 5 seconds');
    }

    /**
     * Setup test environment with users, territory, and master data
     */
    private function setupTestEnvironment(): void
    {
        // Create roles
        $roles = ['admin_desa', 'admin_rw', 'admin_rt', 'viewer', 'super_admin'];
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
            }
        }

        // Create territory structure
        $this->desa = Desa::factory()->create(['kode' => 'DESA1']);
        $rw = Rw::factory()->create(['desa_id' => $this->desa->id]);
        $this->rt = Rt::factory()->create(['rw_id' => $rw->id]);

        // Create admin user
        $this->adminDesa = User::factory()->create([
            'name' => 'Admin Desa Test',
            'desa_id' => $this->desa->id,
        ]);
        $this->adminDesa->assignRole('admin_desa');

        // Create jenis surat
        $this->jenisSurat = JenisSurat::factory()->create([
            'kode' => 'KK',
            'nama' => 'Kartu Keluarga',
            'masa_berlaku_hari' => 0, // No expiry
        ]);

        // Create penduduk
        $this->penduduk = Penduduk::factory()->create([
            'rt_id' => $this->rt->id,
            'nama' => 'Penduduk Test',
        ]);
    }
}
