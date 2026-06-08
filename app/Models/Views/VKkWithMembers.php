<?php

declare(strict_types=1);

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class VKkWithMembers extends Model
{
    protected $table = 'v_kk_with_members';
    protected $primaryKey = 'kk_id';
    protected $keyType = 'int';
    public $incrementing = false;
    public $timestamps = false;

    // SECURITY FIX: View model should not allow mass assignment
    protected $fillable = []; // Read-only database view
}
