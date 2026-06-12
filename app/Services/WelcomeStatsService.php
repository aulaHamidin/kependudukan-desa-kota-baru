<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WelcomeStatsService
{
    private const AGE_LABELS = ['0-4', '5-14', '15-24', '25-34', '35-44', '45-54', '55-64', '65+'];

    private const MONTH_LABELS = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    public function getPublicStats(): array
    {
        return [
            'pendudukStats' => $this->getPendudukStats(),
            'kkStats' => $this->getKkStats(),
            'eventStats' => $this->getEventStats(),
            'suratStats' => $this->getSuratStats(),
            'pendudukByAge' => $this->getPendudukByAge(),
            'eventsByMonth' => $this->getEventsByMonth(),
        ];
    }

    public function emptyStats(): array
    {
        return [
            'pendudukStats' => ['total' => 0, 'aktif' => 0, 'pindah' => 0, 'meninggal' => 0, 'laki_laki' => 0, 'perempuan' => 0],
            'kkStats' => ['total' => 0, 'aktif' => 0, 'non_aktif' => 0],
            'eventStats' => ['kelahiran' => 0, 'kematian' => 0, 'pindah' => 0, 'datang' => 0, 'total' => 0],
            'suratStats' => ['total_aktif' => 0, 'bulan_ini' => 0, 'akan_kadaluarsa' => 0, 'kadaluarsa_hari_ini' => 0],
            'pendudukByAge' => ['labels' => self::AGE_LABELS, 'data' => array_fill(0, count(self::AGE_LABELS), 0)],
            'eventsByMonth' => ['labels' => array_values(self::MONTH_LABELS), 'data' => array_fill(0, 12, 0)],
        ];
    }

    public function getPendudukStats(): array
    {
        $result = DB::table('penduduks')
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'AKTIF' THEN 1 ELSE 0 END) as aktif")
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'PINDAH' THEN 1 ELSE 0 END) as pindah")
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'MENINGGAL' THEN 1 ELSE 0 END) as meninggal")
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'AKTIF' AND jenis_kelamin = 'L' THEN 1 ELSE 0 END) as laki_laki")
            ->selectRaw("SUM(CASE WHEN status_kependudukan_code = 'AKTIF' AND jenis_kelamin = 'P' THEN 1 ELSE 0 END) as perempuan")
            ->first();

        return [
            'total' => (int) ($result->total ?? 0),
            'aktif' => (int) ($result->aktif ?? 0),
            'pindah' => (int) ($result->pindah ?? 0),
            'meninggal' => (int) ($result->meninggal ?? 0),
            'laki_laki' => (int) ($result->laki_laki ?? 0),
            'perempuan' => (int) ($result->perempuan ?? 0),
        ];
    }

    public function getKkStats(): array
    {
        $result = DB::table('kartu_keluargas')
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status_kk = 'AKTIF' THEN 1 ELSE 0 END) as aktif")
            ->selectRaw("SUM(CASE WHEN status_kk = 'NON_AKTIF' THEN 1 ELSE 0 END) as non_aktif")
            ->first();

        return [
            'total' => (int) ($result->total ?? 0),
            'aktif' => (int) ($result->aktif ?? 0),
            'non_aktif' => (int) ($result->non_aktif ?? 0),
        ];
    }

    public function getEventStats(): array
    {
        $stats = DB::table('events')
            ->where('status_data', 'VERIFIED')
            ->whereBetween('event_date', $this->currentYearRange())
            ->select('event_type_code')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('event_type_code')
            ->pluck('total', 'event_type_code');

        return [
            'kelahiran' => (int) ($stats['KELAHIRAN'] ?? 0),
            'kematian' => (int) ($stats['KEMATIAN'] ?? 0),
            'pindah' => (int) ($stats['PINDAH'] ?? 0),
            'datang' => (int) ($stats['DATANG'] ?? 0),
            'total' => (int) $stats->sum(fn ($total) => (int) $total),
        ];
    }

    public function getSuratStats(): array
    {
        $today = today()->toDateString();
        $endOfExpiryWindow = today()->addDays(30)->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $result = DB::table('surat_terbit')
            ->whereNull('deleted_at')
            ->selectRaw("SUM(CASE WHEN status = 'AKTIF' THEN 1 ELSE 0 END) as total_aktif")
            ->selectRaw(
                "SUM(CASE WHEN status = 'AKTIF' AND tanggal_terbit BETWEEN ? AND ? THEN 1 ELSE 0 END) as bulan_ini",
                [$startOfMonth, $endOfMonth]
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'AKTIF' AND tanggal_kadaluarsa BETWEEN ? AND ? THEN 1 ELSE 0 END) as akan_kadaluarsa",
                [$today, $endOfExpiryWindow]
            )
            ->selectRaw(
                "SUM(CASE WHEN status = 'AKTIF' AND tanggal_kadaluarsa = ? THEN 1 ELSE 0 END) as kadaluarsa_hari_ini",
                [$today]
            )
            ->first();

        return [
            'total_aktif' => (int) ($result->total_aktif ?? 0),
            'bulan_ini' => (int) ($result->bulan_ini ?? 0),
            'akan_kadaluarsa' => (int) ($result->akan_kadaluarsa ?? 0),
            'kadaluarsa_hari_ini' => (int) ($result->kadaluarsa_hari_ini ?? 0),
        ];
    }

    public function getPendudukByAge(): array
    {
        $groups = array_fill_keys(self::AGE_LABELS, 0);

        DB::table('penduduks')
            ->whereNull('deleted_at')
            ->where('status_kependudukan_code', 'AKTIF')
            ->whereNotNull('tgl_lahir')
            ->select('tgl_lahir')
            ->cursor()
            ->each(function (object $row) use (&$groups): void {
                $age = max(0, Carbon::parse($row->tgl_lahir)->age);
                $groups[$this->ageGroupFor($age)]++;
            });

        return [
            'labels' => array_keys($groups),
            'data' => array_values($groups),
        ];
    }

    public function getEventsByMonth(): array
    {
        $data = array_fill(1, 12, 0);

        DB::table('events')
            ->where('status_data', 'VERIFIED')
            ->whereBetween('event_date', $this->currentYearRange())
            ->select('event_date')
            ->cursor()
            ->each(function (object $row) use (&$data): void {
                $month = Carbon::parse($row->event_date)->month;
                $data[$month]++;
            });

        return [
            'labels' => array_values(self::MONTH_LABELS),
            'data' => array_values($data),
        ];
    }

    private function ageGroupFor(int $age): string
    {
        return match (true) {
            $age <= 4 => '0-4',
            $age <= 14 => '5-14',
            $age <= 24 => '15-24',
            $age <= 34 => '25-34',
            $age <= 44 => '35-44',
            $age <= 54 => '45-54',
            $age <= 64 => '55-64',
            default => '65+',
        };
    }

    private function currentYearRange(): array
    {
        return [
            now()->startOfYear()->toDateString(),
            now()->endOfYear()->toDateString(),
        ];
    }
}
