<?php

declare(strict_types=1);

namespace App\Http\Controllers\Surat;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Read-only master reference for seeded jenis surat data.
 */
class JenisSuratController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->authorizeResource(JenisSurat::class, 'jenisSurat');
    }

    public function index(Request $request): View
    {
        $query = JenisSurat::query()
            ->withCount([
                'suratTerbit',
                'suratTerbit as surat_aktif_count' => fn($query) => $query->where('status', 'AKTIF'),
                'suratTerbit as surat_batal_count' => fn($query) => $query->where('status', 'BATAL'),
            ]);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($query) use ($search) {
                $query->where('kode', 'LIKE', "%{$search}%")
                    ->orWhere('nama', 'LIKE', "%{$search}%")
                    ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'active');
        }

        $jenisSurat = $query->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => JenisSurat::count(),
            'active' => JenisSurat::where('is_active', true)->count(),
            'with_template' => JenisSurat::whereNotNull('template_category')->count(),
            'most_used' => JenisSurat::withCount('suratTerbit')
                ->orderByDesc('surat_terbit_count')
                ->first()?->nama ?? 'N/A',
        ];

        return view('master_data.jenis_surat.index', compact('jenisSurat', 'stats'));
    }

    public function show(JenisSurat $jenisSurat): View
    {
        $jenisSurat->loadCount([
            'suratTerbit',
            'suratTerbit as surat_aktif_count' => fn($query) => $query->where('status', 'AKTIF'),
            'suratTerbit as surat_batal_count' => fn($query) => $query->where('status', 'BATAL'),
            'suratTerbit as surat_kadaluarsa_count' => fn($query) => $query
                ->where('status', 'AKTIF')
                ->whereNotNull('tanggal_kadaluarsa')
                ->whereDate('tanggal_kadaluarsa', '<', today()),
        ]);

        $recentSurat = $jenisSurat->suratTerbit()
            ->with([
                'penduduk:id,nama_lengkap,nik',
                'rt:id,nomor_rt,rw_id',
                'rw:id,nomor_rw,desa_id',
                'desa:id,nama',
            ])
            ->latest()
            ->take(10)
            ->get();

        $monthlyUsage = $jenisSurat->suratTerbit()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return view('master_data.jenis_surat.show', compact('jenisSurat', 'recentSurat', 'monthlyUsage'));
    }
}
