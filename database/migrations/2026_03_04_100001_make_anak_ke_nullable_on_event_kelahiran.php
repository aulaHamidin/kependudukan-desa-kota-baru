<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_kelahiran', function (Blueprint $table) {
            $table->string('anak_ke')->default(1)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('event_kelahiran', function (Blueprint $table) {
            $table->string('anak_ke')->default(1)->nullable(false)->change();
        });
    }
};
