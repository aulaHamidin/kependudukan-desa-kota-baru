<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rw_id')->constrained('rws')->onDelete('restrict')->onUpdate('cascade');
            $table->string('nomor_rt', 5);
            $table->string('nama_ketua', 200)->nullable();
            $table->string('no_hp_ketua', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('rw_id');
            $table->index('deleted_at');

            // Unique: Prevent duplicate nomor RT in same RW
            $table->unique(['rw_id', 'nomor_rt', 'deleted_at'], 'idx_rts_rw_nomor');

            // Composite for hierarchy lookup
            $table->index(['rw_id', 'nomor_rt'], 'idx_rts_lookup');
        });

        // Note: Format validation moved to application level
        // Using RequestValidation rules instead of database constraints
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rts DROP CONSTRAINT IF EXISTS chk_rts_nomor_format');
        Schema::dropIfExists('rts');
    }
};
