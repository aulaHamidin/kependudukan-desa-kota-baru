<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agamas', function (Blueprint $table) {
            $table->string('kode', 10)->primary();
            $table->string('nama', 50)->unique();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);

            // Indexes
            $table->index('is_active');
            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agamas');
    }
};