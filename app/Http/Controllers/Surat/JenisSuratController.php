<?php

declare(strict_types=1);

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Surat\StoreJenisSuratRequest;
use App\Models\JenisSurat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, JsonResponse, Request};
use Illuminate\Support\Facades\{DB, Redirect};

/**
 * JenisSuratController - Master Data Management untuk Jenis Surat
 * 
 * BUSINESS RULE: Super Admin Only Access
 * - Manage master data jenis surat
 * - Configure hybrid template system (template_category + template_sections)
 * - Control active/inactive status
 * 
 * Uses Hybrid Template System:
 * - template_category: selects which Blade template to use (keterangan, pengantar, etc.)
 * - template_sections: JSON config for custom content sections
 * - required_fields: array of field names required for this surat type
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class JenisSuratController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);

        // Apply policy authorization for all methods
        $this->authorizeResource(JenisSurat::class, 'jenisSurat');
    }

    /**
     * Display listing of jenis surat dengan search & filtering
     */
    public function index(Request $request): View
    {
        $query = JenisSurat::query()
            ->withCount(['suratTerbit' => function ($query) {
                $query->where('status', 'AKTIF');
            }]);

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('kode', 'LIKE', "%{$search}%")
                    ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('is_active', $status === 'active');
        }

        $jenisSurat = $query
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        // Stats for dashboard
        $stats = [
            'total' => JenisSurat::count(),
            'active' => JenisSurat::where('is_active', true)->count(),
            'with_template' => JenisSurat::whereNotNull('template_category')->count(),
            'most_used' => JenisSurat::withCount('suratTerbit')
                ->orderByDesc('surat_terbit_count')
                ->first()?->nama ?? 'N/A'
        ];

        return view('master_data.jenis_surat.index', compact('jenisSurat', 'stats'));
    }

    /**
     * Show form untuk create jenis surat baru
     */
    public function create(): View
    {
        return view('master_data.jenis_surat.create');
    }

    /**
     * Store jenis surat baru dengan hybrid template system
     */
    public function store(StoreJenisSuratRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $jenisSurat = JenisSurat::create([
                'kode' => $validated['kode'],
                'nama' => $validated['nama'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                // Hybrid Template System fields
                'template_category' => $validated['template_category'] ?? null,
                'template_sections' => $validated['template_sections'] ?? null,
                'required_fields' => $validated['required_fields'] ?? null,
                'signature_type' => $validated['signature_type'] ?? 'kepala_desa',
                'format_nomor' => $validated['format_nomor'] ?? null,
                'masa_berlaku_hari' => $validated['masa_berlaku_hari'] ?? 0,
                'persyaratan' => $validated['persyaratan'] ?? null,
                'biaya_admin' => $validated['biaya_admin'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            DB::commit();

            return Redirect::route('surat.jenis-surat.show', $jenisSurat)
                ->with('success', "Jenis surat '{$jenisSurat->nama}' berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollback();

            return Redirect::back()
                ->withInput()
                ->with('error', 'Gagal membuat jenis surat: ' . $e->getMessage());
        }
    }

    /**
     * Show detail jenis surat dengan usage statistics
     */
    public function show(JenisSurat $jenisSurat): View
    {
        // Load usage statistics
        $jenisSurat->loadCount([
            'suratTerbit',
            'suratTerbit as surat_aktif_count' => function ($query) {
                $query->where('status', 'AKTIF');
            },
            'suratTerbit as surat_expired_count' => function ($query) {
                $query->where('status', 'EXPIRED');
            },
            'suratTerbit as surat_cancelled_count' => function ($query) {
                $query->where('status', 'DIBATALKAN');
            }
        ]);

        // Recent surat using this type
        $recentSurat = $jenisSurat->suratTerbit()
            ->with(['penduduk:id,nama,nik', 'rt:id,nama', 'rw:id,nama', 'desa:id,nama'])
            ->latest()
            ->take(10)
            ->get();

        // Monthly usage trend (last 6 months)
        $monthlyUsage = $jenisSurat->suratTerbit()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return view('master_data.jenis_surat.show', compact('jenisSurat', 'recentSurat', 'monthlyUsage'));
    }

    /**
     * Show edit form
     */
    public function edit(JenisSurat $jenisSurat): View
    {
        return view('master_data.jenis_surat.edit', compact('jenisSurat'));
    }

    /**
     * Update jenis surat dengan hybrid template system
     */
    public function update(StoreJenisSuratRequest $request, JenisSurat $jenisSurat): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $jenisSurat->update([
                'kode' => $validated['kode'],
                'nama' => $validated['nama'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                // Hybrid Template System fields
                'template_category' => $validated['template_category'] ?? $jenisSurat->template_category,
                'template_sections' => $validated['template_sections'] ?? $jenisSurat->template_sections,
                'required_fields' => $validated['required_fields'] ?? $jenisSurat->required_fields,
                'signature_type' => $validated['signature_type'] ?? $jenisSurat->signature_type,
                'format_nomor' => $validated['format_nomor'] ?? $jenisSurat->format_nomor,
                'masa_berlaku_hari' => $validated['masa_berlaku_hari'] ?? $jenisSurat->masa_berlaku_hari,
                'persyaratan' => $validated['persyaratan'] ?? $jenisSurat->persyaratan,
                'biaya_admin' => $validated['biaya_admin'] ?? $jenisSurat->biaya_admin,
                'is_active' => $validated['is_active'] ?? $jenisSurat->is_active,
                'keterangan' => $validated['keterangan'] ?? $jenisSurat->keterangan,
            ]);

            DB::commit();

            return Redirect::route('master.jenis_surat.show', $jenisSurat)
                ->with('success', "Jenis surat '{$jenisSurat->nama}' berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollback();

            return Redirect::back()
                ->withInput()
                ->with('error', 'Gagal memperbarui jenis surat: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete jenis surat (dengan dependency check)
     */
    public function destroy(JenisSurat $jenisSurat): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Check for active surat using this type
            $activeSurat = $jenisSurat->suratTerbit()
                ->where('status', 'AKTIF')
                ->count();

            if ($activeSurat > 0) {
                return Redirect::back()
                    ->with('error', "Tidak dapat menghapus. Masih ada {$activeSurat} surat aktif menggunakan jenis ini.");
            }

            $jenisSurat->delete();

            DB::commit();

            return Redirect::route('master.jenis_surat.index')
                ->with('success', "Jenis surat '{$jenisSurat->nama}' berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollback();

            return Redirect::back()
                ->with('error', 'Gagal menghapus jenis surat: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active/inactive status via AJAX
     */
    public function toggleStatus(JenisSurat $jenisSurat): JsonResponse
    {
        try {
            $this->authorize('toggle', $jenisSurat);

            // Prevent deactivating if actively used
            if ($jenisSurat->is_active) {
                $activeSurat = $jenisSurat->suratTerbit()
                    ->where('status', 'AKTIF')
                    ->count();

                if ($activeSurat > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tidak dapat menonaktifkan. Masih ada {$activeSurat} surat aktif."
                    ], 400);
                }
            }

            $jenisSurat->update(['is_active' => !$jenisSurat->is_active]);

            return response()->json([
                'success' => true,
                'message' => $jenisSurat->is_active ? 'Jenis surat diaktifkan.' : 'Jenis surat dinonaktifkan.',
                'is_active' => $jenisSurat->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template preview info (for AJAX)
     * 
     * Returns hybrid template configuration for preview purposes.
     * The actual Blade template is rendered by PdfGeneratorService.
     */
    public function getTemplateInfo(JenisSurat $jenisSurat): JsonResponse
    {
        try {
            $this->authorize('view', $jenisSurat);

            return response()->json([
                'success' => true,
                'data' => [
                    'kode' => $jenisSurat->kode,
                    'nama' => $jenisSurat->nama,
                    'template_category' => $jenisSurat->template_category,
                    'template_category_label' => $jenisSurat->template_category_label,
                    'template_sections' => $jenisSurat->template_sections,
                    'required_fields' => $jenisSurat->required_fields,
                    'signature_type' => $jenisSurat->signature_type,
                    'is_ready' => $jenisSurat->isReadyForGeneration(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil info template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations via AJAX
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:jenis_surat,id'
        ]);

        try {
            $count = 0;
            $errors = [];

            foreach ($request->ids as $id) {
                $jenisSurat = JenisSurat::findOrFail($id);

                switch ($request->action) {
                    case 'activate':
                        $this->authorize('update', $jenisSurat);
                        $jenisSurat->update(['is_active' => true]);
                        $count++;
                        break;

                    case 'deactivate':
                        $this->authorize('update', $jenisSurat);
                        $activeSurat = $jenisSurat->suratTerbit()->where('status', 'AKTIF')->count();
                        if ($activeSurat === 0) {
                            $jenisSurat->update(['is_active' => false]);
                            $count++;
                        } else {
                            $errors[] = "{$jenisSurat->nama}: masih ada {$activeSurat} surat aktif";
                        }
                        break;

                    case 'delete':
                        $this->authorize('delete', $jenisSurat);
                        $activeSurat = $jenisSurat->suratTerbit()->where('status', 'AKTIF')->count();
                        if ($activeSurat === 0) {
                            $jenisSurat->delete();
                            $count++;
                        } else {
                            $errors[] = "{$jenisSurat->nama}: masih ada {$activeSurat} surat aktif";
                        }
                        break;
                }
            }

            $message = "{$count} jenis surat berhasil diproses.";
            if (!empty($errors)) {
                $message .= " Gagal: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $count,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses: ' . $e->getMessage()
            ], 500);
        }
    }
}
