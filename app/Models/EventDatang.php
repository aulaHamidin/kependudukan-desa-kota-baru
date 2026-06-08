<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventDatang extends Model
{
    use Auditable;
    use HasFactory;

    public $timestamps = false;

    protected $table = 'event_datang';

    protected $fillable = [
        'event_id',
        'alamat_asal',
        'rt_asal',
        'rw_asal',
        'desa_asal',
        'kecamatan_asal',
        'kabupaten_asal',
        'provinsi_asal',
        'alasan_datang',
        'keterangan_alasan',
        'jenis_kedatangan',
        'kk_tujuan_id',
        'no_surat_pindah',
        'tanggal_surat_pindah',
        'restored_from_id',
    ];

    protected $casts = [
        'tanggal_surat_pindah' => 'date',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function kartuKeluargaTujuan(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class, 'kk_tujuan_id');
    }

    /**
     * Penduduk yang di-restore (jika event ini melakukan restore soft-deleted penduduk)
     * NULL = pendatang baru murni
     * NOT NULL = penduduk hasil restore dari soft-delete
     */
    public function restoredFrom(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'restored_from_id')
            ->withTrashed(); // Include soft-deleted karena bisa sudah dihapus lagi
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Cek apakah event datang ini adalah hasil restore penduduk soft-deleted
     */
    public function isRestored(): bool
    {
        return $this->restored_from_id !== null;
    }
}
