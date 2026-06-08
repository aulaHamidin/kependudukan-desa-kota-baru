<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penduduks', function (Blueprint $table) {
            $table->id();
            $table->char('nik', 16)->unique();
            $table->string('nama_lengkap', 200);
            $table->char('jenis_kelamin', 1);
            $table->string('tempat_lahir', 100);
            $table->date('tgl_lahir');

            // Parent references (will add FK later via separate migration)
            $table->unsignedBigInteger('ayah_id')->nullable();
            $table->unsignedBigInteger('ibu_id')->nullable();
            $table->string('nama_ayah', 200)->nullable();
            $table->string('nama_ibu', 200)->nullable();

            // Master data references
            $table->string('agama_id', 10);
            $table->string('pendidikan_id', 20)->nullable();
            $table->string('pekerjaan_id', 10)->nullable();
            $table->foreignId('pendapatan_range_id')->nullable()->constrained('pendapatan_ranges')->onDelete('set null')->onUpdate('cascade');
            $table->string('golongan_darah_id', 5)->nullable();

            // Additional info
            $table->string('kewarganegaraan', 3)->default('WNI');
            $table->string('no_paspor', 50)->nullable();
            $table->string('status_perkawinan', 20);
            $table->string('no_hp', 20)->nullable()->unique();
            $table->string('email', 100)->nullable()->unique();

            // Location and status
            $table->foreignId('rt_id')->nullable()->constrained('rts')->onDelete('set null')->onUpdate('cascade');
            $table->string('status_kependudukan_code', 20);
            $table->unsignedBigInteger('current_event_id')->nullable();
            $table->date('tanggal_status');

            // Version control
            $table->integer('data_version')->default(1);

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Single column indexes
            $table->index('nama_lengkap');
            $table->index('tgl_lahir');
            $table->index('jenis_kelamin');
            $table->index('deleted_at');
            $table->index('rt_id');
            $table->index('ayah_id');
            $table->index('ibu_id');
            $table->index('agama_id');
            $table->index('pendidikan_id');
            $table->index('pekerjaan_id');
            $table->index('status_kependudukan_code');
            $table->index('current_event_id');

            // Composite indexes for complex queries
            $table->index(['rt_id', 'status_kependudukan_code'], 'idx_penduduk_rt_status');
            $table->index(['status_kependudukan_code', 'tanggal_status'], 'idx_penduduk_status_tanggal');
            $table->index(['jenis_kelamin', 'status_kependudukan_code'], 'idx_penduduk_jk_status');
            $table->index(['nama_lengkap', 'tgl_lahir'], 'idx_penduduk_nama_tgl_lahir');

            // Index for demographic queries
            $table->index(['rt_id', 'jenis_kelamin', 'tgl_lahir'], 'idx_penduduk_demografi');

            // Foreign key constraints for master data
            $table->foreign('agama_id')->references('kode')->on('agamas')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('pendidikan_id')->references('kode')->on('pendidikans')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('pekerjaan_id')->references('kode')->on('pekerjaans')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('golongan_darah_id')->references('kode')->on('golongan_darahs')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('status_kependudukan_code')->references('kode')->on('status_kependudukan')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE penduduks DROP CONSTRAINT IF EXISTS chk_penduduk_jk');
        DB::statement('ALTER TABLE penduduks DROP CONSTRAINT IF EXISTS chk_penduduk_wn');
        DB::statement('ALTER TABLE penduduks DROP CONSTRAINT IF EXISTS chk_penduduk_tgl_lahir');
        DB::statement('ALTER TABLE penduduks DROP CONSTRAINT IF EXISTS chk_penduduk_status_kawin');
        Schema::dropIfExists('penduduks');
    }
};
