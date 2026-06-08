<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add self-referencing foreign keys for ayah and ibu
        Schema::table('penduduks', function (Blueprint $table) {
            $table->foreign('ayah_id')->references('id')->on('penduduks')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('ibu_id')->references('id')->on('penduduks')->onDelete('set null')->onUpdate('cascade');
        });

        // Add foreign key for current_event_id (references events table)
        Schema::table('penduduks', function (Blueprint $table) {
            $table->foreign('current_event_id')->references('id')->on('events')->onDelete('set null')->onUpdate('cascade');
        });

        // Add foreign key for event_keluar_id in kk_members
        Schema::table('kk_members', function (Blueprint $table) {
            $table->foreign('event_keluar_id')->references('id')->on('events')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('kk_members', function (Blueprint $table) {
            $table->dropForeign(['event_keluar_id']);
        });

        Schema::table('penduduks', function (Blueprint $table) {
            $table->dropForeign(['current_event_id']);
            $table->dropForeign(['ayah_id']);
            $table->dropForeign(['ibu_id']);
        });
    }
};