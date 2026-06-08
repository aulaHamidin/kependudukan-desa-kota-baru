<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $aksi
 * @property string $model
 * @property int|null $model_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 */
class AuditLog extends Model
{
    use HasFactory;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_PASSWORD_RESET = 'password_reset';
    public const ACTION_IMPORT = 'import';

    public const ACTION_LABELS = [
        self::ACTION_LOGIN => 'Login',
        self::ACTION_LOGOUT => 'Logout',
        self::ACTION_PASSWORD_RESET => 'Reset Password',
        self::ACTION_IMPORT => 'Import',
        self::ACTION_CREATE => 'Create',
        self::ACTION_UPDATE => 'Update',
        self::ACTION_DELETE => 'Delete',
    ];

    public const MODEL_LABELS = [
        'User' => 'User',
        'Penduduk' => 'Penduduk',
        'KartuKeluarga' => 'Kartu Keluarga',
        'Desa' => 'Desa',
        'Rw' => 'RW',
        'Rt' => 'RT',
        'Agama' => 'Agama',
        'Pendidikan' => 'Pendidikan',
        'Pekerjaan' => 'Pekerjaan',
        'GolonganDarah' => 'Golongan Darah',
        'PendapatanRange' => 'Range Pendapatan',
        'StatusKependudukan' => 'Status Kependudukan',
        'HubunganKeluarga' => 'Hubungan Keluarga',
        'EventType' => 'Tipe Event',
        'Event' => 'Event',
        'EventKelahiran' => 'Kelahiran',
        'EventKematian' => 'Kematian',
        'EventPindah' => 'Pindah',
        'EventDatang' => 'Datang',
        'JenisSurat' => 'Jenis Surat',
        'SuratSequence' => 'Sequence Surat',
        'SuratTerbit' => 'Surat Terbit',
    ];

    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'actor_type',
        'actor_id',
        'aksi',
        'model',
        'model_id',
        'old_values',
        'new_values',
        'role_snapshot',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'actor_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getAksiLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->aksi] ?? Str::title((string) $this->aksi);
    }

    public function getModelLabelAttribute(): string
    {
        return self::MODEL_LABELS[$this->model] ?? (string) $this->model;
    }

    public function getCreatedAtLabelAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getUserLabelAttribute(): string
    {
        if ($this->actor_type === 'system') {
            return 'System';
        }

        return $this->user?->name ?? '-';
    }

    public function getOldValuesPrettyAttribute(): string
    {
        if (empty($this->old_values)) {
            return '-';
        }

        return json_encode($this->old_values, JSON_PRETTY_PRINT) ?: '-';
    }

    public function getNewValuesPrettyAttribute(): string
    {
        if (empty($this->new_values)) {
            return '-';
        }

        return json_encode($this->new_values, JSON_PRETTY_PRINT) ?: '-';
    }
}
