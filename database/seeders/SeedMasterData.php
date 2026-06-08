<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedMasterData extends Seeder
{
    public function run(): void
    {
        // Seed Agamas
        DB::table('agamas')->insert([
            ['kode' => 'ISLAM', 'nama' => 'Islam', 'urutan' => 1, 'is_active' => true],
            ['kode' => 'KRISTEN', 'nama' => 'Kristen', 'urutan' => 2, 'is_active' => true],
            ['kode' => 'KATOLIK', 'nama' => 'Katolik', 'urutan' => 3, 'is_active' => true],
            ['kode' => 'HINDU', 'nama' => 'Hindu', 'urutan' => 4, 'is_active' => true],
            ['kode' => 'BUDDHA', 'nama' => 'Buddha', 'urutan' => 5, 'is_active' => true],
            ['kode' => 'KONGHUCU', 'nama' => 'Konghucu', 'urutan' => 6, 'is_active' => true],
        ]);

        // Seed Pendidikans
        DB::table('pendidikans')->insert([
            ['kode' => 'TIDAK', 'nama' => 'Tidak/Belum Sekolah', 'urutan' => 1, 'is_active' => true],
            ['kode' => 'BELUM', 'nama' => 'Belum Tamat SD/Sederajat', 'urutan' => 2, 'is_active' => true],
            ['kode' => 'SD', 'nama' => 'Tamat SD/Sederajat', 'urutan' => 3, 'is_active' => true],
            ['kode' => 'SLTP', 'nama' => 'SLTP/Sederajat', 'urutan' => 4, 'is_active' => true],
            ['kode' => 'SLTA', 'nama' => 'SLTA/Sederajat', 'urutan' => 5, 'is_active' => true],
            ['kode' => 'D1', 'nama' => 'Diploma I/II', 'urutan' => 6, 'is_active' => true],
            ['kode' => 'D3', 'nama' => 'Diploma III/Sarjana Muda', 'urutan' => 7, 'is_active' => true],
            ['kode' => 'D4', 'nama' => 'Diploma IV/Strata I', 'urutan' => 8, 'is_active' => true],
            ['kode' => 'S1', 'nama' => 'Strata I', 'urutan' => 9, 'is_active' => true],
            ['kode' => 'S2', 'nama' => 'Strata II', 'urutan' => 10, 'is_active' => true],
            ['kode' => 'S3', 'nama' => 'Strata III', 'urutan' => 11, 'is_active' => true],
        ]);

        // Seed Pekerjaans (sample)
        DB::table('pekerjaans')->insert([
            ['kode' => 'BELUM', 'nama' => 'Belum/Tidak Bekerja', 'urutan' => 1, 'is_active' => true],
            ['kode' => 'PNS', 'nama' => 'Pegawai Negeri Sipil', 'urutan' => 2, 'is_active' => true],
            ['kode' => 'TNI', 'nama' => 'TNI', 'urutan' => 3, 'is_active' => true],
            ['kode' => 'POLRI', 'nama' => 'POLRI', 'urutan' => 4, 'is_active' => true],
            ['kode' => 'SWASTA', 'nama' => 'Karyawan Swasta', 'urutan' => 5, 'is_active' => true],
            ['kode' => 'WIRASWASTA', 'nama' => 'Wiraswasta', 'urutan' => 6, 'is_active' => true],
            ['kode' => 'PETANI', 'nama' => 'Petani/Pekebun', 'urutan' => 7, 'is_active' => true],
            ['kode' => 'NELAYAN', 'nama' => 'Nelayan/Perikanan', 'urutan' => 8, 'is_active' => true],
            ['kode' => 'BURUH', 'nama' => 'Buruh Harian Lepas', 'urutan' => 9, 'is_active' => true],
            ['kode' => 'GURU', 'nama' => 'Guru', 'urutan' => 10, 'is_active' => true],
            ['kode' => 'DOKTER', 'nama' => 'Dokter', 'urutan' => 11, 'is_active' => true],
            ['kode' => 'BIDAN', 'nama' => 'Bidan', 'urutan' => 12, 'is_active' => true],
            ['kode' => 'PENSIUNAN', 'nama' => 'Pensiunan', 'urutan' => 13, 'is_active' => true],
            ['kode' => 'PELAJAR', 'nama' => 'Pelajar/Mahasiswa', 'urutan' => 14, 'is_active' => true],
            ['kode' => 'IRT', 'nama' => 'Ibu Rumah Tangga', 'urutan' => 15, 'is_active' => true],
        ]);

        // Seed Golongan Darah
        DB::table('golongan_darahs')->insert([
            ['kode' => 'A+', 'nama' => 'A', 'rhesus' => '+', 'is_active' => true],
            ['kode' => 'A-', 'nama' => 'A', 'rhesus' => '-', 'is_active' => true],
            ['kode' => 'B+', 'nama' => 'B', 'rhesus' => '+', 'is_active' => true],
            ['kode' => 'B-', 'nama' => 'B', 'rhesus' => '-', 'is_active' => true],
            ['kode' => 'AB+', 'nama' => 'AB', 'rhesus' => '+', 'is_active' => true],
            ['kode' => 'AB-', 'nama' => 'AB', 'rhesus' => '-', 'is_active' => true],
            ['kode' => 'O+', 'nama' => 'O', 'rhesus' => '+', 'is_active' => true],
            ['kode' => 'O-', 'nama' => 'O', 'rhesus' => '-', 'is_active' => true],
        ]);

        // Seed Pendapatan Ranges (non-overlapping, domain-safe)
        DB::table('pendapatan_ranges')->insert([
            [
                'min_value' => null,
                'max_value' => 999999,
                'label' => 'Kurang dari Rp 1.000.000',
                'urutan' => 1,
                'is_active' => true,
            ],
            [
                'min_value' => 1000000,
                'max_value' => 1999999,
                'label' => 'Rp 1.000.000 - Rp 1.999.999',
                'urutan' => 2,
                'is_active' => true,
            ],
            [
                'min_value' => 2000000,
                'max_value' => 4999999,
                'label' => 'Rp 2.000.000 - Rp 4.999.999',
                'urutan' => 3,
                'is_active' => true,
            ],
            [
                'min_value' => 5000000,
                'max_value' => 9999999,
                'label' => 'Rp 5.000.000 - Rp 9.999.999',
                'urutan' => 4,
                'is_active' => true,
            ],
            [
                'min_value' => 10000000,
                'max_value' => null,
                'label' => 'Rp 10.000.000 ke atas',
                'urutan' => 5,
                'is_active' => true,
            ],
        ]);

        $pendapatanRangeId = DB::table('pendapatan_ranges')
            ->where('label', 'Rp 1.000.000 - Rp 1.999.999')
            ->value('id');


        // Seed Status Kependudukan
        // Seed Status Kependudukan (FINAL VERSION)
        DB::table('status_kependudukan')->insert([
            // =========================
            // CORE STATUS (EKSISTENSI)
            // =========================
            [
                'kode' => 'AKTIF',
                'nama' => 'Aktif',
                'deskripsi' => 'Penduduk aktif dan tercatat secara resmi',
                'is_active' => true,
            ],
            [
                'kode' => 'PINDAH',
                'nama' => 'Pindah',
                'deskripsi' => 'Penduduk telah pindah keluar dari wilayah administrasi',
                'is_active' => true,
            ],
            [
                'kode' => 'MENINGGAL',
                'nama' => 'Meninggal Dunia',
                'deskripsi' => 'Penduduk telah meninggal dunia',
                'is_active' => true,
            ],
        ]);

        // Seed Event Types
        DB::table('event_types')->insert([
            ['kode' => 'KELAHIRAN', 'nama' => 'Kelahiran', 'deskripsi' => 'Event kelahiran bayi', 'require_details' => true, 'is_active' => true],
            ['kode' => 'KEMATIAN', 'nama' => 'Kematian', 'deskripsi' => 'Event kematian penduduk', 'require_details' => true, 'is_active' => true],
            ['kode' => 'PINDAH', 'nama' => 'Pindah', 'deskripsi' => 'Event pindah keluar wilayah', 'require_details' => true, 'is_active' => true],
            ['kode' => 'DATANG', 'nama' => 'Datang', 'deskripsi' => 'Event datang/masuk ke wilayah', 'require_details' => true, 'is_active' => true],
            ['kode' => 'LAINNYA', 'nama' => 'Lainnya', 'deskripsi' => 'Event lainnya', 'require_details' => false, 'is_active' => true],
        ]);

        // Seed Hubungan Keluarga
        DB::table('hubungan_keluarga')->insert([
            ['kode' => 'KEPALA_KELUARGA', 'nama' => 'Kepala Keluarga', 'is_active' => true],
            ['kode' => 'SUAMI', 'nama' => 'Suami', 'is_active' => true],
            ['kode' => 'ISTRI', 'nama' => 'Istri', 'is_active' => true],
            ['kode' => 'ANAK', 'nama' => 'Anak', 'is_active' => true],
            ['kode' => 'MENANTU', 'nama' => 'Menantu', 'is_active' => true],
            ['kode' => 'CUCU', 'nama' => 'Cucu', 'is_active' => true],
            ['kode' => 'ORANGTUA', 'nama' => 'Orang Tua', 'is_active' => true],
            ['kode' => 'MERTUA', 'nama' => 'Mertua', 'is_active' => true],
            ['kode' => 'FAMILI', 'nama' => 'Famili Lain', 'is_active' => true],
            ['kode' => 'PEMBANTU', 'nama' => 'Pembantu Rumah Tangga', 'is_active' => true],
            ['kode' => 'LAINNYA', 'nama' => 'Lainnya', 'is_active' => true],
        ]);

        // NOTE: Jenis Surat data is now seeded by JenisSuratSeeder (hybrid template system)
        // See database/seeders/JenisSuratSeeder.php
    }
}
