<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->string('kode', 20)->primary();
            $table->string('nama', 100)->unique();
            $table->text('deskripsi')->nullable();
            $table->boolean('require_details')->default(false);
            $table->boolean('is_active')->default(true);

            // Indexes
            $table->index('is_active');
            $table->index('require_details');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_types');
    }
};