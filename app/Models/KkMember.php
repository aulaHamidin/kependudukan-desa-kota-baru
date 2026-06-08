<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $kartu_keluarga_id
 * @property int $penduduk_id
 * @property string $hubungan_keluarga_code
 * @property bool $is_kepala_keluarga
 * @property \Illuminate\Support\Carbon $tanggal_masuk
 * @property \Illuminate\Support\Carbon|null $tanggal_keluar
 * @property string $status
 * @property int|null $kk_asal_id
 * @property int|null $event_keluar_id
 * @property string|null $alasan_keluar
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class KkMember extends Model
{
    use Auditable; // ADDED: Audit trail untuk perubahan membership
    use HasFactory;

    // PERFORMANCE FIX: Disabled global eager loading for 12K+ dataset
    // Use explicit ->with() when relations are actually needed
    // protected $with = [
    //     'kartuKeluarga.rt.rw',
    // ];

    protected $table = 'kk_members';

    protected $fillable = [
        'kartu_keluarga_id',
        'penduduk_id',
        'hubungan_keluarga_code',
        'is_kepala_keluarga',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'kk_asal_id',
        'event_keluar_id',
        'alasan_keluar',
        'created_by',
    ];

    protected $casts = [
        'is_kepala_keluarga' => 'boolean',
        'tanggal_masuk'      => 'date',
        'tanggal_keluar'     => 'date',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function kartuKeluarga(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class, 'kartu_keluarga_id');
    }

    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'penduduk_id');
    }

    public function hubunganKeluarga(): BelongsTo
    {
        return $this->belongsTo(HubunganKeluarga::class, 'hubungan_keluarga_code', 'kode');
    }

    public function kkAsal(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class, 'kk_asal_id');
    }

    public function eventKeluar(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_keluar_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
