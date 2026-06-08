<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $jenis_surat_kode
 * @property int $tahun
 * @property int $bulan
 * @property int $sequence_number
 * @property \Illuminate\Support\Carbon|null $last_generated_at
 */
class SuratSequence extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'surat_sequence';

    /**
     * ✅ No timestamps - this table doesn't need created_at/updated_at
     */
    public $timestamps = false;

    protected $fillable = [
        'jenis_surat_kode',
        'tahun',
        'bulan',
        'sequence_number',
        'last_generated_at',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
        'sequence_number' => 'integer',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Helper: Get current sequence key for race-condition safety
     */
    public static function getSequenceKey(string $jenisKode, int $tahun, int $bulan): string
    {
        return sprintf('%s_%d_%02d', $jenisKode, $tahun, $bulan);
    }

    /**
     * Helper: Check if sequence exists for given period
     */
    public static function existsForPeriod(string $jenisKode, int $tahun, int $bulan): bool
    {
        return self::where('jenis_surat_kode', $jenisKode)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->exists();
    }

    /**
     * Scope: For specific period
     */
    public function scopeForPeriod(Builder $query, string $jenisKode, int $tahun, int $bulan): Builder
    {
        return $query->where('jenis_surat_kode', $jenisKode)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan);
    }

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_kode', 'kode');
    }
}
