<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TerritoryAware;
use App\Traits\Auditable;
use App\Traits\HasTerritory as TraitsHasTerritory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $event_type_code
 * @property int|null $penduduk_id
 * @property \Illuminate\Support\Carbon $event_date
 * @property string|null $keterangan
 * @property int $rt_id
 * @property int $rw_id
 * @property int $desa_id
 * @property int|null $kk_id
 * @property string $status_data
 * @property string|null $void_reason
 * @property \Illuminate\Support\Carbon|null $void_at
 * @property int|null $voided_by
 * @property int|null $verified_by
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Event extends Model implements TerritoryAware
{
    use Auditable;
    use HasFactory;
    use TraitsHasTerritory;

    /**
     * CE1+CE7: Status yang dianggap "aktif" (belum final).
     * flow DRAFT → VERIFIED → VOID.
     */
    public const ACTIVE_STATUSES = ['DRAFT'];

    protected $table = 'events';

    protected $fillable = [
        'event_type_code',
        'penduduk_id',
        'event_date',
        'keterangan',
        'rt_id',
        'rw_id',
        'desa_id',
        'kk_id',
        'status_data',
        'void_reason',
        'void_at',
        'voided_by',
        'verified_by',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'void_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_code', 'kode');
    }

    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'penduduk_id');
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function kartuKeluarga(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class, 'kk_id');
    }

    /**
     * Override HasTerritory untuk performa optimal
     * Event punya kolom desa_id, rw_id, rt_id langsung — tidak perlu whereHas
     */
    public function applyTerritoryFilter(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin_rt')) {
            if ($user->rt_id === null) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where($this->getTable() . '.rt_id', $user->rt_id);
        }

        if ($user->hasRole('admin_rw')) {
            if ($user->rw_id === null) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where($this->getTable() . '.rw_id', $user->rw_id);
        }

        if ($user->hasRole('admin_desa')) {
            if ($user->desa_id === null) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where($this->getTable() . '.desa_id', $user->desa_id);
        }

        if ($user->hasRole('viewer')) {
            // Viewer selalu via rt_id → rw_id → desa_id
            $viewerDesaId = null;
            if ($user->rt_id !== null) {
                $rt = $user->rt;
                if ($rt !== null && $rt->rw !== null) {
                    $viewerDesaId = $rt->rw->desa_id;
                }
            }

            if ($viewerDesaId === null) {
                return $query->whereRaw('1 = 0');
            }
            // Viewer hanya boleh melihat event VERIFIED
            return $query->where($this->getTable() . '.desa_id', $viewerDesaId)
                         ->where($this->getTable() . '.status_data', 'VERIFIED');
        }

        return $query->whereRaw('1 = 0');
    }
    
    public static function pendudukHasPendingEvent(int $pendudukId): bool
    {
        return static::where('penduduk_id', $pendudukId)
            ->whereIn('status_data', static::ACTIVE_STATUSES)
            ->exists();
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function eventKelahiran(): HasOne
    {
        return $this->hasOne(EventKelahiran::class, 'event_id');
    }

    public function eventKematian(): HasOne
    {
        return $this->hasOne(EventKematian::class, 'event_id');
    }

    public function eventPindah(): HasOne
    {
        return $this->hasOne(EventPindah::class, 'event_id');
    }

    public function eventDatang(): HasOne
    {
        return $this->hasOne(EventDatang::class, 'event_id');
    }
}