<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $kode
 * @property string $nama
 * @property int $urutan
 * @property bool $is_active
 */
class Pendidikan extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'pendidikans';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kode',
        'nama',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function penduduks(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'pendidikan_id', 'kode');
    }
}
