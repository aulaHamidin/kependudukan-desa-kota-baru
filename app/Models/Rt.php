<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $rw_id
 * @property string $nomor_rt
 * @property string|null $nama_ketua
 * @property string|null $no_hp_ketua
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Rt extends Model
{
    use Auditable;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rts';

    protected $fillable = [
        'rw_id',
        'nomor_rt',
        'nama_ketua',
        'no_hp_ketua',
    ];

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function kartuKeluargas(): HasMany
    {
        return $this->hasMany(KartuKeluarga::class, 'rt_id');
    }

    public function penduduks(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'rt_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'rt_id');
    }

    public function suratTerbit(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'rt_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rt_id');
    }
}
