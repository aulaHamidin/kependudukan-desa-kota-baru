<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_desa', 20)->unique();
            $table->string('nama', 100);
            $table->string('kecamatan', 100);
            $table->string('kabupaten', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 10);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('kode_desa');
            $table->index('nama');
            $table->index('kabupaten');
            $table->index('deleted_at');
            $table->index(['provinsi', 'kabupaten', 'kecamatan'], 'idx_desas_regional');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desas');
    }
};