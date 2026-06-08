<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kk_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kartu_keluarga_id')->constrained('kartu_keluargas')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('penduduk_id')->constrained('penduduks')->onDelete('restrict')->onUpdate('cascade');
            $table->string('hubungan_keluarga_code', 20);
            $table->boolean('is_kepala_keluarga')->default(false);
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->string('status', 20)->default('AKTIF');
            $table->unsignedBigInteger('kk_asal_id')->nullable();
            $table->unsignedBigInteger('event_keluar_id')->nullable();
            $table->text('alasan_keluar')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();

            // Indexes
            $table->index('kartu_keluarga_id');
            $table->index('penduduk_id');
            $table->index('status');
            $table->index('is_kepala_keluarga');
            $table->index('event_keluar_id');

            // Composite indexes
            $table->index(['kartu_keluarga_id', 'status'], 'idx_kk_member_kk_status');
            $table->index(['penduduk_id', 'status'], 'idx_kk_member_penduduk_status');

            // Index to detect duplicate kepala keluarga
            $table->index(['penduduk_id', 'is_kepala_keluarga', 'status'], 'idx_kk_member_kepala_check');

            // Foreign keys
            $table->foreign('hubungan_keluarga_code')->references('kode')->on('hubungan_keluarga')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('kk_asal_id')->references('id')->on('kartu_keluargas')->onDelete('set null')->onUpdate('cascade');
        });

        // Note: Unique indexes with WHERE clause and additional constraints 
        // have been moved to application level (MariaDB compatibility)
    }

    public function down(): void
    {
        Schema::dropIfExists('kk_members');
    }
};
