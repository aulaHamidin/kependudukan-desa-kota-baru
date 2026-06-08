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
 * @property bool $require_details
 * @property bool $is_active
 */
class EventType extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'event_types';

    protected $primaryKey = 'kode';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
        'require_details',
        'is_active',
    ];

    protected $casts = [
        'require_details' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_type_code', 'kode');
    }
}
