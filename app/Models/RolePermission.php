<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 * @property \Illuminate\Support\Carbon $created_at
 */
class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_permissions';

    const UPDATED_AT = null;

    protected $fillable = [
        'role_id',
        'permission_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
