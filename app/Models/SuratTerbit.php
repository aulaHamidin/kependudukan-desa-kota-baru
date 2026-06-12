<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TerritoryAware;
use App\Traits\{Auditable, HasTerritory};
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $nomor_surat
 * @property string $jenis_surat_kode
 * @property int $penduduk_id
 * @property \Illuminate\Support\Carbon $tanggal_terbit
 * @property string $keperluan
 * @property string|null $keterangan_tambahan
 * @property array|null $data_surat
 * @property string|null $file_path
 * @property string|null $pdf_status
 * @property int $rt_id
 * @property int $rw_id
 * @property int $desa_id
 * @property int|null $kk_id
 * @property int|null $masa_berlaku_hari
 * @property \Illuminate\Support\Carbon|null $tanggal_kadaluarsa
 * @property string $status
 * @property string|null $alasan_batal
 * @property int|null $cancelled_by
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class SuratTerbit extends Model implements TerritoryAware
{
    use Auditable;
    use HasFactory;
    use HasTerritory;  // ✅ Territory scope integration
    use SoftDeletes;

    protected $table = 'surat_terbit';

    /**
     * ✅ Territory columns for HasTerritory trait
     */
    protected $territoryColumns = ['desa_id', 'rw_id', 'rt_id'];

    protected $fillable = [
        'nomor_surat',
        'jenis_surat_kode',
        'penduduk_id',
        'tanggal_terbit',
        'keperluan',
        'keterangan_tambahan',
        'data_surat',
        'file_path',
        'pdf_status',        // ✅ Queue status field
        'rt_id',
        'rw_id',
        'desa_id',
        'kk_id',
        'masa_berlaku_hari',
        'tanggal_kadaluarsa',
        'pdf_generated_at',
        'status',
        'alasan_batal',
        'cancelled_by',
        'cancelled_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data_surat' => 'array',
        'tanggal_terbit' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'cancelled_at' => 'datetime',
        'pdf_generated_at' => 'datetime',
        'masa_berlaku_hari' => 'integer',
    ];

    /**
     * ✅ NO global $with - performance compliance (audit requirement)
     * All relationships use lazy loading by default 
     */

    /**
     * Helper: Check if surat is active
     */
    public function isAktif(): bool
    {
        return $this->status === 'AKTIF';
    }

    /**
     * Helper: Check if surat is cancelled
     */
    public function isBatal(): bool
    {
        return $this->status === 'BATAL';
    }

    /**
     * Helper: Check if surat is expired
     */
    public function isExpired(): bool
    {
        return $this->tanggal_kadaluarsa && $this->tanggal_kadaluarsa->isPast();
    }

    /**
     * Helper: Check PDF generation status
     */
    public function isPdfReady(): bool
    {
        return $this->pdf_status === 'READY' && $this->file_path;
    }

    /**
     * Helper: Check if PDF is processing
     */
    public function isPdfProcessing(): bool
    {
        return $this->pdf_status === 'PROCESSING';
    }

    /**
     * Scope: Only active surat
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'AKTIF');
    }

    /**
     * Scope: Only cancelled surat
     */
    public function scopeBatal(Builder $query): Builder
    {
        return $query->where('status', 'BATAL');
    }

    /**
     * Scope: Surat yang benar-benar aktif dan belum kadaluarsa secara aktual.
     *
     * Gunakan scope ini sebagai fallback ketika scheduler mungkin belum sempat
     * mengupdate kolom `status` dari AKTIF ke KADALUARSA. Filter berdasarkan
     * `tanggal_kadaluarsa` aktual di DB, bukan hanya kolom `status`.
     *
     * Contoh: SuratTerbit::benarBenarAktif()->get()
     */
    public function scopeBenarBenarAktif(Builder $query): Builder
    {
        return $query->where('status', 'AKTIF')
            ->where(function (Builder $q) {
                $q->whereNull('tanggal_kadaluarsa')
                  ->orWhere('tanggal_kadaluarsa', '>=', today());
            });
    }

    /**
     * Custom territory filter for surat documents
     * Overrides default HasTerritory behavior for surat-specific logic
     */
    public function applyTerritoryFilter(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin_rt')) {
            if ($user->rt_id === null) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('rt_id', $user->rt_id);
        }

        if ($user->hasRole('admin_rw')) {
            if ($user->rw_id === null) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('rw_id', $user->rw_id);
        }

        if ($user->hasRole('admin_desa')) {
            if ($user->desa_id === null) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('desa_id', $user->desa_id);
        }

        if ($user->hasRole('viewer')) {
            // Viewer selalu via rt_id → rw_id → desa_id
            $viewerDesaId = null;
            if ($user->rt_id !== null) {
                $rt = $user->rt;
                if ($rt !== null && $rt->rw !== null) {
                    $viewerDesaId = $rt->rw->desa_id;
                }
            }

            if ($viewerDesaId === null) {
                return $query->whereRaw('1 = 0');
            }
            return $query->where('desa_id', $viewerDesaId);
        }

        // Default: no access
        return $query->whereRaw('1 = 0');
    }

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_kode', 'kode');
    }

    public function penduduk(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'penduduk_id');
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function kartuKeluarga(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class, 'kk_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
