<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Global visible surat number sequence.
 *
 * This table backs the Word-style number format:
 * 145 / 001 / 01.2009 / 2026
 */
class SuratNomorSequence extends Model
{
    use HasFactory;

    protected $table = 'surat_nomor_sequences';

    public $timestamps = false;

    protected $fillable = [
        'kode_surat',
        'tahun',
        'sequence_number',
        'last_generated_at',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'sequence_number' => 'integer',
        'last_generated_at' => 'datetime',
    ];

    public static function getSequenceKey(string $kodeSurat, int $tahun): string
    {
        return sprintf('%s_%d', $kodeSurat, $tahun);
    }

    public function scopeForPeriod(Builder $query, string $kodeSurat, int $tahun): Builder
    {
        return $query->where('kode_surat', $kodeSurat)
            ->where('tahun', $tahun);
    }
}
