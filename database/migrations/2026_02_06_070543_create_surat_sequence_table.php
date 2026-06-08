<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_sequence', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_surat_kode', 20);
            $table->integer('tahun');
            $table->integer('bulan');
            $table->integer('sequence_number')->default(0);
            $table->timestamp('last_generated_at')->nullable();

            // CRITICAL: Unique constraint to prevent race condition
            $table->unique(['jenis_surat_kode', 'tahun', 'bulan'], 'idx_surat_sequence_unique');

            // Indexes
            $table->index(['tahun', 'bulan'], 'idx_surat_sequence_tahun_bulan');
            $table->index('jenis_surat_kode');
            
            // Foreign key
            $table->foreign('jenis_surat_kode')->references('kode')->on('jenis_surat')->onDelete('restrict')->onUpdate('cascade');
        });

        // Check constraints
        DB::statement('ALTER TABLE surat_sequence ADD CONSTRAINT chk_surat_sequence_tahun CHECK (tahun BETWEEN 2000 AND 2100)');
        DB::statement('ALTER TABLE surat_sequence ADD CONSTRAINT chk_surat_sequence_bulan CHECK (bulan BETWEEN 1 AND 12)');
        DB::statement('ALTER TABLE surat_sequence ADD CONSTRAINT chk_surat_sequence_number CHECK (sequence_number >= 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE surat_sequence DROP CONSTRAINT IF EXISTS chk_surat_sequence_tahun');
        DB::statement('ALTER TABLE surat_sequence DROP CONSTRAINT IF EXISTS chk_surat_sequence_bulan');
        DB::statement('ALTER TABLE surat_sequence DROP CONSTRAINT IF EXISTS chk_surat_sequence_number');
        Schema::dropIfExists('surat_sequence');
    }
};