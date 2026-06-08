<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\NikAvailableForRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ViewerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => [
                'required',
                'string',
                'size:16',
                'regex:/^\d{16}$/',
                new NikAvailableForRegistration(),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms_accepted' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'email.unique' => 'Email sudah terdaftar.',
            'terms_accepted.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ];
    }
}
