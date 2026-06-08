<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add template_path column to jenis_surat table.
 * 
 * The existing codebase expects:
 * - template_path: storage path to the template file
 * - template_filename: original uploaded filename for display
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->string('template_path', 255)
                ->nullable()
                ->after('deskripsi')
                ->comment('Storage path to template file');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->dropColumn('template_path');
        });
    }
};
