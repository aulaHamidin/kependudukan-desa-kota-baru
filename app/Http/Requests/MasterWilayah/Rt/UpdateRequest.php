<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterWilayah\Rt;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $rt = $this->route('rt');

        return $rt ? $this->user()?->can('update', $rt) : false;
    }

    public function rules(): array
    {
        /** @var \App\Models\Rt|null $rt */
        $rt = $this->route('rt');
        $rtId = $rt?->id;
        $rwId = $this->input('rw_id');

        return [
            'rw_id' => ['required', 'integer', Rule::exists('rws', 'id')->whereNull('deleted_at')],
            'nomor_rt' => [
                'required',
                'string',
                'regex:/^\d{3}$/',
                Rule::unique('rts')
                    ->ignore($rtId)
                    ->where('rw_id', $rwId)
                    ->whereNull('deleted_at'),
            ],
            'nama_ketua' => ['nullable', 'string', 'max:200'],
            'no_hp_ketua' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'rw_id.required' => 'RW wajib dipilih.',
            'rw_id.exists' => 'RW yang dipilih tidak valid.',
            'nomor_rt.required' => 'Nomor RT wajib diisi.',
            'nomor_rt.unique' => 'Nomor RT sudah digunakan pada RW ini.',
            'nomor_rt.regex' => 'Nomor RT harus 3 digit angka (contoh: 001).',
            'nama_ketua.max' => 'Nama ketua maksimal 200 karakter.',
            'no_hp_ketua.max' => 'No. HP ketua maksimal 20 karakter.',
        ];
    }

    protected function casts(): array
    {
        return [
            'rw_id' => 'int',
        ];
    }
}
