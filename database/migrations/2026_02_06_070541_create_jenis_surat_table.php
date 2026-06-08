<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_surat', function (Blueprint $table) {
            $table->string('kode', 20)->primary();
            $table->string('nama', 100)->unique();
            $table->text('deskripsi')->nullable();
            $table->string('template_filename', 255)->nullable()->comment('Path to template file');
            $table->string('prefix_nomor', 10)->comment('Prefix for numbering, e.g., SKD, SKTM');
            $table->string('format_nomor', 100)->default('{sequence}/{prefix}/{month_roman}/{year}');
            $table->integer('masa_berlaku_hari')->nullable()->comment('Default validity in days, NULL = no expiry');
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('prefix_nomor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_surat');
    }
};