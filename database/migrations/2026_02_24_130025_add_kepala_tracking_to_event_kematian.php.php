<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add kepala tracking columns to event_kematian table.
 *
 * was_kepala  : snapshot apakah almarhum adalah kepala keluarga saat event dibuat.
 *               Digunakan saat delete DRAFT agar restore is_kepala_keluarga presisi,
 *               bukan selalu di-set true.
 *
 * pengganti_id: ID penduduk yang ditunjuk sebagai pengganti kepala keluarga.
 *               Digunakan saat delete DRAFT agar rollback hanya menyentuh pengganti
 *               yang spesifik, bukan semua kepala aktif di KK tersebut.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_kematian', function (Blueprint $table) {
            $table->boolean('was_kepala')
                ->default(false)
                ->after('keterangan_kematian')
                ->comment('Snapshot: apakah almarhum kepala keluarga saat event dibuat');

            $table->foreignId('pengganti_id')
                ->nullable()
                ->after('was_kepala')
                ->constrained('penduduks')
                ->onDelete('set null')
                ->onUpdate('cascade')
                ->comment('Pengganti kepala keluarga yang ditunjuk saat event kematian dibuat');
        });
    }

    public function down(): void
    {
        Schema::table('event_kematian', function (Blueprint $table) {
            $table->dropForeign(['pengganti_id']);
            $table->dropColumn(['was_kepala', 'pengganti_id']);
        });
    }
};