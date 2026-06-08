<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SuratTerbit;
use App\Notifications\SuratPdfFailedNotification;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Queue job for generating surat PDF files asynchronously
 * 
 * Handles PDF generation in background to prevent blocking UI.
 * Updates SuratTerbit model with generation status and file path.
 * Includes retry logic and comprehensive error handling.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class GenerateSuratPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of retry attempts
     */
    public int $tries = 3;

    /**
     * Maximum seconds job can run before timeout
     */
    public int $timeout = 120;

    /**
     * Seconds to wait before retrying failed job
     */
    public int $backoff = 30;

    /**
     * Create a new job instance
     *
     * @param int $suratId SuratTerbit ID to generate PDF for
     * @param array $options PDF generation options
     * @param bool $regenerate Force regeneration even if PDF exists
     */
    public function __construct(
        private readonly int $suratId,
        private readonly array $options = [],
        private readonly bool $regenerate = false
    ) {
        // Set queue name based on priority
        $this->onQueue($options['queue'] ?? 'pdf-generation');
    }

    /**
     * Execute the job
     *
     * @param PdfGeneratorService $pdfService
     * @return void
     */
    public function handle(PdfGeneratorService $pdfService): void
    {
        Log::info("Starting PDF generation job", [
            'surat_id' => $this->suratId,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Load surat with necessary relationships
            $surat = SuratTerbit::with([
                'jenisSurat',
                'penduduk',
                'desa'
            ])->findOrFail($this->suratId);

            // Check if PDF already exists and regeneration not forced
            if (!$this->regenerate && $surat->pdf_status === 'READY' && $surat->file_path) {
                Log::info("PDF already exists, skipping generation", [
                    'surat_id' => $this->suratId,
                    'file_path' => $surat->file_path
                ]);
                return;
            }

            // Mark as processing
            $surat->update([
                'pdf_status' => 'PROCESSING',
                'pdf_generated_at' => null,
                'file_path' => null,
            ]);

            // Generate and save PDF
            $pdfPath = $pdfService->generateAndSavePdf($surat, $this->options);

            // Update surat with success status
            $surat->update([
                'pdf_status' => 'READY',
                'file_path' => $pdfPath,
                'pdf_generated_at' => Carbon::now(),
            ]);

            Log::info("PDF generation completed successfully", [
                'surat_id' => $this->suratId,
                'nomor_surat' => $surat->nomor_surat,
                'file_path' => $pdfPath,
                'attempt' => $this->attempts(),
            ]);
        } catch (Exception $e) {
            $this->handleFailure($e);
        }
    }

    /**
     * Handle job failure
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("PDF generation job failed permanently", [
            'surat_id' => $this->suratId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        try {
            // Update surat with failed status
            $surat = SuratTerbit::find($this->suratId);
            if ($surat) {
                $surat->update([
                    'pdf_status' => 'FAILED',
                    'file_path' => null,
                    'pdf_generated_at' => null,
                ]);

                // Notifikasi ke pembuat surat agar segera menindaklanjuti
                $surat->load('createdBy');
                if ($surat->createdBy) {
                    $surat->createdBy->notify(
                        new SuratPdfFailedNotification($surat, $exception->getMessage())
                    );
                }
            }
        } catch (Exception $e) {
            Log::error("Failed to update surat status after job failure", [
                'surat_id' => $this->suratId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle temporary failure with retry logic
     *
     * @param Exception $exception
     * @return void
     * @throws Exception
     */
    private function handleFailure(Exception $exception): void
    {
        Log::warning("PDF generation attempt failed", [
            'surat_id' => $this->suratId,
            'attempt' => $this->attempts(),
            'max_attempts' => $this->tries,
            'error' => $exception->getMessage(),
        ]);

        try {
            // Update surat with processing error
            $surat = SuratTerbit::find($this->suratId);
            if ($surat) {
                $surat->update([
                    'pdf_status' => 'PROCESSING', // Will retry
                    'file_path' => null,
                    'pdf_generated_at' => null,
                ]);
            }
        } catch (Exception $e) {
            Log::error("Failed to update surat status during retry", [
                'surat_id' => $this->suratId,
                'error' => $e->getMessage(),
            ]);
        }

        // Re-throw to trigger retry mechanism
        throw $exception;
    }

    /**
     * Get job display name for monitoring
     *
     * @return string
     */
    public function displayName(): string
    {
        return "Generate PDF for Surat ID: {$this->suratId}";
    }

    /**
     * Get job tags for monitoring and filtering
     *
     * @return array
     */
    public function tags(): array
    {
        return ['pdf-generation', "surat:{$this->suratId}"];
    }

    /**
     * Determine number of seconds to wait before retrying
     *
     * @return int
     */
    public function backoff(): int
    {
        // Exponential backoff: 30s, 60s, 120s
        return $this->backoff * $this->attempts();
    }

    /**
     * Static helper to dispatch PDF generation job
     *
     * @param SuratTerbit $surat
     * @param array $options
     * @param bool $regenerate
     * @return void
     */
    public static function dispatchForSurat(
        SuratTerbit $surat,
        array $options = [],
        bool $regenerate = false
    ): void {
        // Mark as processing before dispatching
        $surat->update([
            'pdf_status' => 'PROCESSING',
            'file_path' => null,
            'pdf_generated_at' => null,
        ]);

        // Dispatch job using the Dispatchable trait method
        static::dispatch($surat->id, $options, $regenerate);

        Log::info("PDF generation job dispatched", [
            'surat_id' => $surat->id,
            'nomor_surat' => $surat->nomor_surat,
            'regenerate' => $regenerate,
        ]);
    }

    /**
     * Static helper to dispatch high priority PDF generation
     *
     * @param SuratTerbit $surat
     * @param array $options
     * @return void
     */
    public static function dispatchHighPriority(
        SuratTerbit $surat,
        array $options = []
    ): void {
        $options['queue'] = 'pdf-priority';

        self::dispatchForSurat($surat, $options, true);
    }

    /**
     * Static helper to dispatch bulk PDF generation jobs
     *
     * @param array $suratIds Array of SuratTerbit IDs
     * @param array $options Generation options
     * @return int Number of jobs dispatched
     */
    public static function dispatchBulk(array $suratIds, array $options = []): int
    {
        $options['queue'] = 'pdf-bulk';
        $dispatched = 0;

        foreach ($suratIds as $suratId) {
            try {
                $surat = SuratTerbit::findOrFail($suratId);
                self::dispatchForSurat($surat, $options);
                $dispatched++;
            } catch (Exception $e) {
                Log::warning("Failed to dispatch bulk PDF job", [
                    'surat_id' => $suratId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("Bulk PDF generation jobs dispatched", [
            'total_dispatched' => $dispatched,
            'total_requested' => count($suratIds),
        ]);

        return $dispatched;
    }
}
