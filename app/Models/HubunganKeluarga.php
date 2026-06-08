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
 * @property bool $is_active
 */
class HubunganKeluarga extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'hubungan_keluarga';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kkMembers(): HasMany
    {
        return $this->hasMany(KkMember::class, 'hubungan_keluarga_code', 'kode');
    }

    public function eventKematianReports(): HasMany
    {
        return $this->hasMany(EventKematian::class, 'hubungan_pelapor_code', 'kode');
    }
}
