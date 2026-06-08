<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function __invoke()
    {
        // Jika user sudah login, redirect ke dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        try {
            // Ambil data statistik untuk halaman welcome (public stats)
            $pendudukStats = $this->getPublicPendudukStats();
            $kkStats = $this->getPublicKkStats();
            $eventStats = $this->getPublicEventStats();
            $suratStats = $this->getPublicSuratStats();
            $pendudukByAge = $this->getPublicPendudukByAge();
            $eventsByMonth = $this->getPublicEventsByMonth();

        } catch (\Throwable $e) {
            Log::error('Welcome page data load failed', [
                'error' => $e->getMessage(),
            ]);

            // Fallback: render welcome with empty data rather than 500
            $pendudukStats = ['total' => 0, 'aktif' => 0, 'pindah' => 0, 'meninggal' => 0, 'laki_laki' => 0, 'perempuan' => 0];
            $kkStats = ['total' => 0, 'aktif' => 0, 'non_aktif' => 0];
            $eventStats = ['kelahiran' => 0, 'kematian' => 0, 'pindah' => 0, 'datang' => 0, 'total' => 0];
            $suratStats = ['total_aktif' => 0, 'bulan_ini' => 0, 'akan_kadaluarsa' => 0, 'kadaluarsa_hari_ini' => 0];
            $pendudukByAge = ['labels' => [], 'data' => []];
            $eventsByMonth = ['labels' => [], 'data' => []];
        }

        return view('welcome', compact(
            'pendudukStats',
            'kkStats', 
            'eventStats',
            'suratStats',
            'pendudukByAge',
            'eventsByMonth'
        ));
    }

    /**
     * Get public penduduk statistics (aggregated, no PII)
     */
    private function getPublicPendudukStats(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status_kependudukan_code = 'AKTIF' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN status_kependudukan_code = 'PINDAH' THEN 1 ELSE 0 END) as pindah,
                SUM(CASE WHEN status_kependudukan_code = 'MENINGGAL' THEN 1 ELSE 0 END) as meninggal,
                SUM(CASE WHEN jenis_kelamin = 'L' THEN 1 ELSE 0 END) as laki_laki,
                SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as perempuan
            FROM penduduks 
            WHERE deleted_at IS NULL
        ");

        return [
            'total' => (int) ($result->total ?? 0),
            'aktif' => (int) ($result->aktif ?? 0),
            'pindah' => (int) ($result->pindah ?? 0),
            'meninggal' => (int) ($result->meninggal ?? 0),
            'laki_laki' => (int) ($result->laki_laki ?? 0),
            'perempuan' => (int) ($result->perempuan ?? 0),
        ];
    }

    /**
     * Get public kartu keluarga statistics
     */
    private function getPublicKkStats(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status_kk = 'AKTIF' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN status_kk = 'NON_AKTIF' THEN 1 ELSE 0 END) as non_aktif
            FROM kartu_keluargas 
            WHERE deleted_at IS NULL
        ");

        return [
            'total' => (int) ($result->total ?? 0),
            'aktif' => (int) ($result->aktif ?? 0),
            'non_aktif' => (int) ($result->non_aktif ?? 0),
        ];
    }

    /**
     * Get public event statistics
     */
    private function getPublicEventStats(): array
    {
        $stats = DB::select("
            SELECT 
                event_type_code,
                COUNT(*) as count
            FROM events 
            WHERE status_data = 'VERIFIED'
            GROUP BY event_type_code
        ");

        $eventStats = [
            'kelahiran' => 0,
            'kematian' => 0, 
            'pindah' => 0,
            'datang' => 0,
            'total' => 0
        ];

        foreach ($stats as $stat) {
            $eventStats[strtolower($stat->event_type_code)] = (int) $stat->count;
            $eventStats['total'] += (int) $stat->count;
        }

        return $eventStats;
    }

    /**
     * Get public surat statistics
     */
    private function getPublicSuratStats(): array
    {
        $result = DB::selectOne("
            SELECT 
                COUNT(CASE WHEN status = 'AKTIF' THEN 1 END) as total_aktif,
                COUNT(CASE WHEN status = 'AKTIF' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') THEN 1 END) as bulan_ini,
                COUNT(CASE WHEN status = 'AKTIF' AND masa_berlaku_sampai BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 END) as akan_kadaluarsa,
                COUNT(CASE WHEN status = 'AKTIF' AND DATE(masa_berlaku_sampai) = CURDATE() THEN 1 END) as kadaluarsa_hari_ini
            FROM surat_terbits 
            WHERE deleted_at IS NULL
        ");

        return [
            'total_aktif' => (int) ($result->total_aktif ?? 0),
            'bulan_ini' => (int) ($result->bulan_ini ?? 0),
            'akan_kadaluarsa' => (int) ($result->akan_kadaluarsa ?? 0),
            'kadaluarsa_hari_ini' => (int) ($result->kadaluarsa_hari_ini ?? 0),
        ];
    }

    /**
     * Get public penduduk by age distribution
     */
    private function getPublicPendudukByAge(): array
    {
        $ageGroups = DB::select("
            SELECT 
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 5 THEN '0-4'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 15 THEN '5-14'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 25 THEN '15-24'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 35 THEN '25-34'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 45 THEN '35-44'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 55 THEN '45-54'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 65 THEN '55-64'
                    ELSE '65+'
                END as age_group,
                COUNT(*) as count
            FROM penduduks 
            WHERE deleted_at IS NULL 
                AND status_kependudukan_code = 'AKTIF'
                AND tgl_lahir IS NOT NULL
            GROUP BY age_group
            ORDER BY age_group
        ");

        $labels = [];
        $data = [];
        
        foreach ($ageGroups as $group) {
            $labels[] = $group->age_group;
            $data[] = (int) $group->count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get public events by month (last 12 months)
     */
    private function getPublicEventsByMonth(): array
    {
        $monthlyEvents = DB::select("
            SELECT 
                DATE_FORMAT(event_date, '%Y-%m') as month,
                COUNT(*) as count
            FROM events 
            WHERE status_data = 'VERIFIED'
                AND event_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(event_date, '%Y-%m')
            ORDER BY month
        ");

        // Generate last 12 months labels
        $labels = [];
        $data = array_fill(0, 12, 0);
        
        for ($i = 11; $i >= 0; $i--) {
            $month = date('M', strtotime("-$i months"));
            $yearMonth = date('Y-m', strtotime("-$i months"));
            $labels[] = $month;
            
            // Find data for this month
            foreach ($monthlyEvents as $event) {
                if ($event->month === $yearMonth) {
                    $data[11 - $i] = (int) $event->count;
                    break;
                }
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}