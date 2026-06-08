<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\PendudukService;
use App\Services\KartuKeluargaService;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly PendudukService $pendudukService,
        private readonly KartuKeluargaService $kkService,
    ) {
        $this->middleware('auth');
    }

    public function __invoke(): View
    {
        try {
            /** @var \App\Models\User $user */
            $user = auth()->user();

            $pendudukStats = $this->pendudukService->getStats();
            $kkStats       = $this->kkService->getStats();
            $widgets       = $this->dashboardService->getDashboardWidgets($user);
        } catch (\Throwable $e) {
            Log::error('Dashboard data load failed', [
                'error' => $e->getMessage(),
            ]);

            // Fallback: render dashboard with empty data rather than 500
            $pendudukStats = ['total' => 0, 'aktif' => 0, 'pindah' => 0, 'meninggal' => 0, 'laki_laki' => 0, 'perempuan' => 0];
            $kkStats       = ['total' => 0, 'aktif' => 0, 'non_aktif' => 0];
            $widgets       = [
                'expiring_surat'        => collect(),
                'critical_surat_count'  => 0,
                'data_inconsistencies'  => collect(),
                'inconsistency_count'   => 0,
                'active_penduduk_count' => 0,
                'event_stats'           => ['kelahiran' => 0, 'kematian' => 0, 'pindah' => 0, 'datang' => 0, 'total' => 0],
                'pending_events'        => collect(),
                'recent_events'         => collect(),
                'surat_stats'           => ['total_aktif' => 0, 'bulan_ini' => 0, 'akan_kadaluarsa' => 0, 'kadaluarsa_hari_ini' => 0],
                'recent_surat'          => collect(),
                'penduduk_by_age'       => ['labels' => [], 'data' => []],
                'events_by_month'       => ['labels' => ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'], 'data' => array_fill(0, 12, 0)],
            ];
        }

        return view('dashboard', compact(
            'pendudukStats',
            'kkStats',
            'widgets',
        ));
    }
}
