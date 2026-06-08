<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\StatusKependudukan;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StatusKependudukanUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kode = (string) $this->route('status');

        return [
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('status_kependudukan', 'nama')->ignore($kode, 'kode'),
            ],
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
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
