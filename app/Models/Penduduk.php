<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TerritoryAware;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTerritory;
use App\Traits\Maskable;

/**
 * @property int $id
 * @property string $nik
 * @property string $nama_lengkap
 * @property string $jenis_kelamin
 * @property string $tempat_lahir
 * @property \Illuminate\Support\Carbon $tgl_lahir
 * @property int|null $ayah_id
 * @property int|null $ibu_id
 * @property string|null $nama_ayah
 * @property string|null $nama_ibu
 * @property string $agama_id
 * @property string $pendidikan_id
 * @property string $pekerjaan_id
 * @property int|null $pendapatan_range_id
 * @property string|null $golongan_darah_id
 * @property string $kewarganegaraan
 * @property string|null $no_paspor
 * @property string $status_perkawinan
 * @property string|null $no_hp
 * @property string|null $email
 * @property int|null $rt_id
 * @property string $status_kependudukan_code
 * @property int|null $current_event_id
 * @property \Illuminate\Support\Carbon $tanggal_status
 * @property int $data_version
 * @property int $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Penduduk extends Model implements TerritoryAware
{
    use Auditable;
    use HasFactory;
    use SoftDeletes;
    use HasTerritory;
    use Maskable;

    protected $table = 'penduduks';

    protected $fillable = [
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tgl_lahir',
        'ayah_id',
        'ibu_id',
        'nama_ayah',
        'nama_ibu',
        'agama_id',
        'pendidikan_id',
        'pekerjaan_id',
        'pendapatan_range_id',
        'golongan_darah_id',
        'kewarganegaraan',
        'no_paspor',
        'status_perkawinan',
        'no_hp',
        'email',
        'rt_id',
        'status_kependudukan_code',
        'current_event_id',
        'tanggal_status',
        'data_version',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'tanggal_status' => 'date',
        'data_version' => 'integer',
    ];

    public function getIsInKkAttribute(): bool
    {
        return $this->kkMembers()->where('status', 'AKTIF')->exists();
    }

    public function activeKk(): ?KkMember
    {
        return $this->kkMembers()->where('status', 'AKTIF')->first();
    }

    public function agama(): BelongsTo
    {
        return $this->belongsTo(Agama::class, 'agama_id', 'kode');
    }

    public function pendidikan(): BelongsTo
    {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_id', 'kode');
    }

    public function pekerjaan(): BelongsTo
    {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_id', 'kode');
    }

    public function pendapatanRange(): BelongsTo
    {
        return $this->belongsTo(PendapatanRange::class, 'pendapatan_range_id');
    }

    public function golonganDarah(): BelongsTo
    {
        return $this->belongsTo(GolonganDarah::class, 'golongan_darah_id', 'kode');
    }

    public function statusKependudukan(): BelongsTo
    {
        return $this->belongsTo(StatusKependudukan::class, 'status_kependudukan_code', 'kode');
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function currentEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'current_event_id');
    }

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'ayah_id');
    }

    public function ibu(): BelongsTo
    {
        return $this->belongsTo(Penduduk::class, 'ibu_id');
    }

    public function anakDariAyah(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'ayah_id');
    }

    public function anakDariIbu(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'ibu_id');
    }

    public function kkMembers(): HasMany
    {
        return $this->hasMany(KkMember::class, 'penduduk_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'penduduk_id');
    }

    /**
     * Check if this penduduk is kepala keluarga in any active KK membership
     */
    public function isKepalaKeluarga(): bool
    {
        return $this->kkMembers()
            ->where('status', 'AKTIF')
            ->where('is_kepala_keluarga', true)
            ->exists();
    }

    public function suratTerbit(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'penduduk_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Update NIK penduduk. Validasi unik/format harus dilakukan di layer request/service.
     */
    public function updateNik(string $nik): bool
    {
        $this->nik = $nik;

        return $this->save();
    }
}
