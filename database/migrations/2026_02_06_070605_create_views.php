<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // View: Penduduk Aktif dengan Info Lengkap
        DB::statement('
            CREATE OR REPLACE VIEW v_penduduk_aktif AS
            SELECT 
                p.*,
                rt.nomor_rt,
                rw.nomor_rw,
                rw.nama_ketua as ketua_rw,
                d.nama as nama_desa,
                a.nama as agama,
                pend.nama as pendidikan,
                pek.nama as pekerjaan,
                sk.nama as status_kependudukan
            FROM penduduks p
            JOIN rts rt ON p.rt_id = rt.id
            JOIN rws rw ON rt.rw_id = rw.id
            JOIN desas d ON rw.desa_id = d.id
            LEFT JOIN agamas a ON p.agama_id = a.kode
            LEFT JOIN pendidikans pend ON p.pendidikan_id = pend.kode
            LEFT JOIN pekerjaans pek ON p.pekerjaan_id = pek.kode
            JOIN status_kependudukan sk ON p.status_kependudukan_code = sk.kode
            WHERE p.deleted_at IS NULL
              AND p.status_kependudukan_code IN ("AKTIF")
        ');

        // View: KK dengan Anggota
        DB::statement('
            CREATE OR REPLACE VIEW v_kk_with_members AS
            SELECT 
                kk.id as kk_id,
                kk.no_kk,
                kk.alamat,
                rt.nomor_rt,
                rw.nomor_rw,
                COUNT(km.id) as jumlah_anggota,
                MAX(CASE WHEN km.is_kepala_keluarga THEN p.nama_lengkap END) as nama_kepala,
                MAX(CASE WHEN km.is_kepala_keluarga THEN p.nik END) as nik_kepala
            FROM kartu_keluargas kk
            JOIN rts rt ON kk.rt_id = rt.id
            JOIN rws rw ON rt.rw_id = rw.id
            LEFT JOIN kk_members km ON kk.id = km.kartu_keluarga_id AND km.status = "AKTIF"
            LEFT JOIN penduduks p ON km.penduduk_id = p.id
            WHERE kk.deleted_at IS NULL
              AND kk.status_kk = "AKTIF"
            GROUP BY kk.id, kk.no_kk, kk.alamat, rt.nomor_rt, rw.nomor_rw
        ');

        // View: Data Inconsistency Check
        DB::statement('
            CREATE OR REPLACE VIEW v_data_inconsistency AS
            SELECT 
                "PENDUDUK_PINDAH_KK_AKTIF" as issue_type,
                p.id as penduduk_id,
                p.nik,
                p.nama_lengkap,
                km.kartu_keluarga_id,
                "Penduduk status PINDAH tapi masih di KK aktif" as description
            FROM penduduks p
            JOIN kk_members km ON p.id = km.penduduk_id
            WHERE p.status_kependudukan_code = "PINDAH"
              AND km.status = "AKTIF"
              AND km.tanggal_keluar IS NULL
              AND p.deleted_at IS NULL

            UNION ALL

            SELECT 
                "PENDUDUK_MENINGGAL_KK_AKTIF" as issue_type,
                p.id,
                p.nik,
                p.nama_lengkap,
                km.kartu_keluarga_id,
                "Penduduk status MENINGGAL tapi masih di KK aktif" as description
            FROM penduduks p
            JOIN kk_members km ON p.id = km.penduduk_id
            WHERE p.status_kependudukan_code = "MENINGGAL"
              AND km.status = "AKTIF"
              AND km.tanggal_keluar IS NULL
              AND p.deleted_at IS NULL

            UNION ALL

            SELECT 
                "DUPLICATE_KEPALA_KELUARGA" as issue_type,
                p.id,
                p.nik,
                p.nama_lengkap,
                NULL as kartu_keluarga_id,
                CONCAT("Jadi kepala keluarga di ", COUNT(*), " KK sekaligus") as description
            FROM penduduks p
            JOIN kk_members km ON p.id = km.penduduk_id
            WHERE km.is_kepala_keluarga = TRUE
              AND km.status = "AKTIF"
              AND p.deleted_at IS NULL
            GROUP BY p.id, p.nik, p.nama_lengkap
            HAVING COUNT(*) > 1
        ');

        // View: Surat Akan Expired (7 hari ke depan)
        DB::statement('
            CREATE OR REPLACE VIEW v_surat_expiring_soon AS
            SELECT 
                st.*,
                js.nama as jenis_surat,
                p.nama_lengkap,
                p.no_hp,
                DATEDIFF(st.tanggal_kadaluarsa, CURDATE()) as days_remaining
            FROM surat_terbit st
            JOIN jenis_surat js ON st.jenis_surat_kode = js.kode
            JOIN penduduks p ON st.penduduk_id = p.id
            WHERE st.status = "AKTIF"
              AND st.tanggal_kadaluarsa IS NOT NULL
              AND st.tanggal_kadaluarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
              AND st.deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_surat_expiring_soon');
        DB::statement('DROP VIEW IF EXISTS v_data_inconsistency');
        DB::statement('DROP VIEW IF EXISTS v_kk_with_members');
        DB::statement('DROP VIEW IF EXISTS v_penduduk_aktif');
    }
};