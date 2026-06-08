<?php

declare(strict_types=1);

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * View Model for v_surat_expiring_soon
 * 
 * @property int $id
 * @property string $nomor_surat  
 * @property string $jenis_surat_nama
 * @property string $penduduk_nama
 * @property \Illuminate\Support\Carbon $tanggal_kadaluarsa
 * @property int $days_remaining
 */
class VSuratExpiringSoon extends Model
{
    protected $table = 'v_surat_expiring_soon';
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    /**
     * ✅ No auto-increment for view
     */
    public $incrementing = false;

    /**
     * ✅ No timestamps for view
     */
    public $timestamps = false;

    /**
     * ✅ SECURITY COMPLIANCE: View model is read-only
     * No mass assignment allowed for database views
     */
    protected $fillable = []; // Read-only database view

    protected $casts = [
        'tanggal_kadaluarsa' => 'date',
        'days_remaining' => 'integer',
    ];

    /**
     * Helper: Check if surat is expiring today
     */
    public function isExpiringToday(): bool
    {
        return $this->days_remaining === 0;
    }

    /**
     * Helper: Check if surat is critically expiring (< 3 days)
     */
    public function isCriticallyExpiring(): bool
    {
        return $this->days_remaining <= 3;
    }

    /**
     * Scope: Only critically expiring surat
     */
    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('days_remaining', '<=', 3);
    }

    /**
     * Scope: Expiring today
     */
    public function scopeExpiringToday(Builder $query): Builder
    {
        return $query->where('days_remaining', 0);
    }
}
