<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }
}
