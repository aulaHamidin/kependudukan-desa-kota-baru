<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\MasterReference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class StoreRequest extends FormRequest
{
    abstract protected function getTable(): string;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:2',
                Rule::unique($this->getTable(), 'kode'),
            ],
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique($this->getTable(), 'nama'),
            ],
            'urutan' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode wajib diisi.',
            'kode.max' => 'Kode maksimal 2 karakter.',
            'kode.unique' => 'Kode sudah digunakan.',
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nama.unique' => 'Nama sudah digunakan.',
            'urutan.integer' => 'Urutan harus berupa angka.',
            'urutan.min' => 'Urutan minimal 1.',
            'is_active.boolean' => 'Status aktif harus bernilai true/false.',
        ];
    }
}
