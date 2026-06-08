<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_pindah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->onDelete('cascade')->onUpdate('cascade');
            $table->text('alamat_tujuan');
            $table->string('rt_tujuan', 10)->nullable();
            $table->string('rw_tujuan', 10)->nullable();
            $table->string('desa_tujuan', 100)->nullable();
            $table->string('kecamatan_tujuan', 100)->nullable();
            $table->string('kabupaten_tujuan', 100)->nullable();
            $table->string('provinsi_tujuan', 100)->nullable();
            $table->string('kode_pos_tujuan', 10)->nullable();
            $table->string('alasan_pindah', 100)->nullable();
            $table->text('keterangan_alasan')->nullable();
            $table->string('jenis_kepindahan', 50)->nullable();
            $table->date('tanggal_pindah');

            // Indexes
            $table->index(['kabupaten_tujuan', 'kecamatan_tujuan'], 'idx_event_pindah_tujuan');
            $table->index('tanggal_pindah');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_pindah');
    }
};