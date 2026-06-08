<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desa_id')->constrained('desas')->onDelete('restrict')->onUpdate('cascade');
            $table->string('nomor_rw', 5);
            $table->string('nama_ketua', 200)->nullable();
            $table->string('no_hp_ketua', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('desa_id');
            $table->index('deleted_at');
            $table->index('nama_ketua');

            // Unique: Prevent duplicate nomor RW in same desa
            $table->unique(['desa_id', 'nomor_rw', 'deleted_at'], 'idx_rws_desa_nomor');
        });

        // Note: Format validation moved to application level
        // Using RequestValidation rules instead of database constraints
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rws DROP CONSTRAINT IF EXISTS chk_rws_nomor_format');
        Schema::dropIfExists('rws');
    }
};
