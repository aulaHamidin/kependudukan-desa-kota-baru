<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusKelahiran as StatusKelahiranEnum;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property string $nama_bayi
 * @property string $jenis_kelamin
 * @property int|null $ayah_id
 * @property int|null $ibu_id
 * @property string|null $nama_ayah
 * @property string|null $nama_ibu
 * @property string $tempat_lahir
 * @property string|null $jam_lahir
 * @property int $anak_ke
 * @property float|null $berat_badan_kg
 * @property float|null $panjang_badan_cm
 * @property string|null $penolong_kelahiran
 * @property string|null $nama_penolong
 * @property int|null $kk_tujuan_id
 * @property \App\Enums\StatusKelahiran $status_kelahiran
 */
class EventKelahiran extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'event_kelahiran';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'nama_bayi',
        'jenis_kelamin',
        'status_kelahiran',
        'ayah_id',
        'ibu_id',
        'nama_ayah',
        'nama_ibu',
        'tempat_lahir',
        'jam_lahir',
        'anak_ke',
        'berat_badan_kg',
        'panjang_badan_cm',
        'penolong_kelahiran',
        'nama_penolong',
        'kk_tujuan_id',
    ];

    protected $casts = [
        'status_kelahiran' => StatusKelahiranEnum::class,
        'anak_ke' => 'integer',
        'berat_badan_kg' => 'decimal:2',
        'panjang_badan_cm' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'ayah_id');
    }

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'ibu_id');
    }

    public function kkTujuan(): BelongsTo
    {
        return $this->belongsTo(KartuKeluarga::class, 'kk_tujuan_id');
    }

    // Tambah di bagian bawah class (setelah relationships)

    /**
     * Check apakah bayi lahir hidup
     */
    public function isBayiHidup(): bool
    {
        return $this->status_kelahiran === StatusKelahiranEnum::HIDUP;
    }

    /**
     * Check apakah bayi lahir mati
     */
    public function isBayiMati(): bool
    {
        return $this->status_kelahiran === StatusKelahiranEnum::MATI;
    }

    /**
     * Get label status kelahiran untuk display
     */
    public function getStatusKelahiranLabel(): string
    {
        if (!$this->status_kelahiran instanceof StatusKelahiranEnum) {
            return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">-</span>';
        }
        
        return match ($this->status_kelahiran) {
            StatusKelahiranEnum::HIDUP => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Hidup</span>',
            StatusKelahiranEnum::MATI => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Lahir Mati</span>',
        };
    }
}
