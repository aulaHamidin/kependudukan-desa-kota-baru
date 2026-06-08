<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $event_id
 * @property string $alamat_tujuan
 * @property string|null $rt_tujuan
 * @property string|null $rw_tujuan
 * @property string|null $desa_tujuan
 * @property string|null $kecamatan_tujuan
 * @property string|null $kabupaten_tujuan
 * @property string|null $provinsi_tujuan
 * @property string|null $kode_pos_tujuan
 * @property string|null $alasan_pindah
 * @property string|null $keterangan_alasan
 * @property string|null $jenis_kepindahan
 * @property \Illuminate\Support\Carbon $tanggal_pindah
 * @property bool $was_kepala
 * @property int|null $pengganti_id
 */
class EventPindah extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'event_pindah';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'alamat_tujuan',
        'rt_tujuan',
        'rw_tujuan',
        'desa_tujuan',
        'kecamatan_tujuan',
        'kabupaten_tujuan',
        'provinsi_tujuan',
        'kode_pos_tujuan',
        'alasan_pindah',
        'keterangan_alasan',
        'jenis_kepindahan',
        'tanggal_pindah',
        'was_kepala',
        'pengganti_id',
    ];

    protected $casts = [
        'tanggal_pindah' => 'date',
        'was_kepala' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function pengganti(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'pengganti_id');
    }
}