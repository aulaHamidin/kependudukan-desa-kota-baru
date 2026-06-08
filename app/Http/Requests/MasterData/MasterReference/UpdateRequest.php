<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\MasterReference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class UpdateRequest extends FormRequest
{
    abstract protected function getTable(): string;

    abstract protected function getRouteKey(): string;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kode = (string) $this->route($this->getRouteKey());

        return [
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique($this->getTable(), 'nama')->ignore($kode, 'kode'),
            ],
            'urutan' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nama.unique' => 'Nama sudah digunakan.',
            'urutan.integer' => 'Urutan harus berupa angka.',
            'urutan.min' => 'Urutan minimal 1.',
            'is_active.boolean' => 'Status aktif harus bernilai true/false.',
        ];
    }
}
