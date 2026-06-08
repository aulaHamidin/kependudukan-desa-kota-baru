<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('golongan_darahs', function (Blueprint $table) {
            $table->string('kode', 5)->primary();
            $table->string('nama', 10);
            $table->string('rhesus', 10)->nullable();
            $table->boolean('is_active')->default(true);

            // Indexes
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('golongan_darahs');
    }
};