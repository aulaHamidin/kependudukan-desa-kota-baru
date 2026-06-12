<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_nomor_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('kode_surat', 30);
            $table->integer('tahun');
            $table->integer('sequence_number')->default(0);
            $table->timestamp('last_generated_at')->nullable();

            $table->unique(['kode_surat', 'tahun'], 'idx_surat_nomor_sequences_unique');
            $table->index('tahun', 'idx_surat_nomor_sequences_tahun');
        });

        DB::statement('ALTER TABLE surat_nomor_sequences ADD CONSTRAINT chk_surat_nomor_sequences_tahun CHECK (tahun BETWEEN 2000 AND 2100)');
        DB::statement('ALTER TABLE surat_nomor_sequences ADD CONSTRAINT chk_surat_nomor_sequences_number CHECK (sequence_number >= 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE surat_nomor_sequences DROP CONSTRAINT IF EXISTS chk_surat_nomor_sequences_tahun');
        DB::statement('ALTER TABLE surat_nomor_sequences DROP CONSTRAINT IF EXISTS chk_surat_nomor_sequences_number');

        Schema::dropIfExists('surat_nomor_sequences');
    }
};
