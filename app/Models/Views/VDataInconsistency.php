<?php

declare(strict_types=1);

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class VDataInconsistency extends Model
{
    protected $table = 'v_data_inconsistency';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    // SECURITY FIX: View model should not allow mass assignment
    protected $fillable = []; // Read-only database view
}
