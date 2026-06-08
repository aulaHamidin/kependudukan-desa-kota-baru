<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Import;

use App\Exports\PendudukImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\PendudukImport;
use App\Models\Agama;
use App\Models\HubunganKeluarga;
use App\Services\Import\PendudukImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PendudukImportController extends Controller
{
    public function __construct(
        private PendudukImportService $importService,
    ) {}

    /**
     * Show import page with upload form and reference data.
     */
    public function index(): View
    {
        return view('penduduk.import.index', [
            'agamaList' => Agama::orderBy('kode')->get(['kode', 'nama']),
            'hubunganList' => HubunganKeluarga::orderBy('kode')->get(['kode', 'nama']),
        ]);
    }

    /**
     * Download import template Excel.
     */
    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new PendudukImportTemplateExport(),
            'template-import-penduduk.xlsx'
        );
    }

    /**
     * Phase 1: Upload, parse, and validate all rows.
     */
    public function validateUpload(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $import = new PendudukImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()
                ->route('penduduk.import.index')
                ->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        $rows = $import->getRows();

        if ($rows->isEmpty()) {
            return redirect()
                ->route('penduduk.import.index')
                ->with('error', 'File tidak berisi data.');
        }

        // Filter to only rows with actual data (nik + no_kk present)
        $dataRows = $rows->filter(function ($row) {
            $nik = trim((string) ($row['nik'] ?? ''));
            $noKk = trim((string) ($row['no_kk'] ?? ''));
            return $nik !== '' || $noKk !== '';
        })->values();

        $result = $this->importService->validate($dataRows, $request->user());

        // Store validated rows in session for Phase 2
        if ($result['valid']) {
            session(['import_penduduk_rows' => $dataRows->toArray()]);
            session(['import_penduduk_hash' => md5(serialize($dataRows->toArray()))]);
        }

        return view('penduduk.import.validate', [
            'result' => $result,
            'rows' => $dataRows,
        ]);
    }

    /**
     * Phase 2: Execute import (only if validation passed).
     */
    public function execute(Request $request): RedirectResponse
    {
        $storedRows = session('import_penduduk_rows');
        $storedHash = session('import_penduduk_hash');

        if (!$storedRows || !$storedHash) {
            return redirect()
                ->route('penduduk.import.index')
                ->with('error', 'Sesi import telah kadaluarsa. Silakan upload ulang.');
        }

        $rows = collect($storedRows);

        // Re-validate to prevent stale session data (race condition protection)
        $validationResult = $this->importService->validate($rows, $request->user());
        if (!$validationResult['valid']) {
            session()->forget(['import_penduduk_rows', 'import_penduduk_hash']);
            return redirect()
                ->route('penduduk.import.index')
                ->with('error', 'Data sudah berubah sejak validasi terakhir. Silakan upload ulang file Anda.');
        }

        try {
            $result = $this->importService->execute($rows, $request->user());

            session()->forget(['import_penduduk_rows', 'import_penduduk_hash']);

            return redirect()
                ->route('penduduk.import.index')
                ->with('success', "Berhasil mengimpor {$result['imported_count']} data penduduk.");
        } catch (\Exception $e) {
            Log::error('Import penduduk failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'actor_id' => $request->user()->id,
            ]);

            session()->forget(['import_penduduk_rows', 'import_penduduk_hash']);

            return redirect()
                ->route('penduduk.import.index')
                ->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}
