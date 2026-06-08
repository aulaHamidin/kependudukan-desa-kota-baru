<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->whereNull('deleted_at'),
            ],
            'nik' => [
                'nullable',
                'string',
                'size:16',
                'regex:/^\d{16}$/',
                Rule::unique('users', 'nik')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                Rule::in(['super_admin', 'admin_desa', 'admin_rw', 'admin_rt', 'viewer']),
            ],
            'desa_id' => ['nullable', 'integer', 'exists:desas,id'],
            'rw_id' => ['nullable', 'integer', 'exists:rws,id'],
            'rt_id' => ['nullable', 'integer', 'exists:rts,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'username.required' => 'Username harus diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.alpha_dash' => 'Username hanya boleh mengandung huruf, angka, dash, dan underscore.',
            'username.unique' => 'Username sudah terdaftar.',
            'nik.string' => 'NIK harus berupa teks.',
            'nik.size' => 'NIK harus terdiri dari 16 digit.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password harus diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'role.required' => 'Role harus dipilih.',
            'role.in' => 'Role tidak valid.',
            'desa_id.integer' => 'Desa harus berupa angka.',
            'desa_id.exists' => 'Desa tidak ditemukan.',
            'rw_id.integer' => 'RW harus berupa angka.',
            'rw_id.exists' => 'RW tidak ditemukan.',
            'rt_id.integer' => 'RT harus berupa angka.',
            'rt_id.exists' => 'RT tidak ditemukan.',
            'is_active.boolean' => 'Status aktif harus berupa nilai boolean.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            $desaId = $this->input('desa_id');
            $rwId = $this->input('rw_id');
            $rtId = $this->input('rt_id');

            if ($role === 'super_admin' && ($desaId || $rwId || $rtId)) {
                $validator->errors()->add('role', 'Super admin tidak boleh memiliki wilayah.');
            }

            if ($role === 'admin_desa' && (!$desaId || $rwId || $rtId)) {
                $validator->errors()->add('desa_id', 'Admin desa harus memiliki desa_id saja.');
            }

            if ($role === 'admin_rw' && ($desaId || !$rwId || $rtId)) {
                $validator->errors()->add('rw_id', 'Admin RW harus memiliki rw_id saja.');
            }

            if (in_array($role, ['admin_rt', 'viewer'], true) && ($desaId || $rwId || !$rtId)) {
                $validator->errors()->add('rt_id', 'Admin RT/Viewer harus memiliki rt_id saja.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'desa_id' => 'int',
            'rw_id' => 'int',
            'rt_id' => 'int',
            'is_active' => 'bool',
        ];
    }
}
