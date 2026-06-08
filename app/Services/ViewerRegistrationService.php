<?php

namespace App\Services;

use App\Models\Penduduk;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ViewerRegistrationService
{
    public function checkNikAvailability(string $nik): array
    {
        if (!preg_match('/^\d{16}$/', $nik)) {
            return [
                'available' => false,
                'penduduk' => null,
                'message' => 'Format NIK tidak valid (harus 16 digit).',
            ];
        }

        $penduduk = Penduduk::query()
            ->with('rt.rw.desa')
            ->where('nik', $nik)
            ->where('status_kependudukan_code', 'AKTIF')
            ->whereNull('deleted_at')
            ->first();

        if (!$penduduk) {
            return [
                'available' => false,
                'penduduk' => null,
                'message' => 'NIK tidak ditemukan atau status tidak aktif.',
            ];
        }

        $existingUser = User::withTrashed()
            ->where('nik', $nik)
            ->first();

        if ($existingUser) {
            return [
                'available' => false,
                'penduduk' => $penduduk,
                'message' => 'NIK sudah terdaftar. Silakan login atau reset password.',
            ];
        }

        return [
            'available' => true,
            'penduduk' => $penduduk,
            'message' => 'NIK valid. Silakan lengkapi data registrasi.',
        ];
    }

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $check = $this->checkNikAvailability($data['nik']);

            if (!$check['available']) {
                throw new DomainException($check['message']);
            }

            $penduduk = $check['penduduk'];

            return User::create([
                'name' => $penduduk->nama_lengkap,
                'username' => $data['nik'],
                'nik' => $data['nik'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'viewer',
                'desa_id' => null,
                'rw_id' => null,
                'rt_id' => $penduduk->rt_id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        });
    }
}
