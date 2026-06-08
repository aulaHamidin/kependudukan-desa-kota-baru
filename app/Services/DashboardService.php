<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\KartuKeluarga;
use App\Models\Rt;
use App\Models\SuratTerbit;
use App\Models\User;
use App\Models\Views\VDataInconsistency;
use App\Models\Views\VKkWithMembers;
use App\Models\Views\VPendudukAktif;
use App\Models\Views\VSuratExpiringSoon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * DashboardService — aggregates data from DB views for dashboard widgets.
 *
 * All queries are scoped to the authenticated user's territory (RT IDs)
 * to prevent cross-desa/cross-RT data leakage.
 */
class DashboardService
{
    /**
     * Get RT IDs accessible by the given user based on their role.
     *
     * @return SupportCollection<int, int>
     */
    private function rtIdsForUser(User $user): SupportCollection
    {
        if ($user->hasRole('super_admin')) {
            return Rt::pluck('id');
        }

        if ($user->hasRole('admin_desa')) {
            return Rt::whereHas('rw', fn($q) => $q->where('desa_id', $user->desa_id))
                ->pluck('id');
        }

        if ($user->hasRole('admin_rw')) {
            return Rt::where('rw_id', $user->rw_id)->pluck('id');
        }

        if ($user->hasRole('admin_rt') || $user->hasRole('viewer')) {
            return $user->rt_id !== null ? collect([$user->rt_id]) : collect();
        }

        return collect();
    }

