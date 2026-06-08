<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add pengganti_id column to event_pindah table for kepala keluarga replacement tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_pindah', function (Blueprint $table) {
            $table->foreignId('pengganti_id')
                ->nullable()
                ->after('was_kepala')
                ->constrained('penduduks')
                ->onDelete('set null')
                ->onUpdate('cascade')
                ->comment('Replacement kepala keluarga when current kepala moves');
        });
    }

    public function down(): void
    {
        Schema::table('event_pindah', function (Blueprint $table) {
            $table->dropForeign(['pengganti_id']);
            $table->dropColumn('pengganti_id');
        });
    }
};
