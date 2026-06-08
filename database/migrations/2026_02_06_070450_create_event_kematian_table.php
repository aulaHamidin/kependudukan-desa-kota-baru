<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_kematian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->onDelete('cascade')->onUpdate('cascade');
            $table->string('tempat_meninggal', 200);
            $table->time('jam_meninggal')->nullable();
            $table->string('sebab_kematian', 100)->nullable();
            $table->string('penyakit', 200)->nullable();
            $table->text('keterangan_kematian')->nullable();
            $table->foreignId('pelapor_id')->nullable()->constrained('penduduks')->onDelete('set null')->onUpdate('cascade');
            $table->string('nama_pelapor', 200)->nullable();
            $table->string('hubungan_pelapor_code', 20)->nullable();

            // Indexes
            $table->index('pelapor_id');
            $table->index('tempat_meninggal');
            
            // Foreign keys
            $table->foreign('hubungan_pelapor_code')->references('kode')->on('hubungan_keluarga')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_kematian');
    }
};