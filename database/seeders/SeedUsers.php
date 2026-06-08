<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SeedUsers extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $desa1Id = $this->getDesaId('3201012001');

        $rw1_1Id = $this->getRwId($desa1Id, '001');
        $rw1_2Id = $this->getRwId($desa1Id, '002');

        $rt1_1_1Id = $this->getRtId($rw1_1Id, '001');
        $rt1_1_2Id = $this->getRtId($rw1_1Id, '002');

        // 1. SUPER ADMIN (tidak terikat wilayah)
        DB::table('users')->insert([
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => $password,
            'role' => 'super_admin',
            'desa_id' => null,
            'rw_id' => null,
            'rt_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. ADMIN DESA 1 (hanya desa_id)
        DB::table('users')->insert([
            'name' => 'Admin Desa Kota Baru',
            'username' => 'admin_desa_kotabaru',
            'email' => 'admin.desa.kotabaru@example.com',
            'password' => $password,
            'role' => 'admin_desa',
            'desa_id' => $desa1Id,
            'rw_id' => null,
            'rt_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. ADMIN RW (Desa 1, RW 01) - hanya rw_id
        DB::table('users')->insert([
            'name' => 'Admin RW 01 Desa Kota Baru',
            'username' => 'admin_rw01_kotabaru',
            'email' => 'admin.rw01.kotabaru@example.com',
            'password' => $password,
            'role' => 'admin_rw',
            'desa_id' => null,
            'rw_id' => $rw1_1Id,
            'rt_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. ADMIN RW (Desa 1, RW 002)
        DB::table('users')->insert([
            'name' => 'Admin RW 002 Desa Kota Baru',
            'username' => 'admin_rw002_kotabaru',
            'email' => 'admin.rw002.kotabaru@example.com',
            'password' => $password,
            'role' => 'admin_rw',
            'desa_id' => null,
            'rw_id' => $rw1_2Id,
            'rt_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. ADMIN RT (Desa 1, RW 001, RT 001) - hanya rt_id
        DB::table('users')->insert([
            'name' => 'Admin RT 001/001 Desa Kota Baru',
            'username' => 'admin_rt001001_kotabaru',
            'email' => 'admin.rt001001.kotabaru@example.com',
            'password' => $password,
            'role' => 'admin_rt',
            'desa_id' => null,
            'rw_id' => null,
            'rt_id' => $rt1_1_1Id,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. ADMIN RT (Desa 1, RW 001, RT 002)
        DB::table('users')->insert([
            'name' => 'Admin RT 001/002 Desa Kota Baru',
            'username' => 'admin_rt001002_kotabaru',
            'email' => 'admin.rt001002.kotabaru@example.com',
            'password' => $password,
            'role' => 'admin_rt',
            'desa_id' => null,
            'rw_id' => null,
            'rt_id' => $rt1_1_2Id,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getDesaId(string $kodeDesa): int
    {
        $id = DB::table('desas')->where('kode_desa', $kodeDesa)->value('id');

        if (!$id) {
            throw new RuntimeException('Desa tidak ditemukan untuk kode: ' . $kodeDesa);
        }

        return (int) $id;
    }

    private function getRwId(int $desaId, string $nomorRw): int
    {
        $id = DB::table('rws')
            ->where('desa_id', $desaId)
            ->where('nomor_rw', $nomorRw)
            ->value('id');

        if (!$id) {
            throw new RuntimeException('RW tidak ditemukan untuk desa_id ' . $desaId . ' nomor ' . $nomorRw);
        }

        return (int) $id;
    }

    private function getRtId(int $rwId, string $nomorRt): int
    {
        $id = DB::table('rts')
            ->where('rw_id', $rwId)
            ->where('nomor_rt', $nomorRt)
            ->value('id');

        if (!$id) {
            throw new RuntimeException('RT tidak ditemukan untuk rw_id ' . $rwId . ' nomor ' . $nomorRt);
        }

        return (int) $id;
    }
}
