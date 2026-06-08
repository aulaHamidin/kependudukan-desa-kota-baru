<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Catat timestamp setiap kali scheduler berjalan.
 *
 * Dijalankan setiap menit oleh Kernel::schedule(). Jika heartbeat
 * terakhir lebih dari N menit yang lalu, scheduler kemungkinan mati.
 *
 * Dashboard atau monitoring bisa memeriksa via:
 *   Cache::get('scheduler_last_heartbeat')
 */
class SchedulerHeartbeatCommand extends Command
{
    protected $signature = 'scheduler:heartbeat';

    protected $description = 'Catat timestamp terakhir scheduler aktif (untuk health monitoring)';

    /**
     * TTL cache lebih lama dari interval check agar tidak false-positive.
     * Default: 1 jam (3600 detik). Jika scheduler mati > 1 jam, cache expired.
     */
    public const CACHE_TTL = 3600;

    public const CACHE_KEY = 'scheduler_last_heartbeat';

    public function handle(): int
    {
        Cache::put(self::CACHE_KEY, now()->toIso8601String(), ttl: self::CACHE_TTL);

        return self::SUCCESS;
    }

    /**
     * Helper statis untuk memeriksa apakah scheduler masih aktif.
     *
     * @param int $thresholdMinutes Batas menit scheduler dianggap mati
     */
    public static function isAlive(int $thresholdMinutes = 10): bool
    {
        $lastBeat = Cache::get(self::CACHE_KEY);

        if ($lastBeat === null) {
            return false;
        }

        return now()->diffInMinutes(\Carbon\Carbon::parse($lastBeat)) <= $thresholdMinutes;
    }
}
