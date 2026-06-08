<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_pindah', function (Blueprint $table) {
            // Track apakah penduduk yang pindah adalah kepala keluarga
            // CRITICAL untuk void rollback - info ini hilang saat is_kepala_keluarga di-set false
            $table->boolean('was_kepala')->default(false)->after('tanggal_pindah');
        });
    }

    public function down(): void
    {
        Schema::table('event_pindah', function (Blueprint $table) {
            $table->dropColumn('was_kepala');
        });
    }
};