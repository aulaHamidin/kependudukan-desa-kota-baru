<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_terbit', function (Blueprint $table) {
            // -------------------------
            // PRIMARY
            // -------------------------
            $table->id();

            // -------------------------
            // IDENTITAS SURAT
            // -------------------------
            $table->string('nomor_surat', 50)->unique();
            $table->string('jenis_surat_kode', 20);
            $table->date('tanggal_terbit');
            $table->text('keperluan');
            $table->text('keterangan_tambahan')->nullable();

            /**
             * Dynamic fields per jenis surat — disimpan sebagai JSON.
             * Contoh: { "nama_tujuan": "PT ABC", "keperluan_khusus": "..." }
             */
            $table->json('data_surat')->nullable()->comment('Dynamic fields per jenis surat type in JSON');

            // -------------------------
            // PDF
            // -------------------------
            $table->string('file_path', 255)->nullable()->comment('Generated PDF path');
            $table->enum('pdf_status', ['PROCESSING', 'READY', 'FAILED'])
                  ->default('PROCESSING')
                  ->comment('PDF generation status for queue');

            // -------------------------
            // RELASI PENDUDUK & KK
            // -------------------------
            $table->foreignId('penduduk_id')
                  ->constrained('penduduks')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            /**
             * kk_id: NOT NULL — semua penduduk pasti memiliki KK terdaftar.
             * Revisi: dari nullable → not null karena sistem memastikan
             * penduduk memiliki KK aktif sebelum surat bisa diterbitkan.
             */
            $table->foreignId('kk_id')
                  ->constrained('kartu_keluargas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // -------------------------
            // TERRITORY (denormalisasi)
            // -------------------------
            /**
             * rt_id, rw_id, desa_id sengaja di-denormalisasi dari penduduk
             * untuk performa query filter by territory di policy & reporting.
             * Nilai diisi otomatis dari penduduk->rt_id saat create (lihat SuratTerbitService).
             */
            $table->foreignId('rt_id')
                  ->constrained('rts')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('rw_id')
                  ->constrained('rws')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->foreignId('desa_id')
                  ->constrained('desas')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // -------------------------
            // MASA BERLAKU
            // -------------------------
            /**
             * masa_berlaku_hari: diambil dari jenis_surat saat create.
             * tanggal_kadaluarsa: dihitung otomatis via observer/service
             *   = tanggal_terbit + masa_berlaku_hari
             * Jika masa_berlaku_hari null → surat tidak memiliki masa berlaku.
             */
            $table->unsignedInteger('masa_berlaku_hari')->nullable()
                  ->comment('Diambil dari jenis_surat.masa_berlaku_hari saat terbit');
            $table->date('tanggal_kadaluarsa')->nullable()
                  ->comment('Computed: tanggal_terbit + masa_berlaku_hari via observer');

            // -------------------------
            // STATUS
            // -------------------------
            $table->enum('status', ['AKTIF', 'KADALUARSA', 'BATAL'])->default('AKTIF');
            $table->text('alasan_batal')->nullable();

            // -------------------------
            // AUDIT TRAIL — CANCEL
            // -------------------------
            $table->foreignId('cancelled_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->timestamp('cancelled_at')->nullable();

            // -------------------------
            // AUDIT TRAIL — CREATE / UPDATE
            // -------------------------
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            /**
             * updated_by: wajib ada karena update() ALLOW untuk admin_desa.
             * Tracks siapa yang terakhir mengubah surat.
             */
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // -------------------------
            // TIMESTAMPS & SOFT DELETE
            // -------------------------
            $table->timestamps();
            $table->softDeletes();

            // -------------------------
            // FOREIGN KEY — JENIS SURAT
            // -------------------------
            $table->foreign('jenis_surat_kode')
                  ->references('kode')
                  ->on('jenis_surat')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });

        // -------------------------
        // INDEXES — SINGLE COLUMN
        // -------------------------
        Schema::table('surat_terbit', function (Blueprint $table) {
            $table->index('jenis_surat_kode',   'idx_surat_jenis_kode');
            $table->index('penduduk_id',         'idx_surat_penduduk');
            $table->index('kk_id',               'idx_surat_kk');
            $table->index('tanggal_terbit',      'idx_surat_tgl_terbit');
            $table->index('tanggal_kadaluarsa',  'idx_surat_tgl_exp');
            $table->index('status',              'idx_surat_status');
            $table->index('pdf_status',          'idx_surat_pdf_status');   // queue monitoring
            $table->index('rt_id',               'idx_surat_rt');
            $table->index('rw_id',               'idx_surat_rw');
            $table->index('desa_id',             'idx_surat_desa');
            $table->index('created_by',          'idx_surat_created_by');
            $table->index('updated_by',          'idx_surat_updated_by');
            $table->index('deleted_at',          'idx_surat_deleted_at');   // soft delete query

            // -------------------------
            // INDEXES — COMPOSITE
            // -------------------------

            // Filter surat per jenis dalam rentang tanggal
            $table->index(['jenis_surat_kode', 'tanggal_terbit'],   'idx_surat_jenis_tanggal');

            // Cek riwayat surat penduduk per jenis (duplikasi, kuota)
            $table->index(['penduduk_id', 'jenis_surat_kode'],       'idx_surat_penduduk_jenis');

            // Dashboard admin_desa: surat per desa dalam rentang tanggal
            $table->index(['desa_id', 'tanggal_terbit'],             'idx_surat_desa_tanggal');

            // Policy territory check: surat aktif per desa
            $table->index(['desa_id', 'status'],                     'idx_surat_desa_status');

            // Queue: ambil surat yang PROCESSING untuk di-generate PDF
            $table->index(['pdf_status', 'created_at'],              'idx_surat_queue');

            // Monitoring kadaluarsa: surat aktif yang akan/sudah expired
            $table->index(['status', 'tanggal_kadaluarsa'],          'idx_surat_exp_monitor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_terbit');
    }
};