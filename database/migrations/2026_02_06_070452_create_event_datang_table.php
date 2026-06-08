<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_datang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->onDelete('cascade')->onUpdate('cascade');
            $table->text('alamat_asal')->nullable();
            $table->string('rt_asal', 10)->nullable();
            $table->string('rw_asal', 10)->nullable();
            $table->string('desa_asal', 100)->nullable();
            $table->string('kecamatan_asal', 100)->nullable();
            $table->string('kabupaten_asal', 100)->nullable();
            $table->string('provinsi_asal', 100)->nullable();
            $table->string('alasan_datang', 100)->nullable();
            $table->text('keterangan_alasan')->nullable();
            $table->string('jenis_kedatangan', 50)->nullable();
            $table->foreignId('kk_tujuan_id')->nullable()->constrained('kartu_keluargas')->onDelete('set null')->onUpdate('cascade');
            $table->string('no_surat_pindah', 50)->nullable();
            $table->date('tanggal_surat_pindah')->nullable();

            // Indexes
            $table->index(['kabupaten_asal', 'kecamatan_asal'], 'idx_event_datang_asal');
            $table->index('kk_tujuan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_datang');
    }
};