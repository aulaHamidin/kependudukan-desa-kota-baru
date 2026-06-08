<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $kode_desa
 * @property string $nama
 * @property string $kecamatan
 * @property string $kabupaten
 * @property string $provinsi
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Desa extends Model
{
    use Auditable;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'desas';

    protected $fillable = [
        'kode_desa',
        'nama',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kode_pos',
    ];

    public function rws(): HasMany
    {
        return $this->hasMany(Rw::class, 'desa_id');
    }

    public function rts(): HasManyThrough
    {
        return $this->hasManyThrough(Rt::class, Rw::class, 'desa_id', 'rw_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'desa_id');
    }

    public function suratTerbit(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'desa_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'desa_id');
    }
}
