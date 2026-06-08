<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\StatusKependudukan;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StatusKependudukanStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:20|unique:status_kependudukan,kode',
            'nama' => 'required|string|max:50|unique:status_kependudukan,nama',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode status wajib diisi.',
            'kode.max' => 'Kode status maksimal 20 karakter.',
            'kode.unique' => 'Kode status sudah digunakan.',
            'nama.required' => 'Nama status wajib diisi.',
            'nama.max' => 'Nama status maksimal 50 karakter.',
            'nama.unique' => 'Nama status sudah digunakan.',
            'is_active.boolean' => 'Status aktif harus bernilai true/false.',
        ];
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'bool',
        ];
    }
}
