<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_keluargas', function (Blueprint $table) {
            $table->id();
            $table->char('no_kk', 16);
            $table->text('alamat');
            $table->foreignId('rt_id')->constrained('rts')->onDelete('restrict')->onUpdate('cascade');
            $table->string('status_kk', 20)->default('AKTIF');
            $table->date('tanggal_terbentuk');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('rt_id');
            $table->index('status_kk');
            $table->index('tanggal_terbentuk');
            $table->index('deleted_at');
            $table->index('created_by');

            // Composite indexes
            $table->index(['rt_id', 'status_kk'], 'idx_kk_rt_status');
            $table->index(['status_kk', 'tanggal_terbentuk'], 'idx_kk_status_tanggal');

            // Unique index for no_kk
            $table->unique('no_kk', 'idx_kk_no_kk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_keluargas');
    }
};
