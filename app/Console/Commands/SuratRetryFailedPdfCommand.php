<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateSuratPdfJob;
use App\Models\SuratTerbit;
use Illuminate\Console\Command;

/**
 * Dispatch ulang job PDF generation untuk surat yang berstatus FAILED.
 *
 * Berguna untuk recovery manual ketika PDF gagal di-generate
 * karena error sementara (DomPDF timeout, disk penuh, dll).
 *
 * Usage:
 *   php artisan surat:retry-failed-pdf
 *   php artisan surat:retry-failed-pdf --limit=20
 */
class SuratRetryFailedPdfCommand extends Command
{
    protected $signature = 'surat:retry-failed-pdf
                            {--limit=10 : Jumlah surat yang akan dicoba ulang}';

    protected $description = 'Dispatch ulang job PDF generation untuk surat dengan status FAILED';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        // Artisan context: Auth::user() null → TerritoryScope tidak aktif
        $failedSurat = SuratTerbit::query()
            ->where('pdf_status', 'FAILED')
            ->where('status', 'AKTIF')
            ->limit($limit)
            ->get();

        if ($failedSurat->isEmpty()) {
            $this->info('Tidak ada surat dengan PDF gagal.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$failedSurat->count()} surat dengan PDF FAILED. Mendispatch ulang...");

        foreach ($failedSurat as $surat) {
            GenerateSuratPdfJob::dispatchForSurat($surat, [], regenerate: true);
            $this->line("  ✓ Dispatched: [{$surat->id}] {$surat->nomor_surat}");
        }

        $this->info("Selesai. {$failedSurat->count()} job PDF berhasil didispatch ulang.");

        return self::SUCCESS;
    }
}
