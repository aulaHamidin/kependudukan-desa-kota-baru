-- Schema export from Laravel migrated database
-- Database: kependudukan_desa
-- Generated at: 2026-05-25 18:31:44

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `agamas`;
CREATE TABLE `agamas` (
  `kode` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `agamas_nama_unique` (`nama`),
  KEY `agamas_is_active_index` (`is_active`),
  KEY `agamas_urutan_index` (`urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `actor_type` varchar(20) DEFAULT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `aksi` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `model_id` varchar(100) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `role_snapshot` varchar(50) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_aksi_index` (`aksi`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `idx_audit_user_date` (`user_id`,`created_at`),
  KEY `idx_audit_aksi_date` (`aksi`,`created_at`),
  KEY `idx_audit_search` (`user_id`,`aksi`,`model`,`created_at`),
  KEY `idx_audit_model` (`model`,`model_id`),
  KEY `idx_audit_model_date` (`model`,`model_id`,`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `desas`;
CREATE TABLE `desas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_desa` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kecamatan` varchar(100) NOT NULL,
  `kabupaten` varchar(100) NOT NULL,
  `provinsi` varchar(100) NOT NULL,
  `kode_pos` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `desas_kode_desa_unique` (`kode_desa`),
  KEY `desas_kode_desa_index` (`kode_desa`),
  KEY `desas_nama_index` (`nama`),
  KEY `desas_kabupaten_index` (`kabupaten`),
  KEY `desas_deleted_at_index` (`deleted_at`),
  KEY `idx_desas_regional` (`provinsi`,`kabupaten`,`kecamatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `event_datang`;
CREATE TABLE `event_datang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `alamat_asal` text DEFAULT NULL,
  `rt_asal` varchar(10) DEFAULT NULL,
  `rw_asal` varchar(10) DEFAULT NULL,
  `desa_asal` varchar(100) DEFAULT NULL,
  `kecamatan_asal` varchar(100) DEFAULT NULL,
  `kabupaten_asal` varchar(100) DEFAULT NULL,
  `provinsi_asal` varchar(100) DEFAULT NULL,
  `alasan_datang` varchar(100) DEFAULT NULL,
  `keterangan_alasan` text DEFAULT NULL,
  `jenis_kedatangan` varchar(50) DEFAULT NULL,
  `kk_tujuan_id` bigint(20) unsigned DEFAULT NULL,
  `restored_from_id` bigint(20) unsigned DEFAULT NULL COMMENT 'ID penduduk yang di-restore, null jika pendatang baru murni',
  `no_surat_pindah` varchar(50) DEFAULT NULL,
  `tanggal_surat_pindah` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_datang_event_id_unique` (`event_id`),
  KEY `idx_event_datang_asal` (`kabupaten_asal`,`kecamatan_asal`),
  KEY `event_datang_kk_tujuan_id_index` (`kk_tujuan_id`),
  KEY `event_datang_restored_from_id_foreign` (`restored_from_id`),
  CONSTRAINT `event_datang_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_datang_kk_tujuan_id_foreign` FOREIGN KEY (`kk_tujuan_id`) REFERENCES `kartu_keluargas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `event_datang_restored_from_id_foreign` FOREIGN KEY (`restored_from_id`) REFERENCES `penduduks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `event_kelahiran`;
CREATE TABLE `event_kelahiran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `nama_bayi` varchar(200) NOT NULL,
  `jenis_kelamin` char(1) NOT NULL,
  `status_kelahiran` enum('HIDUP','MATI') NOT NULL DEFAULT 'HIDUP' COMMENT 'Status kelahiran bayi: HIDUP atau MATI (stillbirth)',
  `ayah_id` bigint(20) unsigned DEFAULT NULL,
  `ibu_id` bigint(20) unsigned DEFAULT NULL,
  `nama_ayah` varchar(200) DEFAULT NULL,
  `nama_ibu` varchar(200) DEFAULT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `jam_lahir` time DEFAULT NULL,
  `anak_ke` varchar(255) DEFAULT '1',
  `berat_badan_kg` decimal(4,2) DEFAULT NULL,
  `panjang_badan_cm` decimal(5,2) DEFAULT NULL,
  `penolong_kelahiran` varchar(50) DEFAULT NULL,
  `nama_penolong` varchar(200) DEFAULT NULL,
  `kk_tujuan_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_kelahiran_event_id_unique` (`event_id`),
  KEY `event_kelahiran_kk_tujuan_id_foreign` (`kk_tujuan_id`),
  KEY `event_kelahiran_ayah_id_index` (`ayah_id`),
  KEY `event_kelahiran_ibu_id_index` (`ibu_id`),
  KEY `event_kelahiran_nama_bayi_index` (`nama_bayi`),
  KEY `event_kelahiran_jenis_kelamin_index` (`jenis_kelamin`),
  CONSTRAINT `event_kelahiran_ayah_id_foreign` FOREIGN KEY (`ayah_id`) REFERENCES `penduduks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `event_kelahiran_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_kelahiran_ibu_id_foreign` FOREIGN KEY (`ibu_id`) REFERENCES `penduduks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `event_kelahiran_kk_tujuan_id_foreign` FOREIGN KEY (`kk_tujuan_id`) REFERENCES `kartu_keluargas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_event_kelahiran_jk` CHECK (`jenis_kelamin` in ('L','P')),
  CONSTRAINT `chk_event_kelahiran_anak_ke` CHECK (`anak_ke` >= 1),
  CONSTRAINT `chk_event_kelahiran_berat` CHECK (`berat_badan_kg` is null or `berat_badan_kg` between 0.5 and 10),
  CONSTRAINT `chk_event_kelahiran_panjang` CHECK (`panjang_badan_cm` is null or `panjang_badan_cm` between 20 and 80)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `event_kematian`;
CREATE TABLE `event_kematian` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `tempat_meninggal` varchar(200) NOT NULL,
  `jam_meninggal` time DEFAULT NULL,
  `sebab_kematian` varchar(100) DEFAULT NULL,
  `penyakit` varchar(200) DEFAULT NULL,
  `keterangan_kematian` text DEFAULT NULL,
  `was_kepala` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Snapshot: apakah almarhum kepala keluarga saat event dibuat',
  `pengganti_id` bigint(20) unsigned DEFAULT NULL,
  `pelapor_id` bigint(20) unsigned DEFAULT NULL,
  `nama_pelapor` varchar(200) DEFAULT NULL,
  `hubungan_pelapor_code` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_kematian_event_id_unique` (`event_id`),
  KEY `event_kematian_pelapor_id_index` (`pelapor_id`),
  KEY `event_kematian_tempat_meninggal_index` (`tempat_meninggal`),
  KEY `event_kematian_hubungan_pelapor_code_foreign` (`hubungan_pelapor_code`),
  KEY `event_kematian_pengganti_id_foreign` (`pengganti_id`),
  CONSTRAINT `event_kematian_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_kematian_hubungan_pelapor_code_foreign` FOREIGN KEY (`hubungan_pelapor_code`) REFERENCES `hubungan_keluarga` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `event_kematian_pelapor_id_foreign` FOREIGN KEY (`pelapor_id`) REFERENCES `penduduks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `event_kematian_pengganti_id_foreign` FOREIGN KEY (`pengganti_id`) REFERENCES `penduduks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `event_pindah`;
CREATE TABLE `event_pindah` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `alamat_tujuan` text NOT NULL,
  `rt_tujuan` varchar(10) DEFAULT NULL,
  `rw_tujuan` varchar(10) DEFAULT NULL,
  `desa_tujuan` varchar(100) DEFAULT NULL,
  `kecamatan_tujuan` varchar(100) DEFAULT NULL,
  `kabupaten_tujuan` varchar(100) DEFAULT NULL,
  `provinsi_tujuan` varchar(100) DEFAULT NULL,
  `kode_pos_tujuan` varchar(10) DEFAULT NULL,
  `alasan_pindah` varchar(100) DEFAULT NULL,
  `keterangan_alasan` text DEFAULT NULL,
  `jenis_kepindahan` varchar(50) DEFAULT NULL,
  `tanggal_pindah` date NOT NULL,
  `was_kepala` tinyint(1) NOT NULL DEFAULT 0,
  `pengganti_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_pindah_event_id_unique` (`event_id`),
  KEY `idx_event_pindah_tujuan` (`kabupaten_tujuan`,`kecamatan_tujuan`),
  KEY `event_pindah_tanggal_pindah_index` (`tanggal_pindah`),
  KEY `event_pindah_pengganti_id_foreign` (`pengganti_id`),
  CONSTRAINT `event_pindah_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_pindah_pengganti_id_foreign` FOREIGN KEY (`pengganti_id`) REFERENCES `penduduks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `event_types`;
CREATE TABLE `event_types` (
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `require_details` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `event_types_nama_unique` (`nama`),
  KEY `event_types_is_active_index` (`is_active`),
  KEY `event_types_require_details_index` (`require_details`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_type_code` varchar(20) NOT NULL,
  `penduduk_id` bigint(20) unsigned DEFAULT NULL,
  `event_date` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `rt_id` bigint(20) unsigned NOT NULL,
  `rw_id` bigint(20) unsigned NOT NULL,
  `desa_id` bigint(20) unsigned NOT NULL,
  `kk_id` bigint(20) unsigned DEFAULT NULL,
  `status_data` enum('DRAFT','VERIFIED','VOID') NOT NULL DEFAULT 'DRAFT',
  `void_reason` text DEFAULT NULL,
  `void_at` timestamp NULL DEFAULT NULL,
  `voided_by` bigint(20) unsigned DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_voided_by_foreign` (`voided_by`),
  KEY `events_verified_by_foreign` (`verified_by`),
  KEY `events_event_type_code_index` (`event_type_code`),
  KEY `events_penduduk_id_index` (`penduduk_id`),
  KEY `events_event_date_index` (`event_date`),
  KEY `events_rt_id_index` (`rt_id`),
  KEY `events_rw_id_index` (`rw_id`),
  KEY `events_desa_id_index` (`desa_id`),
  KEY `events_kk_id_index` (`kk_id`),
  KEY `events_status_data_index` (`status_data`),
  KEY `events_created_by_index` (`created_by`),
  KEY `idx_event_type_date` (`event_type_code`,`event_date`),
  KEY `idx_event_type_status` (`event_type_code`,`status_data`),
  KEY `idx_event_penduduk_date` (`penduduk_id`,`event_date`),
  KEY `idx_event_desa_type_date` (`desa_id`,`event_type_code`,`event_date`),
  KEY `idx_events_type_status` (`event_type_code`,`status_data`),
  KEY `idx_events_penduduk_date` (`penduduk_id`,`event_date`),
  KEY `idx_events_rt_type` (`rt_id`,`event_type_code`),
  KEY `idx_event_status_type` (`status_data`,`event_type_code`),
  CONSTRAINT `events_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `events_desa_id_foreign` FOREIGN KEY (`desa_id`) REFERENCES `desas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `events_event_type_code_foreign` FOREIGN KEY (`event_type_code`) REFERENCES `event_types` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `events_kk_id_foreign` FOREIGN KEY (`kk_id`) REFERENCES `kartu_keluargas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `events_penduduk_id_foreign` FOREIGN KEY (`penduduk_id`) REFERENCES `penduduks` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `events_rt_id_foreign` FOREIGN KEY (`rt_id`) REFERENCES `rts` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `events_rw_id_foreign` FOREIGN KEY (`rw_id`) REFERENCES `rws` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `events_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `events_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `golongan_darahs`;
CREATE TABLE `golongan_darahs` (
  `kode` varchar(5) NOT NULL,
  `nama` varchar(10) NOT NULL,
  `rhesus` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`kode`),
  KEY `golongan_darahs_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `hubungan_keluarga`;
CREATE TABLE `hubungan_keluarga` (
  `kode` varchar(20) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `hubungan_keluarga_nama_unique` (`nama`),
  KEY `hubungan_keluarga_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `jenis_surat`;
CREATE TABLE `jenis_surat` (
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `template_category` varchar(50) NOT NULL DEFAULT 'keterangan' COMMENT 'Kategori template: keterangan, pengantar, izin, pernyataan, rekomendasi, internal',
  `template_sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON config: data_fields, intro, body, signature, dll' CHECK (json_valid(`template_sections`)),
  `prefix_nomor` varchar(10) NOT NULL COMMENT 'Prefix for numbering, e.g., SKD, SKTM',
  `format_nomor` varchar(100) NOT NULL DEFAULT '{sequence}/{prefix}/{month_roman}/{year}',
  `masa_berlaku_hari` int(11) DEFAULT NULL COMMENT 'Default validity in days, NULL = no expiry',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `jenis_surat_nama_unique` (`nama`),
  KEY `jenis_surat_is_active_index` (`is_active`),
  KEY `jenis_surat_prefix_nomor_index` (`prefix_nomor`),
  KEY `jenis_surat_template_category_index` (`template_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `kartu_keluargas`;
CREATE TABLE `kartu_keluargas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_kk` char(16) NOT NULL,
  `alamat` text NOT NULL,
  `rt_id` bigint(20) unsigned NOT NULL,
  `status_kk` varchar(20) NOT NULL DEFAULT 'AKTIF',
  `tanggal_terbentuk` date NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_kk_no_kk` (`no_kk`),
  KEY `kartu_keluargas_updated_by_foreign` (`updated_by`),
  KEY `kartu_keluargas_rt_id_index` (`rt_id`),
  KEY `kartu_keluargas_status_kk_index` (`status_kk`),
  KEY `kartu_keluargas_tanggal_terbentuk_index` (`tanggal_terbentuk`),
  KEY `kartu_keluargas_deleted_at_index` (`deleted_at`),
  KEY `kartu_keluargas_created_by_index` (`created_by`),
  KEY `idx_kk_rt_status` (`rt_id`,`status_kk`),
  KEY `idx_kk_status_tanggal` (`status_kk`,`tanggal_terbentuk`),
  CONSTRAINT `kartu_keluargas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `kartu_keluargas_rt_id_foreign` FOREIGN KEY (`rt_id`) REFERENCES `rts` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `kartu_keluargas_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `kk_members`;
CREATE TABLE `kk_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kartu_keluarga_id` bigint(20) unsigned NOT NULL,
  `penduduk_id` bigint(20) unsigned NOT NULL,
  `hubungan_keluarga_code` varchar(20) NOT NULL,
  `is_kepala_keluarga` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal_masuk` date NOT NULL,
  `tanggal_keluar` date DEFAULT NULL,
  `status` enum('AKTIF','KELUAR','PINDAH','MENINGGAL') NOT NULL DEFAULT 'AKTIF',
  `kk_asal_id` bigint(20) unsigned DEFAULT NULL,
  `event_keluar_id` bigint(20) unsigned DEFAULT NULL,
  `alasan_keluar` text DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kk_members_created_by_foreign` (`created_by`),
  KEY `kk_members_kartu_keluarga_id_index` (`kartu_keluarga_id`),
  KEY `kk_members_penduduk_id_index` (`penduduk_id`),
  KEY `kk_members_status_index` (`status`),
  KEY `kk_members_is_kepala_keluarga_index` (`is_kepala_keluarga`),
  KEY `kk_members_event_keluar_id_index` (`event_keluar_id`),
  KEY `idx_kk_member_kk_status` (`kartu_keluarga_id`,`status`),
  KEY `idx_kk_member_penduduk_status` (`penduduk_id`,`status`),
  KEY `idx_kk_member_kepala_check` (`penduduk_id`,`is_kepala_keluarga`,`status`),
  KEY `kk_members_hubungan_keluarga_code_foreign` (`hubungan_keluarga_code`),
  KEY `kk_members_kk_asal_id_foreign` (`kk_asal_id`),
  KEY `idx_kk_members_kk_status` (`kartu_keluarga_id`,`status`),
  KEY `idx_kk_members_penduduk_status` (`penduduk_id`,`status`),
  CONSTRAINT `kk_members_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `kk_members_event_keluar_id_foreign` FOREIGN KEY (`event_keluar_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `kk_members_hubungan_keluarga_code_foreign` FOREIGN KEY (`hubungan_keluarga_code`) REFERENCES `hubungan_keluarga` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `kk_members_kartu_keluarga_id_foreign` FOREIGN KEY (`kartu_keluarga_id`) REFERENCES `kartu_keluargas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `kk_members_kk_asal_id_foreign` FOREIGN KEY (`kk_asal_id`) REFERENCES `kartu_keluargas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `kk_members_penduduk_id_foreign` FOREIGN KEY (`penduduk_id`) REFERENCES `penduduks` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `pekerjaans`;
CREATE TABLE `pekerjaans` (
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `pekerjaans_nama_unique` (`nama`),
  KEY `pekerjaans_is_active_index` (`is_active`),
  KEY `pekerjaans_urutan_index` (`urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `pendapatan_ranges`;
CREATE TABLE `pendapatan_ranges` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `min_value` decimal(15,2) DEFAULT NULL,
  `max_value` decimal(15,2) DEFAULT NULL,
  `label` varchar(100) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pendapatan_ranges_label_unique` (`label`),
  KEY `pendapatan_ranges_is_active_index` (`is_active`),
  KEY `pendapatan_ranges_urutan_index` (`urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `pendidikans`;
CREATE TABLE `pendidikans` (
  `kode` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `pendidikans_nama_unique` (`nama`),
  KEY `pendidikans_is_active_index` (`is_active`),
  KEY `pendidikans_urutan_index` (`urutan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `penduduks`;
CREATE TABLE `penduduks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nik` char(16) NOT NULL,
  `nama_lengkap` varchar(200) NOT NULL,
  `jenis_kelamin` char(1) NOT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tgl_lahir` date NOT NULL,
  `ayah_id` bigint(20) unsigned DEFAULT NULL,
  `ibu_id` bigint(20) unsigned DEFAULT NULL,
  `nama_ayah` varchar(200) DEFAULT NULL,
  `nama_ibu` varchar(200) DEFAULT NULL,
  `agama_id` varchar(10) NOT NULL,
  `pendidikan_id` varchar(20) DEFAULT NULL,
  `pekerjaan_id` varchar(10) DEFAULT NULL,
  `pendapatan_range_id` bigint(20) unsigned DEFAULT NULL,
  `golongan_darah_id` varchar(5) DEFAULT NULL,
  `kewarganegaraan` varchar(3) NOT NULL DEFAULT 'WNI',
  `no_paspor` varchar(50) DEFAULT NULL,
  `status_perkawinan` varchar(20) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rt_id` bigint(20) unsigned DEFAULT NULL,
  `status_kependudukan_code` varchar(20) NOT NULL,
  `current_event_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal_status` date NOT NULL,
  `data_version` int(11) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `penduduks_nik_unique` (`nik`),
  UNIQUE KEY `penduduks_no_hp_unique` (`no_hp`),
  UNIQUE KEY `penduduks_email_unique` (`email`),
  KEY `penduduks_pendapatan_range_id_foreign` (`pendapatan_range_id`),
  KEY `penduduks_created_by_foreign` (`created_by`),
  KEY `penduduks_updated_by_foreign` (`updated_by`),
  KEY `penduduks_nama_lengkap_index` (`nama_lengkap`),
  KEY `penduduks_tgl_lahir_index` (`tgl_lahir`),
  KEY `penduduks_jenis_kelamin_index` (`jenis_kelamin`),
  KEY `penduduks_deleted_at_index` (`deleted_at`),
  KEY `penduduks_rt_id_index` (`rt_id`),
  KEY `penduduks_ayah_id_index` (`ayah_id`),
  KEY `penduduks_ibu_id_index` (`ibu_id`),
  KEY `penduduks_agama_id_index` (`agama_id`),
  KEY `penduduks_pendidikan_id_index` (`pendidikan_id`),
  KEY `penduduks_pekerjaan_id_index` (`pekerjaan_id`),
  KEY `penduduks_status_kependudukan_code_index` (`status_kependudukan_code`),
  KEY `penduduks_current_event_id_index` (`current_event_id`),
  KEY `idx_penduduk_rt_status` (`rt_id`,`status_kependudukan_code`),
  KEY `idx_penduduk_status_tanggal` (`status_kependudukan_code`,`tanggal_status`),
  KEY `idx_penduduk_jk_status` (`jenis_kelamin`,`status_kependudukan_code`),
  KEY `idx_penduduk_nama_tgl_lahir` (`nama_lengkap`,`tgl_lahir`),
  KEY `idx_penduduk_demografi` (`rt_id`,`jenis_kelamin`,`tgl_lahir`),
  KEY `penduduks_golongan_darah_id_foreign` (`golongan_darah_id`),
  KEY `idx_penduduks_status` (`status_kependudukan_code`),
  KEY `idx_penduduks_rt` (`rt_id`),
  KEY `idx_penduduks_rt_status` (`rt_id`,`status_kependudukan_code`),
  CONSTRAINT `penduduks_agama_id_foreign` FOREIGN KEY (`agama_id`) REFERENCES `agamas` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `penduduks_ayah_id_foreign` FOREIGN KEY (`ayah_id`) REFERENCES `penduduks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `penduduks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `penduduks_current_event_id_foreign` FOREIGN KEY (`current_event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `penduduks_golongan_darah_id_foreign` FOREIGN KEY (`golongan_darah_id`) REFERENCES `golongan_darahs` (`kode`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `penduduks_ibu_id_foreign` FOREIGN KEY (`ibu_id`) REFERENCES `penduduks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `penduduks_pekerjaan_id_foreign` FOREIGN KEY (`pekerjaan_id`) REFERENCES `pekerjaans` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `penduduks_pendapatan_range_id_foreign` FOREIGN KEY (`pendapatan_range_id`) REFERENCES `pendapatan_ranges` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `penduduks_pendidikan_id_foreign` FOREIGN KEY (`pendidikan_id`) REFERENCES `pendidikans` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `penduduks_rt_id_foreign` FOREIGN KEY (`rt_id`) REFERENCES `rts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `penduduks_status_kependudukan_code_foreign` FOREIGN KEY (`status_kependudukan_code`) REFERENCES `status_kependudukan` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `penduduks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `rts`;
CREATE TABLE `rts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rw_id` bigint(20) unsigned NOT NULL,
  `nomor_rt` varchar(5) NOT NULL,
  `nama_ketua` varchar(200) DEFAULT NULL,
  `no_hp_ketua` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rts_rw_nomor` (`rw_id`,`nomor_rt`,`deleted_at`),
  KEY `rts_rw_id_index` (`rw_id`),
  KEY `rts_deleted_at_index` (`deleted_at`),
  KEY `idx_rts_lookup` (`rw_id`,`nomor_rt`),
  CONSTRAINT `rts_rw_id_foreign` FOREIGN KEY (`rw_id`) REFERENCES `rws` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `rws`;
CREATE TABLE `rws` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `desa_id` bigint(20) unsigned NOT NULL,
  `nomor_rw` varchar(5) NOT NULL,
  `nama_ketua` varchar(200) DEFAULT NULL,
  `no_hp_ketua` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_rws_desa_nomor` (`desa_id`,`nomor_rw`,`deleted_at`),
  KEY `rws_desa_id_index` (`desa_id`),
  KEY `rws_deleted_at_index` (`deleted_at`),
  KEY `rws_nama_ketua_index` (`nama_ketua`),
  CONSTRAINT `rws_desa_id_foreign` FOREIGN KEY (`desa_id`) REFERENCES `desas` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `status_kependudukan`;
CREATE TABLE `status_kependudukan` (
  `kode` varchar(20) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`kode`),
  UNIQUE KEY `status_kependudukan_nama_unique` (`nama`),
  KEY `status_kependudukan_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `surat_nomor_sequences`;
CREATE TABLE `surat_nomor_sequences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode_surat` varchar(30) NOT NULL,
  `tahun` int(11) NOT NULL,
  `sequence_number` int(11) NOT NULL DEFAULT 0,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_surat_nomor_sequences_unique` (`kode_surat`,`tahun`),
  KEY `idx_surat_nomor_sequences_tahun` (`tahun`),
  CONSTRAINT `chk_surat_nomor_sequences_tahun` CHECK (`tahun` between 2000 and 2100),
  CONSTRAINT `chk_surat_nomor_sequences_number` CHECK (`sequence_number` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `surat_sequence`;
CREATE TABLE `surat_sequence` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jenis_surat_kode` varchar(20) NOT NULL,
  `tahun` int(11) NOT NULL,
  `bulan` int(11) NOT NULL,
  `sequence_number` int(11) NOT NULL DEFAULT 0,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_surat_sequence_unique` (`jenis_surat_kode`,`tahun`,`bulan`),
  KEY `idx_surat_sequence_tahun_bulan` (`tahun`,`bulan`),
  KEY `surat_sequence_jenis_surat_kode_index` (`jenis_surat_kode`),
  CONSTRAINT `surat_sequence_jenis_surat_kode_foreign` FOREIGN KEY (`jenis_surat_kode`) REFERENCES `jenis_surat` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `chk_surat_sequence_tahun` CHECK (`tahun` between 2000 and 2100),
  CONSTRAINT `chk_surat_sequence_bulan` CHECK (`bulan` between 1 and 12),
  CONSTRAINT `chk_surat_sequence_number` CHECK (`sequence_number` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `surat_terbit`;
CREATE TABLE `surat_terbit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(50) NOT NULL,
  `jenis_surat_kode` varchar(20) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `keperluan` text NOT NULL,
  `keterangan_tambahan` text DEFAULT NULL,
  `data_surat` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Dynamic fields per jenis surat type in JSON' CHECK (json_valid(`data_surat`)),
  `file_path` varchar(255) DEFAULT NULL COMMENT 'Generated PDF path',
  `pdf_status` enum('PROCESSING','READY','FAILED') NOT NULL DEFAULT 'PROCESSING' COMMENT 'PDF generation status for queue',
  `pdf_generated_at` timestamp NULL DEFAULT NULL,
  `penduduk_id` bigint(20) unsigned NOT NULL,
  `kk_id` bigint(20) unsigned NOT NULL,
  `rt_id` bigint(20) unsigned NOT NULL,
  `rw_id` bigint(20) unsigned NOT NULL,
  `desa_id` bigint(20) unsigned NOT NULL,
  `masa_berlaku_hari` int(10) unsigned DEFAULT NULL COMMENT 'Diambil dari jenis_surat.masa_berlaku_hari saat terbit',
  `tanggal_kadaluarsa` date DEFAULT NULL COMMENT 'Computed: tanggal_terbit + masa_berlaku_hari via observer',
  `status` enum('AKTIF','BATAL') NOT NULL DEFAULT 'AKTIF',
  `alasan_batal` text DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_terbit_nomor_surat_unique` (`nomor_surat`),
  KEY `surat_terbit_cancelled_by_foreign` (`cancelled_by`),
  KEY `idx_surat_jenis_kode` (`jenis_surat_kode`),
  KEY `idx_surat_penduduk` (`penduduk_id`),
  KEY `idx_surat_kk` (`kk_id`),
  KEY `idx_surat_tgl_terbit` (`tanggal_terbit`),
  KEY `idx_surat_tgl_exp` (`tanggal_kadaluarsa`),
  KEY `idx_surat_status` (`status`),
  KEY `idx_surat_pdf_status` (`pdf_status`),
  KEY `idx_surat_rt` (`rt_id`),
  KEY `idx_surat_rw` (`rw_id`),
  KEY `idx_surat_desa` (`desa_id`),
  KEY `idx_surat_created_by` (`created_by`),
  KEY `idx_surat_updated_by` (`updated_by`),
  KEY `idx_surat_deleted_at` (`deleted_at`),
  KEY `idx_surat_jenis_tanggal` (`jenis_surat_kode`,`tanggal_terbit`),
  KEY `idx_surat_penduduk_jenis` (`penduduk_id`,`jenis_surat_kode`),
  KEY `idx_surat_desa_tanggal` (`desa_id`,`tanggal_terbit`),
  KEY `idx_surat_desa_status` (`desa_id`,`status`),
  KEY `idx_surat_queue` (`pdf_status`,`created_at`),
  KEY `idx_surat_exp_monitor` (`status`,`tanggal_kadaluarsa`),
  KEY `idx_surat_status_expired` (`status`,`tanggal_kadaluarsa`),
  CONSTRAINT `surat_terbit_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_desa_id_foreign` FOREIGN KEY (`desa_id`) REFERENCES `desas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_jenis_surat_kode_foreign` FOREIGN KEY (`jenis_surat_kode`) REFERENCES `jenis_surat` (`kode`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_kk_id_foreign` FOREIGN KEY (`kk_id`) REFERENCES `kartu_keluargas` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_penduduk_id_foreign` FOREIGN KEY (`penduduk_id`) REFERENCES `penduduks` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_rt_id_foreign` FOREIGN KEY (`rt_id`) REFERENCES `rts` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_rw_id_foreign` FOREIGN KEY (`rw_id`) REFERENCES `rws` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `surat_terbit_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `role` enum('super_admin','admin_desa','admin_rw','admin_rt','viewer') NOT NULL DEFAULT 'viewer',
  `desa_id` bigint(20) unsigned DEFAULT NULL,
  `rw_id` bigint(20) unsigned DEFAULT NULL,
  `rt_id` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_nik_unique` (`nik`),
  KEY `users_desa_id_index` (`desa_id`),
  KEY `users_rw_id_index` (`rw_id`),
  KEY `users_rt_id_index` (`rt_id`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_deleted_at_index` (`deleted_at`),
  KEY `users_last_login_at_index` (`last_login_at`),
  KEY `idx_user_scope` (`desa_id`,`rw_id`,`rt_id`,`is_active`),
  KEY `users_nik_index` (`nik`),
  CONSTRAINT `users_desa_id_foreign` FOREIGN KEY (`desa_id`) REFERENCES `desas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `users_rt_id_foreign` FOREIGN KEY (`rt_id`) REFERENCES `rts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `users_rw_id_foreign` FOREIGN KEY (`rw_id`) REFERENCES `rws` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
