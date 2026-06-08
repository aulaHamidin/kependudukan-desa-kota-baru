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
 * @property int $desa_id
 * @property string $nomor_rw
 * @property string|null $nama_ketua
 * @property string|null $no_hp_ketua
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Rw extends Model
{
    use Auditable;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rws';

    protected $fillable = [
        'desa_id',
        'nomor_rw',
        'nama_ketua',
        'no_hp_ketua',
    ];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function rts(): HasMany
    {
        return $this->hasMany(Rt::class, 'rw_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'rw_id');
    }

    public function suratTerbit(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'rw_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rw_id');
    }
}
