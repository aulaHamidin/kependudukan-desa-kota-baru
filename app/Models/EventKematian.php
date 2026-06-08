<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $event_id
 * @property string      $tempat_meninggal
 * @property string|null $jam_meninggal
 * @property string|null $sebab_kematian
 * @property string|null $penyakit
 * @property string|null $keterangan_kematian
 * @property bool        $was_kepala           Snapshot: apakah almarhum kepala saat event dibuat
 * @property int|null    $pengganti_id         Pengganti kepala yang ditunjuk saat event dibuat
 * @property int|null    $pelapor_id
 * @property string|null $nama_pelapor
 * @property string|null $hubungan_pelapor_code
 */
class EventKematian extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'event_kematian';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'tempat_meninggal',
        'jam_meninggal',
        'sebab_kematian',
        'penyakit',
        'keterangan_kematian',
        'was_kepala',
        'pengganti_id',
        'pelapor_id',
        'nama_pelapor',
        'hubungan_pelapor_code',
    ];

    protected $casts = [
        'was_kepala' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'pelapor_id');
    }

    public function pengganti(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'pengganti_id');
    }

    public function hubunganPelapor(): BelongsTo
    {
        return $this->belongsTo(HubunganKeluarga::class, 'hubungan_pelapor_code', 'kode');
    }
}