<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type_code', 20);
            $table->foreignId('penduduk_id')->nullable()->constrained('penduduks')->onDelete('restrict')->onUpdate('cascade');
            $table->date('event_date');
            $table->text('keterangan')->nullable();
            $table->foreignId('rt_id')->constrained('rts')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('rw_id')->constrained('rws')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('desa_id')->constrained('desas')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('kk_id')->nullable()->constrained('kartu_keluargas')->onDelete('set null')->onUpdate('cascade');
            $table->string('status_data', 20)->default('DRAFT');
            $table->text('void_reason')->nullable();
            $table->timestamp('void_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();

            // Indexes
            $table->index('event_type_code');
            $table->index('penduduk_id');
            $table->index('event_date');
            $table->index('rt_id');
            $table->index('rw_id');
            $table->index('desa_id');
            $table->index('kk_id');
            $table->index('status_data');
            $table->index('created_by');

            // Composite indexes for complex queries
            $table->index(['event_type_code', 'event_date'], 'idx_event_type_date');
            $table->index(['event_type_code', 'status_data'], 'idx_event_type_status');
            $table->index(['penduduk_id', 'event_date'], 'idx_event_penduduk_date');
            $table->index(['desa_id', 'event_type_code', 'event_date'], 'idx_event_desa_type_date');

            // Foreign keys
            $table->foreign('event_type_code')->references('kode')->on('event_types')->onDelete('restrict')->onUpdate('cascade');
        });

        // Note: Additional indexes with WHERE clause and check constraints
        // have been moved to application level (MariaDB compatibility)
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
