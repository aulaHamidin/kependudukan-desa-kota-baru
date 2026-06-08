<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hybrid Template System Migration
 *
 * Mengubah sistem template dari per-file ke kategori + JSON configuration.
 *
 * Pendekatan:
 * - template_category: memilih file Blade mana yang digunakan (keterangan, pengantar, izin, dll)
 * - template_sections: JSON konfigurasi untuk fields, intro, body, signature, dll
 *
 * Keuntungan:
 * - Template Blade tetap di filesystem (version-controlled, aman dari RCE)
 * - Konfigurasi per jenis surat bisa diubah via UI tanpa deploy
 * - 95% jenis surat akan masuk kategori yang sudah ada
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            // Tambah kolom baru
            $table->string('template_category', 50)
                ->default('keterangan')
                ->after('template_path')
                ->comment('Kategori template: keterangan, pengantar, izin, pernyataan, rekomendasi, internal');

            $table->json('template_sections')
                ->nullable()
                ->after('template_category')
                ->comment('JSON config: data_fields, intro, body, signature, dll');

            // Index untuk performa
            $table->index('template_category');
        });

        // Drop kolom lama yang tidak dipakai lagi
        Schema::table('jenis_surat', function (Blueprint $table) {
            if (Schema::hasColumn('jenis_surat', 'template_filename')) {
                $table->dropColumn('template_filename');
            }
            if (Schema::hasColumn('jenis_surat', 'template_path')) {
                $table->dropColumn('template_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            // Restore kolom lama
            $table->string('template_path', 255)->nullable()->after('deskripsi');
            $table->string('template_filename', 255)->nullable()->after('template_path');

            // Drop kolom baru
            $table->dropIndex(['template_category']);
            $table->dropColumn(['template_category', 'template_sections']);
        });
    }
};
