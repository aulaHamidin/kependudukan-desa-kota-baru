<?php

declare(strict_types=1);

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;

class VPendudukAktif extends Model
{
    protected $table = 'v_penduduk_aktif';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = false;
    public $timestamps = false;

    // SECURITY FIX: View model should not allow mass assignment
    protected $fillable = []; // Read-only database view
}
