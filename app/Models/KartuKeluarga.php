<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TerritoryAware;
use App\Traits\Auditable;
use App\Traits\HasTerritory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $no_kk
 * @property string $alamat
 * @property int $rt_id
 * @property string $status_kk
 * @property \Illuminate\Support\Carbon $tanggal_terbentuk
 * @property int $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class KartuKeluarga extends Model implements TerritoryAware
{
    use Auditable;
    use HasFactory;
    use HasTerritory;
    use SoftDeletes;

    // PERFORMANCE FIX: Disabled global eager loading for 12K+ dataset  
    // Use explicit ->with() when RT/RW data is actually needed
    // protected $with = [
    //     'rt.rw',
    // ];

    protected $table = 'kartu_keluargas';

    protected $fillable = [
        'no_kk',
        'alamat',
        'rt_id',
        'status_kk',
        'tanggal_terbentuk',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'no_kk'             => 'string',
        'tanggal_terbentuk' => 'date',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function kkMembers(): HasMany
    {
        return $this->hasMany(KkMember::class, 'kartu_keluarga_id');
    }

    /**
     * Relasi khusus kepala keluarga aktif
     *
     * Gunakan ini untuk eager load di index/list:
     *   ->with(['kepalaKeluarga.penduduk:id,nama_lengkap'])
     *
     * Di view:
     *   $kk->kepalaKeluarga?->penduduk?->nama_lengkap
     *
     * Lebih clean dan semantik dibanding filter kkMembers di setiap query.
     */
    public function kepalaKeluarga(): HasOne
    {
        return $this->hasOne(KkMember::class, 'kartu_keluarga_id')
            ->where('is_kepala_keluarga', true)
            ->where('status', 'AKTIF');
    }

    public function suratTerbit(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'kk_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'kk_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
