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
 * @property string|null $deskripsi
 * @property bool $is_active
 */
class StatusKependudukan extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'status_kependudukan';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function penduduks(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'status_kependudukan_code', 'kode');
    }
}
