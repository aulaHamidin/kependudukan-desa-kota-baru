<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterWilayah\Rw;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $rw = $this->route('rw');

        return $rw ? $this->user()?->can('update', $rw) : false;
    }

    public function rules(): array
    {
        /** @var \App\Models\Rw|null $rw */
        $rw = $this->route('rw');
        $rwId = $rw?->id;
        $desaId = $this->input('desa_id');

        return [
            'desa_id' => ['required', 'integer', Rule::exists('desas', 'id')->whereNull('deleted_at')],
            'nomor_rw' => [
                'required',
                'string',
                'regex:/^\d{3}$/',
                Rule::unique('rws')
                    ->ignore($rwId)
                    ->where('desa_id', $desaId)
                    ->whereNull('deleted_at'),
            ],
            'nama_ketua' => ['nullable', 'string', 'max:200'],
            'no_hp_ketua' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'desa_id.required' => 'Desa wajib dipilih.',
            'desa_id.exists' => 'Desa yang dipilih tidak valid.',
            'nomor_rw.required' => 'Nomor RW wajib diisi.',
            'nomor_rw.unique' => 'Nomor RW sudah digunakan pada desa ini.',
            'nomor_rw.regex' => 'Nomor RW harus 3 digit angka (contoh: 001).',
            'nama_ketua.max' => 'Nama ketua maksimal 200 karakter.',
            'no_hp_ketua.max' => 'No. HP ketua maksimal 20 karakter.',
        ];
    }

    protected function casts(): array
    {
        return [
            'desa_id' => 'int',
        ];
    }
}
