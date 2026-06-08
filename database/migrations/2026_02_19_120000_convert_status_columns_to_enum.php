<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert status string columns to ENUM for better data integrity.
     * This migration uses raw SQL for MariaDB enum modification.
     */
    public function up(): void
    {
        // Convert surat_terbit.status from VARCHAR to ENUM
        DB::statement("ALTER TABLE surat_terbit MODIFY COLUMN status ENUM('AKTIF', 'BATAL') NOT NULL DEFAULT 'AKTIF'");

        // Convert kk_members.status from VARCHAR to ENUM
        DB::statement("ALTER TABLE kk_members MODIFY COLUMN status ENUM('AKTIF', 'KELUAR', 'PINDAH', 'MENINGGAL') NOT NULL DEFAULT 'AKTIF'");

        // Convert kartu_keluargas.status_kk from VARCHAR to ENUM
        // DB::statement("ALTER TABLE kartu_keluargas MODIFY COLUMN status_kk ENUM('AKTIF', 'KELUAR', 'PECAH', 'VOID') NOT NULL DEFAULT 'AKTIF'");

        // Convert events.status_data from VARCHAR to ENUM
        DB::statement("ALTER TABLE events MODIFY COLUMN status_data ENUM('DRAFT', 'VERIFIED', 'VOID') NOT NULL DEFAULT 'DRAFT'");

        // Convert penduduks.status_perkawinan from VARCHAR to ENUM
        DB::statement("ALTER TABLE penduduks MODIFY COLUMN status_perkawinan ENUM('Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati') NOT NULL DEFAULT 'Belum Kawin'");
    }

    public function down(): void
    {
        // Revert back to VARCHAR
        DB::statement("ALTER TABLE surat_terbit MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'AKTIF'");
        DB::statement("ALTER TABLE kk_members MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'AKTIF'");
        // DB::statement("ALTER TABLE kartu_keluargas MODIFY COLUMN status_kk VARCHAR(20) NOT NULL DEFAULT 'AKTIF'");
        DB::statement("ALTER TABLE events MODIFY COLUMN status_data VARCHAR(20) NOT NULL DEFAULT 'DRAFT'");
        DB::statement("ALTER TABLE penduduks MODIFY COLUMN status_perkawinan VARCHAR(20) NOT NULL DEFAULT 'Belum Kawin'");
    }
};
