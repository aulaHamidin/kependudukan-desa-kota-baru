<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property float|null $min_value
 * @property float|null $max_value
 * @property string $label
 * @property int $urutan
 * @property bool $is_active
 */
class PendapatanRange extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'pendapatan_ranges';

    public $timestamps = false;

    protected $fillable = [
        'min_value',
        'max_value',
        'label',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
        'urutan' => 'integer',
        'is_active' => 'boolean',
    ];

    public function penduduks(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'pendapatan_range_id');
    }
}
