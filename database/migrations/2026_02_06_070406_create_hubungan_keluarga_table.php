<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hubungan_keluarga', function (Blueprint $table) {
            $table->string('kode', 20)->primary();
            $table->string('nama', 50)->unique();
            $table->boolean('is_active')->default(true);

            // Indexes
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hubungan_keluarga');
    }
};