    /**
     * Get surat that are expiring soon (within given days)
     *
     * Uses v_surat_expiring_soon database view, filtered by territory.
     *
     * @param User $user Authenticated user for territory scoping
     * @param int $limit Max results
     * @return Collection<int, VSuratExpiringSoon>
     */
    public function getExpiringSurat(User $user, int $limit = 10): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return VSuratExpiringSoon::query()
            ->whereIn('rt_id', $rtIds)
            ->orderBy('days_remaining')
            ->limit($limit)
            ->get();
    }

    /**
     * Get critically expiring surat (≤ 3 days)
     */
    public function getCriticallyExpiringSurat(User $user): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return VSuratExpiringSoon::critical()
            ->whereIn('rt_id', $rtIds)
            ->get();
    }

    /**
     * Count surat expiring today
     */
    public function countSuratExpiringToday(User $user): int
    {
        $rtIds = $this->rtIdsForUser($user);

        return VSuratExpiringSoon::expiringToday()
            ->whereIn('rt_id', $rtIds)
            ->count();
    }

    /**
     * Get data inconsistencies detected by the DB view, filtered by territory.
     *
     * @param User $user Authenticated user for territory scoping
     * @param int $limit Max results
     * @return Collection<int, VDataInconsistency>
     */
    public function getDataInconsistencies(User $user, int $limit = 20): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return VDataInconsistency::query()
            ->whereExists(function ($sub) use ($rtIds) {
                $sub->select(DB::raw(1))
                    ->from('penduduks as p')
                    ->whereColumn('p.id', 'v_data_inconsistency.penduduk_id')
                    ->whereIn('p.rt_id', $rtIds);
            })
            ->limit($limit)
            ->get();
    }

    /**
     * Count total data inconsistencies within territory
     */
    public function countDataInconsistencies(User $user): int
    {
        $rtIds = $this->rtIdsForUser($user);

        return VDataInconsistency::query()
            ->whereExists(function ($sub) use ($rtIds) {
                $sub->select(DB::raw(1))
                    ->from('penduduks as p')
                    ->whereColumn('p.id', 'v_data_inconsistency.penduduk_id')
                    ->whereIn('p.rt_id', $rtIds);
            })
            ->count();
    }

    /**
     * Get KK summary with member counts (from v_kk_with_members), filtered by territory.
     *
     * @param User $user Authenticated user for territory scoping
     * @param int $limit Max results
     * @return Collection<int, VKkWithMembers>
     */
    public function getKkSummary(User $user, int $limit = 10): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return VKkWithMembers::query()
            ->whereIn('kk_id', KartuKeluarga::whereIn('rt_id', $rtIds)->pluck('id'))
            ->limit($limit)
            ->get();
    }

    /**
     * Get active penduduk list (from v_penduduk_aktif), filtered by territory.
     *
     * @param User $user Authenticated user for territory scoping
     * @param int $limit Max results
     * @return Collection<int, VPendudukAktif>
     */
    public function getActivePenduduk(User $user, int $limit = 10): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return VPendudukAktif::query()
            ->whereIn('rt_id', $rtIds)
            ->limit($limit)
            ->get();
    }

    /**
     * Count active penduduk from DB view within territory
     */
    public function countActivePenduduk(User $user): int
    {
        $rtIds = $this->rtIdsForUser($user);

        return VPendudukAktif::query()
            ->whereIn('rt_id', $rtIds)
            ->count();
    }

    /**
     * Get event statistics by type within territory
     */
    public function getEventStats(User $user): array
    {
        $rtIds = $this->rtIdsForUser($user);

        $stats = Event::select('event_type_code', DB::raw('count(*) as total'))
            ->where('status_data', 'VERIFIED')
            ->whereYear('event_date', date('Y'))
            ->whereIn('rt_id', $rtIds)
            ->groupBy('event_type_code')
            ->pluck('total', 'event_type_code')
            ->map(fn($total) => (int) $total)
            ->toArray();

        return [
            'kelahiran' => $stats['KELAHIRAN'] ?? 0,
            'kematian' => $stats['KEMATIAN'] ?? 0,
            'pindah' => $stats['PINDAH'] ?? 0,
            'datang' => $stats['DATANG'] ?? 0,
            'total' => array_sum($stats),
        ];
    }

    /**
     * Get pending events (waiting approval) within territory
     */
    public function getPendingEvents(User $user, int $limit = 5): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return Event::where('status_data', 'DRAFT')
            ->whereIn('rt_id', $rtIds)
            ->with(['penduduk', 'eventType'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent events (verified) within territory
     */
    public function getRecentEvents(User $user, int $limit = 5): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return Event::where('status_data', 'VERIFIED')
            ->whereIn('rt_id', $rtIds)
            ->with(['penduduk', 'eventType', 'createdBy'])
            ->orderBy('verified_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get surat statistics within territory
     */
    public function getSuratStats(User $user): array
    {
        $rtIds = $this->rtIdsForUser($user);
        $currentMonth = date('Y-m');

        return [
            'total_aktif' => SuratTerbit::where('status', 'AKTIF')
                ->whereIn('rt_id', $rtIds)
                ->count(),
            'bulan_ini' => SuratTerbit::where('status', 'AKTIF')
                ->whereIn('rt_id', $rtIds)
                ->whereRaw("DATE_FORMAT(tanggal_terbit, '%Y-%m') = ?", [$currentMonth])
                ->count(),
            'akan_kadaluarsa' => $this->getExpiringSurat($user)->count(),
            'kadaluarsa_hari_ini' => $this->countSuratExpiringToday($user),
        ];
    }

    /**
     * Get recent surat terbit within territory
     */
    public function getRecentSurat(User $user, int $limit = 5): Collection
    {
        $rtIds = $this->rtIdsForUser($user);

        return SuratTerbit::where('status', 'AKTIF')
            ->whereIn('rt_id', $rtIds)
            ->with(['penduduk', 'jenisSurat'])
            ->orderBy('tanggal_terbit', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get chart data for penduduk by age group within territory
     */
    public function getPendudukByAgeGroup(User $user): array
    {
        $rtIds = $this->rtIdsForUser($user);

        $result = DB::table('v_penduduk_aktif')
            ->whereIn('rt_id', $rtIds)
            ->select(DB::raw("
                CASE
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 1 THEN '<1'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) BETWEEN 1 AND 6 THEN '1-6'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) BETWEEN 7 AND 12 THEN '7-12'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) BETWEEN 13 AND 19 THEN '13-19'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) BETWEEN 20 AND 30 THEN '20-30'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) BETWEEN 31 AND 40 THEN '31-40'
                    WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) BETWEEN 41 AND 60 THEN '41-60'
                    ELSE '>60'
                END as age_group,
                COUNT(*) as total
            "))
            ->groupBy('age_group')
            ->orderByRaw("FIELD(age_group, '<1', '1-6', '7-12', '13-19', '20-30', '31-40', '41-60', '>60')")
            ->get();

        return [
            'labels' => $result->pluck('age_group')->toArray(),
            'data' => $result->pluck('total')->toArray(),
        ];
    }

    /**
     * Get chart data for events by month (current year) within territory
     */
    public function getEventsByMonth(User $user): array
    {
        $rtIds = $this->rtIdsForUser($user);

        $result = Event::selectRaw('MONTH(event_date) as month, COUNT(*) as total')
            ->where('status_data', 'VERIFIED')
            ->whereYear('event_date', date('Y'))
            ->whereIn('rt_id', $rtIds)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data = array_fill(0, 12, 0);

        foreach ($result as $row) {
            $monthIndex = ((int) $row['month']) - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $data[$monthIndex] = (int) $row['total'];
            }
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    /**
     * Assemble all dashboard widget data in one call, scoped to user territory.
     *
     * @param User $user Authenticated user for territory scoping
     * @return array{
     *     expiring_surat: Collection,
     *     critical_surat_count: int,
     *     data_inconsistencies: Collection,
     *     inconsistency_count: int,
     *     active_penduduk_count: int,
     *     event_stats: array,
     *     pending_events: Collection,
     *     recent_events: Collection,
     *     surat_stats: array,
     *     recent_surat: Collection,
     *     penduduk_by_age: array,
     *     events_by_month: array
     * }
     */
    public function getDashboardWidgets(User $user): array
    {
        $canViewSurat = Gate::allows('viewAny', SuratTerbit::class);

        return [
            'expiring_surat'        => $canViewSurat ? $this->getExpiringSurat($user) : collect(),
            'critical_surat_count'  => $canViewSurat ? $this->getCriticallyExpiringSurat($user)->count() : 0,
            'data_inconsistencies'  => $this->getDataInconsistencies($user),
            'inconsistency_count'   => $this->countDataInconsistencies($user),
            'active_penduduk_count' => $this->countActivePenduduk($user),
            'event_stats'           => $this->getEventStats($user),
            'pending_events'        => $this->getPendingEvents($user),
            'recent_events'         => $this->getRecentEvents($user),
            'surat_stats'           => $canViewSurat ? $this->getSuratStats($user) : ['total_aktif' => 0, 'bulan_ini' => 0, 'akan_kadaluarsa' => 0, 'kadaluarsa_hari_ini' => 0],
            'recent_surat'          => $canViewSurat ? $this->getRecentSurat($user) : collect(),
            'penduduk_by_age'       => $this->getPendudukByAgeGroup($user),
            'events_by_month'       => $this->getEventsByMonth($user),
        ];
    }
}
