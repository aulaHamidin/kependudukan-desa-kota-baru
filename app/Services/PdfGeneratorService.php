<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SuratTerbit;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * PDF Generation service using DomPDF
 * 
 * Handles PDF generation for surat documents using Blade templates.
 * Supports multiple output formats (save, download, stream) and
 * custom paper sizes. Integrates with storage system for file management.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class PdfGeneratorService
{
    /**
     * PDF storage disk name
     * 
     * ✅ SECURITY: Using private 'surat' disk instead of 'public'
     * PDF files contain citizen personal data and must NOT be publicly accessible.
     * Download must go through auth-guarded route.
     */
    private const PDF_DISK = 'surat';

    /**
     * PDF storage path relative to disk
     */
    private const PDF_PATH = 'surat';

    /**
     * Default paper size  
     */
    private const DEFAULT_PAPER = 'A4';

    /**
     * Default orientation
     */
    private const DEFAULT_ORIENTATION = 'portrait';

    /**
     * Generate PDF for surat and save to storage
     * 
     * @param SuratTerbit $surat Surat instance
     * @param array $options PDF generation options
     * @return string Relative path to saved PDF file
     * @throws Exception When PDF generation fails
     */
    public function generateAndSavePdf(SuratTerbit $surat, array $options = []): string
    {
        try {
            // Generate PDF content
            $pdf = $this->generatePdf($surat, $options);

            // Create filename
            $filename = $this->generateFilename($surat);
            $fullPath = self::PDF_PATH . '/' . $filename;

            // Ensure directory exists
            $this->ensureDirectoryExists();

            // Save PDF to storage
            Storage::disk(self::PDF_DISK)->put($fullPath, $pdf->output());

            Log::info("PDF generated and saved", [
                'surat_id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat,
                'filename' => $filename,
                'path' => $fullPath
            ]);

            return $fullPath;
        } catch (Exception $e) {
            Log::error("PDF generation failed", [
                'surat_id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat,
                'error' => $e->getMessage()
            ]);

            throw new Exception("Failed to generate PDF: " . $e->getMessage());
        }
    }

    /**
     * Generate PDF and return for direct download
     * 
     * @param SuratTerbit $surat Surat instance
     * @param array $options PDF generation options
     * @return \Illuminate\Http\Response PDF download response
     */
    public function downloadPdf(SuratTerbit $surat, array $options = []): Response
    {
        $pdf = $this->generatePdf($surat, $options);
        $filename = $this->generateFilename($surat);

        return $pdf->download($filename);
    }

    /**
     * Generate PDF and stream to browser
     * 
     * @param SuratTerbit $surat Surat instance  
     * @param array $options PDF generation options
     * @return \Illuminate\Http\Response PDF stream response
     */
    public function streamPdf(SuratTerbit $surat, array $options = []): Response
    {
        $pdf = $this->generatePdf($surat, $options);
        $filename = $this->generateFilename($surat);

        return $pdf->stream($filename);
    }

    /**
     * Get PDF file from storage
     * 
     * @param string $path Relative path to PDF file
     * @return string PDF content
     * @throws Exception When file not found
     */
    public function getPdfContent(string $path): string
    {
        if (!Storage::disk(self::PDF_DISK)->exists($path)) {
            throw new Exception("PDF file not found: {$path}");
        }

        return Storage::disk(self::PDF_DISK)->get($path);
    }

    /**
     * Delete PDF file from storage
     * 
     * @param string $path Relative path to PDF file
     * @return bool Success status
     */
    public function deletePdf(string $path): bool
    {
        if (!Storage::disk(self::PDF_DISK)->exists($path)) {
            return true; // Already deleted
        }

        return Storage::disk(self::PDF_DISK)->delete($path);
    }

    /**
     * Check if PDF file exists in storage
     * 
     * @param string $path Relative path to PDF file
     * @return bool Exists status
     */
    public function pdfExists(string $path): bool
    {
        return Storage::disk(self::PDF_DISK)->exists($path);
    }

    /**
     * Get download URL for PDF file
     * 
     * ✅ SECURITY: Since PDF is on private disk, return auth-guarded route instead of direct URL.
     * Caller must pass surat ID to generate proper route.
     * 
     * @param SuratTerbit $surat Surat instance for route generation
     * @return string Download route URL
     */
    public function getPdfDownloadUrl(SuratTerbit $surat): string
    {
        return route('surat.terbit.download', $surat);
    }

    /**
     * @deprecated Use getPdfDownloadUrl() instead - private disk has no public URL
     * @throws \RuntimeException Always throws since private disk has no public URL
     */
    public function getPdfUrl(string $path): string
    {
        throw new \RuntimeException(
            'PDF disimpan di private disk dan tidak memiliki public URL. ' .
                'Gunakan getPdfDownloadUrl() untuk generate auth-guarded download URL.'
        );
    }

    /**
     * Generate PDF instance with content
     * 
     * Uses hybrid template system via JenisSurat::renderTemplate().
     * Prepares penduduk data and passes to template rendering.
     * 
     * @param SuratTerbit $surat Surat instance
     * @param array $options Generation options
     * @return \Barryvdh\DomPDF\PDF PDF instance
     */
    private function generatePdf(SuratTerbit $surat, array $options = []): DomPDF
    {
        // Load relationships needed for template
        $surat->loadMissing([
            'jenisSurat',
            'penduduk.agama',
            'penduduk.pendidikan',
            'penduduk.pekerjaan',
            'penduduk.rt.rw.desa',
            'kartuKeluarga',
            'desa'
        ]);

        // Prepare data for hybrid template system
        $templateData = $this->prepareTemplateData($surat);

        // Get rendered view via hybrid template (includes whitelist + validation)
        $renderedView = $surat->jenisSurat->renderTemplate($templateData);

        // Generate PDF from rendered view
        $pdf = Pdf::loadHTML($renderedView->render());

        // Configure PDF options
        $this->configurePdf($pdf, $options);

        return $pdf;
    }

    /**
     * Prepare template data from surat and penduduk
     * 
     * Converts Eloquent models to flat array for template rendering.
     * 
     * @param SuratTerbit $surat Surat instance with loaded relationships
     * @return array Template data array
     */
    private function prepareTemplateData(SuratTerbit $surat): array
    {
        $penduduk = $surat->penduduk;
        /** @var \App\Models\Desa|null $desa */
        $desa = $surat->desa;
        if (!$desa && $penduduk->rt && $penduduk->rt->rw) {
            $desa = $penduduk->rt->rw->desa;
        }

        // Build alamat from relationships
        $rtNum = $penduduk->rt?->nomor_rt ?? '-';
        $rwNum = $penduduk->rt?->rw?->nomor_rw ?? '-';
        $alamat = $penduduk->rt
            ? "RT {$rtNum}/RW {$rwNum}"
            : '-';

        return [
            // Surat info
            'suratTerbit'    => $surat,
            'nomor_surat'    => $surat->nomor_surat,
            'tanggal_terbit' => $surat->tanggal_terbit,
            'keperluan'      => $surat->keperluan,
            'tujuan'         => $surat->keperluan, // alias

            // Penduduk data (flat for template access)
            'nama_lengkap'    => $penduduk->nama_lengkap,
            'nik'             => $penduduk->nik,
            'tempat_lahir'    => $penduduk->tempat_lahir,
            'tanggal_lahir'   => $penduduk->tgl_lahir,
            'jenis_kelamin'   => $penduduk->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'agama'           => $penduduk->agama?->nama ?? '-',
            'pekerjaan'       => $penduduk->pekerjaan?->nama ?? '-',
            'pendidikan'      => $penduduk->pendidikan?->nama ?? '-',
            'status_kawin'    => $penduduk->status_perkawinan ?? '-',
            'kewarganegaraan' => $penduduk->kewarganegaraan ?? 'WNI',
            'no_kk'           => $surat->kartuKeluarga?->no_kk ?? '-',

            // Alamat (flat)
            'alamat'    => $alamat,
            'rt'        => $penduduk->rt?->nomor_rt ?? '-',
            'rw'        => $penduduk->rt?->rw?->nomor_rw ?? '-',
            'desa'      => $desa?->nama ?? '-',
            'kecamatan' => $desa?->kecamatan ?? '-',
            'kabupaten' => $desa?->kabupaten ?? '-',
            'provinsi'  => $desa?->provinsi ?? '-',

            // Desa info (for signatures/kop)
            'desa_info' => [
                'nama'      => $desa?->nama ?? config('app.desa.nama'),
                'kecamatan' => $desa?->kecamatan ?? config('app.desa.kecamatan'),
                'kabupaten' => $desa?->kabupaten ?? config('app.desa.kabupaten'),
                'provinsi'  => $desa?->provinsi ?? config('app.desa.provinsi'),
            ],

            // Additional data from surat
            'keterangan_tambahan' => $surat->keterangan_tambahan,
            'data_surat'          => $surat->data_surat ?? [],

            // Pejabat (from config or override)
            'kepala_desa' => config('app.desa.kepala_desa'),
            'sekdes'      => config('app.desa.sekdes'),
            'kasi'        => config('app.desa.kasi'),
        ];
    }

    /**
     * Configure PDF options (paper size, orientation, etc.)
     * 
     * @param \Barryvdh\DomPDF\PDF $pdf PDF instance
     * @param array $options Configuration options
     * @return void
     */
    private function configurePdf($pdf, array $options): void
    {
        // Set paper size and orientation
        $paper = $options['paper'] ?? self::DEFAULT_PAPER;
        $orientation = $options['orientation'] ?? self::DEFAULT_ORIENTATION;
        $pdf->setPaper($paper, $orientation);

        // Set additional DomPDF options
        if (isset($options['dpi'])) {
            $pdf->setOptions(['dpi' => $options['dpi']]);
        }

        if (isset($options['default_font'])) {
            $pdf->setOptions(['default_font' => $options['default_font']]);
        }

        // Enable font subsetting if needed
        if ($options['enable_font_subsetting'] ?? false) {
            $pdf->setOptions(['enable_font_subsetting' => true]);
        }
    }

    /**
     * Generate filename for PDF
     * 
     * @param SuratTerbit $surat Surat instance
     * @return string Generated filename
     */
    private function generateFilename(SuratTerbit $surat): string
    {
        // Clean nomor surat for filename (remove slashes, spaces)
        $cleanNomor = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $surat->nomor_surat);

        // Format: JENISSURAT_NOMORSURAT_YYYYMMDD.pdf
        return sprintf(
            '%s_%s_%s.pdf',
            strtoupper($surat->jenis_surat_kode),
            $cleanNomor,
            Carbon::now()->format('Ymd')
        );
    }

    /**
     * Ensure PDF storage directory exists
     * 
     * @return void
     */
    private function ensureDirectoryExists(): void
    {
        $directory = self::PDF_PATH;

        if (!Storage::disk(self::PDF_DISK)->exists($directory)) {
            Storage::disk(self::PDF_DISK)->makeDirectory($directory);
        }
    }

    /**
     * Get PDF generation statistics
     * 
     * @return array Statistics array
     */
    public function getGenerationStats(): array
    {
        $directory = self::PDF_PATH;
        $files = Storage::disk(self::PDF_DISK)->files($directory);

        $totalFiles = count($files);
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += Storage::disk(self::PDF_DISK)->size($file);
        }

        return [
            'total_files' => $totalFiles,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'storage_path' => $directory,
            'disk' => self::PDF_DISK,
        ];
    }

    /**
     * Clean up old PDF files (for maintenance)
     * 
     * @param int $daysOld Files older than X days
     * @return int Number of files deleted
     */
    public function cleanupOldPdfs(int $daysOld = 30): int
    {
        $directory = self::PDF_PATH;
        $files = Storage::disk(self::PDF_DISK)->files($directory);
        $cutoffTime = Carbon::now()->subDays($daysOld)->timestamp;

        $deletedCount = 0;

        foreach ($files as $file) {
            $lastModified = Storage::disk(self::PDF_DISK)->lastModified($file);

            if ($lastModified < $cutoffTime) {
                if (Storage::disk(self::PDF_DISK)->delete($file)) {
                    $deletedCount++;
                }
            }
        }

        Log::info("PDF cleanup completed", [
            'deleted_files' => $deletedCount,
            'cutoff_days' => $daysOld
        ]);

        return $deletedCount;
    }
}
