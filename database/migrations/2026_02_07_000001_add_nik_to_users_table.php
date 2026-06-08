<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 16)->nullable()->unique()->after('username');
                $table->index('nik');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nik')) {
                $table->dropIndex(['nik']);
                $table->dropUnique(['nik']);
                $table->dropColumn('nik');
            }
        });
    }
};
