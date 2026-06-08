<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_kelahiran', function (Blueprint $table) {
            // Tambah kolom status_kelahiran
            $table->enum('status_kelahiran', ['HIDUP', 'MATI'])
                  ->default('HIDUP')
                  ->after('jenis_kelamin')
                  ->comment('Status kelahiran bayi: HIDUP atau MATI (stillbirth)');
        });

        // Tidak perlu tambah agama_id di event_kelahiran
        // Karena agama_id sudah ada di tabel penduduks
        // Akan diisi via form saat create dan disimpan langsung ke penduduk bayi
    }

    public function down(): void
    {
        Schema::table('event_kelahiran', function (Blueprint $table) {
            $table->dropColumn('status_kelahiran');
        });
    }
};