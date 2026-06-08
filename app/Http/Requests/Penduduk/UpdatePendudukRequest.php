<?php

declare(strict_types=1);

namespace App\Http\Requests\Penduduk;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * UpdatePendudukRequest - Validation for updating penduduk data
 * 
 * Allows admin_desa to edit all fields including NIK and core identity data.
 * Validates NIK uniqueness (except current penduduk).
 * 
 * @author System Generator
 * @since 2026-02-27
 */
class UpdatePendudukRequest extends BaseRequest
{
    public function authorize(): bool
    {
        // Authorization handled by policy in controller
        return true;
    }

    public function rules(): array
    {
        $penduduk = $this->route('penduduk');

        if (!$penduduk instanceof \App\Models\Penduduk) {
            return [];
        }

        $pendudukId = $penduduk->id;

        return [
            // Data Identitas (editable by admin_desa)
            'nik' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]{16}$/',
                Rule::unique('penduduks', 'nik')
                    ->ignore($pendudukId)
                    ->whereNull('deleted_at'),
            ],
            'nama_lengkap' => ['required', 'string', 'max:200'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tgl_lahir' => ['required', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'agama_id' => ['required', 'string', 'exists:agamas,kode'],

            // Data Keluarga
            'nama_ayah' => ['nullable', 'string', 'max:200'],
            'nama_ibu' => ['nullable', 'string', 'max:200'],
            'status_perkawinan' => [
                'nullable',
                'in:BELUM_KAWIN,KAWIN,CERAI_HIDUP,CERAI_MATI'
            ],
            'kewarganegaraan' => ['nullable', 'string', 'max:50'],

            // Data Pendidikan & Pekerjaan
            'pendidikan_id' => ['nullable', 'string', 'exists:pendidikans,kode'],
            'pekerjaan_id' => ['nullable', 'string', 'exists:pekerjaans,kode'],
            'pendapatan_range_id' => ['nullable', 'integer', 'exists:pendapatan_ranges,id'],
            'golongan_darah_id' => ['nullable', 'string', 'exists:golongan_darahs,kode'],

            // Data Kontak
            'no_hp' => [
                'nullable',
                'string',
                'regex:/^[\+]?[0-9]{10,20}$/',
                Rule::unique('penduduks', 'no_hp')
                    ->ignore($pendudukId)
                    ->whereNull('deleted_at'),
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('penduduks', 'email')
                    ->ignore($pendudukId)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus 16 digit.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar untuk penduduk lain.',

            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 200 karakter.',

            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tgl_lahir.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',

            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',

            'agama_id.required' => 'Agama wajib dipilih.',
            'agama_id.exists' => 'Agama yang dipilih tidak valid.',

            'no_hp.regex' => 'Format nomor HP tidak valid (10-20 digit).',
            'no_hp.unique' => 'Nomor HP sudah terdaftar oleh penduduk lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar oleh penduduk lain.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nik' => 'NIK',
            'nama_lengkap' => 'nama lengkap',
            'tempat_lahir' => 'tempat lahir',
            'tgl_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'agama_id' => 'agama',
            'nama_ayah' => 'nama ayah',
            'nama_ibu' => 'nama ibu',
            'status_perkawinan' => 'status perkawinan',
            'kewarganegaraan' => 'kewarganegaraan',
            'pendidikan_id' => 'pendidikan',
            'pekerjaan_id' => 'pekerjaan',
            'pendapatan_range_id' => 'pendapatan',
            'golongan_darah_id' => 'golongan darah',
            'no_hp' => 'nomor HP',
            'email' => 'email',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Clean NIK (remove spaces/dashes)
        if ($this->has('nik') && is_string($this->nik)) {
            $this->merge([
                'nik' => preg_replace('/[^0-9]/', '', $this->nik)
            ]);
        }

        // Clean phone number
        if ($this->has('no_hp') && is_string($this->no_hp)) {
            $this->merge([
                'no_hp' => preg_replace('/[\s\-\(\)]/', '', $this->no_hp)
            ]);
        }

        // Trim text fields
        $textFields = ['nama_lengkap', 'tempat_lahir', 'nama_ayah', 'nama_ibu', 'kewarganegaraan', 'email'];
        foreach ($textFields as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $this->merge([$field => trim($this->$field)]);
            }
        }
    }
}
