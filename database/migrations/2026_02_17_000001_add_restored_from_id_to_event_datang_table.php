<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_datang', function (Blueprint $table) {
            $table->unsignedBigInteger('restored_from_id')
                ->nullable()
                ->after('kk_tujuan_id')
                ->comment('ID penduduk yang di-restore, null jika pendatang baru murni');
            $table->foreign('restored_from_id')
                ->references('id')
                ->on('penduduks');
        });
    }

    public function down(): void
    {
        Schema::table('event_datang', function (Blueprint $table) {
            $table->dropForeign(['restored_from_id']);
            $table->dropColumn('restored_from_id');
        });
    }
};
