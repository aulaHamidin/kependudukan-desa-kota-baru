<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedWilayah extends Seeder
{
    public function run(): void
    {
        // ===== DESA 1: Desa Maju =====
        $desa1Id = DB::table('desas')->insertGetId([
            'kode_desa' => '3201012001',
            'nama' => 'Kota Baru',
            'kecamatan' => 'Kecamatan Martapura',
            'kabupaten' => 'Kabupaten Ogan Komering Ulu Timur',
            'provinsi' => 'Provinsi Sumatera Selatan',
            'kode_pos' => '32181',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // RW 001 - Desa Maju
        $rw1_1Id = DB::table('rws')->insertGetId([
            'desa_id' => $desa1Id,
            'nomor_rw' => '001',
            'nama_ketua' => 'Bapak Joko RW 01',
            'no_hp_ketua' => '081234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // RT 001 & 002 di RW 001
        DB::table('rts')->insert([
            [
                'rw_id' => $rw1_1Id,
                'nomor_rt' => '001',
                'nama_ketua' => 'Bapak Andi RT 001',
                'no_hp_ketua' => '081234567891',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rw_id' => $rw1_1Id,
                'nomor_rt' => '002',
                'nama_ketua' => 'Bapak Budi RT 002',
                'no_hp_ketua' => '081234567892',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // RW 002 - Desa Maju
        $rw1_2Id = DB::table('rws')->insertGetId([
            'desa_id' => $desa1Id,
            'nomor_rw' => '002',
            'nama_ketua' => 'Bapak Dedi RW 02',
            'no_hp_ketua' => '081234567893',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // RT 001 & 002 di RW 002
        DB::table('rts')->insert([
            [
                'rw_id' => $rw1_2Id,
                'nomor_rt' => '001',
                'nama_ketua' => 'Bapak Candra RT 001',
                'no_hp_ketua' => '081234567894',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rw_id' => $rw1_2Id,
                'nomor_rt' => '002',
                'nama_ketua' => 'Bapak Eko RT 002',
                'no_hp_ketua' => '081234567895',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
