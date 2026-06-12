<?php

declare(strict_types=1);

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Surat\{StoreSuratTerbitRequest, BatalkanSuratRequest};
use App\Jobs\GenerateSuratPdfJob;
use App\Services\{SuratTerbitService, PdfGeneratorService};
use App\Models\{SuratTerbit, JenisSurat, Penduduk};
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, Log, DB};
use Illuminate\View\View;
use Exception;

/**
 * SuratTerbitController - Web interface untuk surat management
 * 
 * Handles complete surat lifecycle: generate, view, download, cancel.
 * Web-focused: returns views with proper error handling and user feedback.
 * Territory-aware: all operations respect user jurisdiction.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class SuratTerbitController extends Controller
{
    public function __construct(
        private readonly SuratTerbitService $suratService,
        private readonly PdfGeneratorService $pdfService
    ) {
        // Apply authorization middleware
        $this->middleware('auth');
    }

    /**
     * Display paginated surat list with territory filtering
     */
    public function index(Request $request): View
    {
        $filters = [];

        try {
            $this->authorize('viewAny', SuratTerbit::class);

            $filters = $request->only([
                'status',
                'jenis_surat_kode',
                'tanggal_dari',
                'tanggal_sampai',
                'search'
            ]);

            // Territory scope auto-applied via service
            $surats = $this->suratService->getPaginatedSuratList(
                $filters,
                (int) $request->input('per_page', 15),
                Auth::user()
            );

            // Additional data for filters
            $jenisSuratOptions = $this->suratService->getActiveJenisSuratOptions()
                ->pluck('nama', 'kode')
                ->toArray();

            $statusOptions = [
                'AKTIF' => 'Aktif',
                'BATAL' => 'Dibatalkan'
            ];

            // Statistik untuk summary cards, scoped sesuai wilayah user
            $statsQuery = SuratTerbit::query()->forTerritory(Auth::user());
            $stats = [
                'total'  => (clone $statsQuery)->count(),
                'aktif'  => (clone $statsQuery)->where('status', 'AKTIF')->count(),
                'batal'  => (clone $statsQuery)->where('status', 'BATAL')->count(),
            ];

            return view('surat.terbit.index', compact(
                'surats',
                'filters',
                'jenisSuratOptions',
                'statusOptions',
                'stats'
            ));
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses untuk melihat daftar surat.');
        } catch (Exception $e) {
            Log::error('SuratTerbit index failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);

            return view('surat.terbit.index')
                ->with('error', 'Terjadi kesalahan saat memuat data surat.')
                ->with('surats', new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15))
                ->with('filters', $filters)
                ->with('jenisSuratOptions', [])
                ->with('statusOptions', ['AKTIF' => 'Aktif', 'BATAL' => 'Dibatalkan'])
                ->with('stats', ['total' => 0, 'aktif' => 0, 'batal' => 0]);
        }
    }

    /**
     * Show form for generating new surat
     */
    public function create(): View|RedirectResponse
    {
        try {
            $this->authorize('create', SuratTerbit::class);

            // Get active jenis surat with templates
            $jenisSuratOptions = $this->suratService->getActiveJenisSuratWithTemplates();

            return view('surat.terbit.create', compact('jenisSuratOptions'));
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses untuk membuat surat.');
        } catch (Exception $e) {
            Log::error('SuratTerbit create form failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('surat.terbit.index')
                ->with('error', 'Terjadi kesalahan saat memuat form surat.');
        }
    }

    /**
     * Store newly generated surat
     * 
     * ✅ FIXED: Removed double transaction wrapper - service already handles transaction
     */
    public function store(StoreSuratTerbitRequest $request): RedirectResponse
    {
        try {
            $this->authorize('create', SuratTerbit::class);

            // Service handles DB::transaction internally
            $surat = $this->suratService->createSurat(
                $request->getValidatedWithDefaults()
            );

            // Dispatch PDF generation job AFTER transaction commits (service returns = committed)
            // to prevent race condition where worker can't find the record
            GenerateSuratPdfJob::dispatch($surat->id);

            // Success with proper feedback
            return redirect()->route('surat.terbit.show', $surat)
                ->with('success', 'Surat berhasil diterbitkan dengan nomor: ' . $surat->nomor_surat)
                ->with('surat_generated', true); // Flag for UI highlighting

        } catch (AuthorizationException $e) {
            Log::warning('SuratTerbit store authorization failed', [
                'user_id' => Auth::id(),
                'data' => $request->validated()
            ]);

            return back()->withInput()
                ->with('error', 'Anda tidak memiliki akses untuk membuat surat.');
        } catch (\DomainException $e) {
            Log::info('SuratTerbit store business rule violation', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return back()->withInput()
                ->with('warning', $e->getMessage());
        } catch (QueryException $e) {
            $isDuplicateNomor = $this->isDuplicateNomorSurat($e);

            Log::error('SuratTerbit store database failed', [
                'user_id' => Auth::id(),
                'duplicate_nomor' => $isDuplicateNomor,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->with(
                    $isDuplicateNomor ? 'warning' : 'error',
                    $isDuplicateNomor
                        ? 'Nomor surat bentrok dengan data yang sudah ada. Silakan coba terbitkan ulang.'
                        : 'Terjadi kesalahan database saat membuat surat. Silakan coba lagi.'
                );
        } catch (Exception $e) {
            Log::error('SuratTerbit store failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat surat. Silakan coba lagi.');
        }
    }

    private function isDuplicateNomorSurat(QueryException $e): bool
    {
        $message = $e->getMessage();
        $isIntegrityError = (string) $e->getCode() === '23000'
            || in_array($e->errorInfo[1] ?? null, [1062, 19, 2067], true);

        return $isIntegrityError
            && (str_contains($message, 'surat_terbit_nomor_surat_unique')
            || str_contains($message, 'Duplicate entry')
            || str_contains($message, 'nomor_surat'));
    }

    /**
     * Display detailed surat information
     */
    public function show(SuratTerbit $suratTerbit): View|RedirectResponse
    {
        try {
            $this->authorize('view', $suratTerbit);

            // Load relationships for display
            $suratTerbit->load([
                'jenisSurat:kode,nama,masa_berlaku_hari',
                'penduduk:id,nama_lengkap,nik,tempat_lahir,tgl_lahir,rt_id',
                'penduduk.rt:id,nomor_rt,rw_id',
                'penduduk.rt.rw:id,nomor_rw,desa_id',
                'penduduk.rt.rw.desa:id,kode_desa,nama'
            ]);

            // Check PDF status for download availability (source of truth: file_path)
            $pdfAvailable = !empty($suratTerbit->file_path) &&
                $this->pdfService->pdfExists($suratTerbit->file_path);

            return view('surat.terbit.show', compact('suratTerbit', 'pdfAvailable'));
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses untuk melihat surat ini.');
        } catch (ModelNotFoundException $e) {
            abort(404, 'Surat tidak ditemukan.');
        } catch (Exception $e) {
            Log::error('SuratTerbit show failed', [
                'user_id' => Auth::id(),
                'surat_id' => $suratTerbit->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memuat detail surat.');
        }
    }

    /**
     * Download PDF surat
     * * @param SuratTerbit $suratTerbit
     * @return Response|RedirectResponse
     */
    public function download(SuratTerbit $suratTerbit): Response|RedirectResponse
    {
        try {
            // 1. Verifikasi izin akses
            $this->authorize('download', $suratTerbit);

            // 2. Ambil path dari kolom 'file_path' (sebelumnya salah panggil 'pdf_path')
            $filePath = $suratTerbit->file_path;

            // 3. Validasi keberadaan file di database dan di filesystem
            if (empty($filePath) || !$this->pdfService->pdfExists($filePath)) {
                // Log detail untuk memudahkan administrator melacak file yang hilang
                Log::warning('File PDF fisik tidak ditemukan', [
                    'surat_id' => $suratTerbit->id,
                    'file_path' => $filePath,
                    'exists_in_db' => !empty($filePath)
                ]);

                return back()->with('warning', 'PDF belum siap atau file tidak ditemukan di server. Silakan hubungi administrator.');
            }

            // 4. Ambil konten PDF melalui Service
            $pdfContent = $this->pdfService->getPdfContent($filePath);

            // 5. Generate nama file yang bersih (slug) untuk user
            // Mengganti karakter non-alphanumeric pada nomor surat agar aman untuk file system
            $safeNomorSurat = str_replace(['/', '\\', ' '], '_', $suratTerbit->nomor_surat);
            $filename = sprintf(
                'Surat_%s_%s_%s.pdf',
                $suratTerbit->jenisSurat->kode ?? 'SURAT',
                $safeNomorSurat,
                now()->format('Ymd_His')
            );

            // 6. Return response download
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh surat ini.');
        } catch (\Exception $e) {
            // Log error asli untuk debugging dev
            Log::error('SuratTerbit download failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'surat_id' => $suratTerbit->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal mengunduh PDF karena kendala teknis.');
        }
    }

    /**
     * Regenerate private PDF from the latest Blade template without changing surat data.
     */
    public function regeneratePdf(SuratTerbit $suratTerbit): RedirectResponse
    {
        try {
            $this->authorize('regeneratePdf', $suratTerbit);

            $oldFilePath = $suratTerbit->file_path;
            if ($oldFilePath) {
                rescue(fn() => $this->pdfService->deletePdf($oldFilePath), false, report: false);
            }

            GenerateSuratPdfJob::dispatchForSurat($suratTerbit, [], true);

            return redirect()->route('surat.terbit.show', $suratTerbit)
                ->with('success', 'PDF sedang dibuat ulang menggunakan template terbaru.')
                ->with('surat_generated', true);
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses untuk membuat ulang PDF surat ini.');
        } catch (Exception $e) {
            Log::error('SuratTerbit regenerate PDF failed', [
                'user_id' => Auth::id(),
                'surat_id' => $suratTerbit->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuat ulang PDF karena kendala teknis.');
        }
    }

    /**
     * Show form for cancelling surat
     */
    public function batalkanForm(SuratTerbit $suratTerbit): View|RedirectResponse
    {
        try {
            $this->authorize('batalkan', $suratTerbit);

            // Only AKTIF surat can be cancelled
            if (!$suratTerbit->isAktif()) {
                return redirect()->route('surat.terbit.show', $suratTerbit)
                    ->with('warning', 'Surat ini sudah dibatalkan atau tidak aktif.');
            }

            return view('surat.terbit.batalkan', compact('suratTerbit'));
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan surat ini.');
        } catch (Exception $e) {
            Log::error('SuratTerbit batalkan form failed', [
                'user_id' => Auth::id(),
                'surat_id' => $suratTerbit->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('surat.terbit.show', $suratTerbit)
                ->with('error', 'Terjadi kesalahan saat memuat form pembatalan.');
        }
    }

    /**
     * Process surat cancellation
     * 
     * ✅ FIXED: PDF deletion moved OUTSIDE transaction to prevent data inconsistency.
     * File system operations can't be rolled back, so we delete after DB commit succeeds.
     */
    public function batalkan(BatalkanSuratRequest $request, SuratTerbit $suratTerbit): RedirectResponse
    {
        try {
            $this->authorize('batalkan', $suratTerbit);

            // Business validation: only AKTIF surat can be cancelled
            if (!$suratTerbit->isAktif()) {
                return back()->with('warning', 'Surat ini sudah dibatalkan atau tidak aktif.');
            }

            // Store file path before update (in case it gets cleared)
            $filePath = $suratTerbit->file_path;

            // DB transaction for status update only
            DB::transaction(function () use ($request, $suratTerbit) {
                $suratTerbit->update($request->getValidatedWithAudit());
            });

            // Delete PDF AFTER transaction commits - file deletion is non-reversible
            // Use rescue() to prevent failure from affecting user experience
            if ($filePath) {
                rescue(
                    fn() => $this->pdfService->deletePdf($filePath),
                    function ($e) use ($filePath) {
                        Log::warning('PDF delete failed after batalkan', [
                            'path' => $filePath,
                            'error' => $e->getMessage()
                        ]);
                    }
                );
            }

            Log::info('SuratTerbit cancelled', [
                'user_id' => Auth::id(),
                'surat_id' => $suratTerbit->id,
                'nomor_surat' => $suratTerbit->nomor_surat,
                'alasan' => $request->input('alasan_batal')
            ]);

            return redirect()->route('surat.terbit.show', $suratTerbit)
                ->with('success', 'Surat berhasil dibatalkan.');
        } catch (AuthorizationException $e) {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan surat ini.');
        } catch (Exception $e) {
            Log::error('SuratTerbit batalkan failed', [
                'user_id' => Auth::id(),
                'surat_id' => $suratTerbit->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Terjadi kesalahan saat membatalkan surat. Silakan coba lagi.');
        }
    }

    /**
     * AJAX: Search penduduk for surat generation form
     */
    public function searchPenduduk(Request $request): JsonResponse
    {
        try {
            $this->authorize('create', SuratTerbit::class);

            $query = $request->input('q', '');

            if (strlen($query) < 3) {
                return response()->json([
                    'results' => [],
                    'message' => 'Masukkan minimal 3 karakter untuk pencarian.'
                ]);
            }

            // Territory-aware search via service
            $pendudukList = Penduduk::with(['rt.rw.desa:id,nama'])
                ->where('status_kependudukan_code', 'AKTIF')
                ->where(function ($q) use ($query) {
                    $q->where('nama_lengkap', 'like', "%{$query}%")
                        ->orWhere('nik', 'like', "%{$query}%");
                })
                ->forTerritory(Auth::user()) // Territory scope applied
                ->limit(10)
                ->get(['id', 'nama_lengkap', 'nik', 'rt_id']);

            $results = $pendudukList->map(function ($penduduk) {
                return [
                    'id' => $penduduk->id,
                    'text' => $penduduk->nama_lengkap . ' (NIK: ' . $penduduk->nik . ')',
                    'nama_lengkap' => $penduduk->nama_lengkap,
                    'nik' => $penduduk->nik,
                    'alamat' => $penduduk->rt?->rw?->desa?->nama ?? 'N/A'
                ];
            });

            return response()->json(['results' => $results]);
        } catch (Exception $e) {
            Log::error('Penduduk search failed', [
                'user_id' => Auth::id(),
                'query' => $query ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'results' => [],
                'error' => 'Terjadi kesalahan saat mencari penduduk.'
            ], 500);
        }
    }

    /**
     * AJAX: Get jenis surat details for form
     * 
     * ✅ FIXED: Uses hybrid template system instead of old template_filename
     */
    public function getJenisSuratDetails(string $kode): JsonResponse
    {
        try {
            $jenisSurat = JenisSurat::where('kode', $kode)->firstOrFail();

            // Authorization check
            $this->authorize('view', $jenisSurat);

            if (!$jenisSurat->is_active) {
                return response()->json(['error' => 'Jenis surat tidak aktif.'], 404);
            }

            $sections = $jenisSurat->getSections();

            return response()->json([
                'kode'              => $jenisSurat->kode,
                'nama'              => $jenisSurat->nama,
                'masa_berlaku_hari' => $jenisSurat->masa_berlaku_hari,
                'deskripsi'         => $jenisSurat->deskripsi,
                'keterangan'        => $jenisSurat->keterangan,
                'template_category' => $jenisSurat->template_category,
                'required_fields'   => $sections['required_fields'] ?? [],
                'dynamic_fields'    => $this->resolveDynamicFields($jenisSurat, $sections),
                'is_ready'          => $jenisSurat->isReadyForGeneration(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Jenis surat tidak ditemukan.'], 404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        } catch (\TypeError $e) {
            Log::error('TypeError in getJenisSuratDetails', [
                'kode' => $kode,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'error' => 'Type error occurred',
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Exception in getJenisSuratDetails', [
                'kode' => $kode,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve dynamic request fields required by hybrid templates.
     *
     * @param array<string, mixed> $sections
     * @return array<int, array{name:string,label:string,required:bool}>
     */
    private function resolveDynamicFields(JenisSurat $jenisSurat, array $sections): array
    {
        $sectionKeys = [
            'data_fields',
            'additional_fields',
            'detail_fields',
            'activity_fields',
            'related_fields',
        ];

        $baseFields = [
            'nama_lengkap',
            'nik',
            'no_kk',
            'tempat_lahir',
            'tanggal_lahir',
            'tgl_lahir',
            'tanggal_lahir_text',
            'tempat_tanggal_lahir',
            'bin_binti',
            'jenis_kelamin',
            'agama',
            'pekerjaan',
            'pendidikan',
            'status_kawin',
            'status_perkawinan',
            'kewarganegaraan',
            'alamat',
            'alamat_kk',
            'alamat_rt_rw',
            'rt',
            'rw',
            'desa',
            'kecamatan',
            'kabupaten',
            'provinsi',
            'tujuan',
            'keperluan',
        ];

        $fields = [];
        foreach ($sectionKeys as $key) {
            $sectionFields = $sections[$key] ?? [];
            if (is_array($sectionFields)) {
                $fields = array_merge($fields, $sectionFields);
            }
        }

        if ($jenisSurat->template_category === 'internal') {
            $fields = array_merge($fields, [
                'kepada',
                'alamat_tujuan',
                'perihal',
                'lampiran',
                'nomor_rujukan',
                'nomor_surat_masuk',
                'tanggal_surat_masuk',
                'isi_balasan',
            ]);
        }

        $requiredFields = $sections['required_fields'] ?? [];
        $fieldLabels = $jenisSurat->getFieldLabels();

        return collect($fields)
            ->filter(fn($field) => is_string($field) && !in_array($field, $baseFields, true))
            ->unique()
            ->values()
            ->map(fn(string $field) => [
                'name' => $field,
                'label' => $fieldLabels[$field] ?? str($field)->replace('_', ' ')->title()->toString(),
                'required' => in_array($field, $requiredFields, true),
            ])
            ->all();
    }

    /**
     * Dashboard: Get expiring surat widget data
     */
    public function getExpiringSuratWidget(): View
    {
        try {
            $this->authorize('viewExpiring', SuratTerbit::class);

            $expiringSurat = $this->suratService->getSuratExpiringSoon(30, 10);

            return view('surat.terbit.expiring-widget', compact('expiringSurat'));
        } catch (Exception $e) {
            Log::error('Expiring surat widget failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return view('surat.terbit.expiring-widget')
                ->with('expiringSurat', collect())
                ->with('error', 'Gagal memuat data surat mendekati kadaluarsa.');
        }
    }
}
