<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterWilayah\Desa;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $desa = $this->route('desa');

        return $desa ? $this->user()?->can('update', $desa) : false;
    }

    public function rules(): array
    {
        /** @var \App\Models\Desa|null $desa */
        $desa = $this->route('desa');
        $desaId = $desa?->id;

        return [
            'kode_desa' => [
                'required',
                'string',
                'max:20',
                Rule::unique('desas')
                    ->ignore($desaId)
                    ->whereNull('deleted_at'),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kabupaten' => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kode_pos' => ['required', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_desa.required' => 'Kode desa wajib diisi.',
            'kode_desa.unique' => 'Kode desa sudah digunakan.',
            'kode_desa.max' => 'Kode desa maksimal 20 karakter.',
            'nama.required' => 'Nama desa wajib diisi.',
            'nama.max' => 'Nama desa maksimal 100 karakter.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'kecamatan.max' => 'Kecamatan maksimal 100 karakter.',
            'kabupaten.required' => 'Kabupaten wajib diisi.',
            'kabupaten.max' => 'Kabupaten maksimal 100 karakter.',
            'provinsi.required' => 'Provinsi wajib diisi.',
            'provinsi.max' => 'Provinsi maksimal 100 karakter.',
            'kode_pos.required' => 'Kode pos wajib diisi.',
            'kode_pos.max' => 'Kode pos maksimal 10 karakter.',
        ];
    }

    protected function casts(): array
    {
        return [];
    }
}
