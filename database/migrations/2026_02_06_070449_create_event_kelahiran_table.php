<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_kelahiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->onDelete('cascade')->onUpdate('cascade');
            $table->string('nama_bayi', 200);
            $table->char('jenis_kelamin', 1);
            $table->foreignId('ayah_id')->nullable()->constrained('penduduks')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('ibu_id')->nullable()->constrained('penduduks')->onDelete('set null')->onUpdate('cascade');
            $table->string('nama_ayah', 200)->nullable();
            $table->string('nama_ibu', 200)->nullable();
            $table->string('tempat_lahir', 100);
            $table->time('jam_lahir')->nullable();
            $table->integer('anak_ke')->default(1);
            $table->decimal('berat_badan_kg', 4, 2)->nullable();
            $table->decimal('panjang_badan_cm', 5, 2)->nullable();
            $table->string('penolong_kelahiran', 50)->nullable();
            $table->string('nama_penolong', 200)->nullable();
            $table->foreignId('kk_tujuan_id')->nullable()->constrained('kartu_keluargas')->onDelete('set null')->onUpdate('cascade');

            // Indexes
            $table->index('ayah_id');
            $table->index('ibu_id');
            $table->index('nama_bayi');
            $table->index('jenis_kelamin');
        });

        // Check constraints
        DB::statement('ALTER TABLE event_kelahiran ADD CONSTRAINT chk_event_kelahiran_jk CHECK (jenis_kelamin IN ("L", "P"))');
        DB::statement('ALTER TABLE event_kelahiran ADD CONSTRAINT chk_event_kelahiran_anak_ke CHECK (anak_ke >= 1)');
        DB::statement('ALTER TABLE event_kelahiran ADD CONSTRAINT chk_event_kelahiran_berat CHECK (berat_badan_kg IS NULL OR berat_badan_kg BETWEEN 0.5 AND 10)');
        DB::statement('ALTER TABLE event_kelahiran ADD CONSTRAINT chk_event_kelahiran_panjang CHECK (panjang_badan_cm IS NULL OR panjang_badan_cm BETWEEN 20 AND 80)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE event_kelahiran DROP CONSTRAINT IF EXISTS chk_event_kelahiran_jk');
        DB::statement('ALTER TABLE event_kelahiran DROP CONSTRAINT IF EXISTS chk_event_kelahiran_anak_ke');
        DB::statement('ALTER TABLE event_kelahiran DROP CONSTRAINT IF EXISTS chk_event_kelahiran_berat');
        DB::statement('ALTER TABLE event_kelahiran DROP CONSTRAINT IF EXISTS chk_event_kelahiran_panjang');
        Schema::dropIfExists('event_kelahiran');
    }
};