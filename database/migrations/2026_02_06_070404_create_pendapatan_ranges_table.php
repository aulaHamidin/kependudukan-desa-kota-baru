<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendapatan_ranges', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_value', 15, 2)->nullable();
            $table->decimal('max_value', 15, 2)->nullable();
            $table->string('label', 100)->unique();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);

            // Indexes
            $table->index('is_active');
            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatan_ranges');
    }
};