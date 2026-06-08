<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role
 * @property int|null $desa_id
 * @property int|null $rw_id
 * @property int|null $rt_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property \Illuminate\Support\Carbon|null $password_changed_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Auditable;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'nik',
        'email',
        'password',
        'role',
        'desa_id',
        'rw_id',
        'rt_id',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'password_changed_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
        'nik' => 'string',
        'desa_id' => 'integer',
        'rw_id' => 'integer',
        'rt_id' => 'integer',
    ];

    public function getRoleNames(): string
    {
        return $this->role_label;
    }

    public function getRoleLabelAttribute(): string
    {
        $labels = [
            'super_admin' => 'Super Admin',
            'admin_desa' => 'Admin Desa',
            'admin_rw' => 'Admin RW',
            'admin_rt' => 'Admin RT',
            'viewer' => 'Viewer',
        ];

        return $labels[(string) $this->role] ?? ucwords(str_replace('_', ' ', (string) $this->role));
    }

    public function getLastLoginAtLabelAttribute(): string
    {
        return $this->last_login_at?->format('d/m/Y H:i') ?? '-';
    }

    public function hasRole(string $role): bool
    {
        return strtolower((string) $this->role) === strtolower($role);
    }

    /**
     * Check if user has any of the specified roles
     *
     * @param array<string> $roles Array of role names to check
     * @return bool True if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array(strtolower((string) $this->role), array_map('strtolower', $roles), true);
    }


    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class, 'rw_id');
    }

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class, 'rt_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function eventsCreated(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function eventsVoided(): HasMany
    {
        return $this->hasMany(Event::class, 'voided_by');
    }

    public function eventsVerified(): HasMany
    {
        return $this->hasMany(Event::class, 'verified_by');
    }

    public function suratTerbitCreated(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'created_by');
    }

    public function suratTerbitCancelled(): HasMany
    {
        return $this->hasMany(SuratTerbit::class, 'cancelled_by');
    }

    public function kartuKeluargaCreated(): HasMany
    {
        return $this->hasMany(KartuKeluarga::class, 'created_by');
    }

    public function kartuKeluargaUpdated(): HasMany
    {
        return $this->hasMany(KartuKeluarga::class, 'updated_by');
    }

    public function pendudukCreated(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'created_by');
    }

    public function pendudukUpdated(): HasMany
    {
        return $this->hasMany(Penduduk::class, 'updated_by');
    }

    public function kkMemberCreated(): HasMany
    {
        return $this->hasMany(KkMember::class, 'created_by');
    }
}
