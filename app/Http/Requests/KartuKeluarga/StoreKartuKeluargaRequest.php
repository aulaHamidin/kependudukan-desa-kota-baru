<?php

declare(strict_types=1);

namespace App\Http\Requests\KartuKeluarga;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreKartuKeluargaRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\KartuKeluarga::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'no_kk' => [
                'required',
                'string',
                'size:16',
                'regex:/^\d{16}$/',
                Rule::unique('kartu_keluargas', 'no_kk')
                    ->whereNull('deleted_at'),
            ],
            'alamat' => ['required', 'string', 'max:500'],
            'rt_id' => ['required', 'integer', Rule::exists('rts', 'id')->whereNull('deleted_at')],
            'status_kk' => ['required', 'string', 'in:AKTIF,NON_AKTIF'],
            'tanggal_terbentuk' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_kk.required' => 'No. KK wajib diisi.',
            'no_kk.size' => 'No. KK harus terdiri dari 16 digit.',
            'no_kk.regex' => 'No. KK harus berupa 16 digit angka.',
            'no_kk.unique' => 'No. KK sudah terdaftar.',
            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'rt_id.required' => 'RT wajib dipilih.',
            'rt_id.exists' => 'RT tidak ditemukan.',
            'status_kk.required' => 'Status KK wajib dipilih.',
            'status_kk.in' => 'Status KK tidak valid.',
            'tanggal_terbentuk.required' => 'Tanggal terbentuk wajib diisi.',
            'tanggal_terbentuk.date' => 'Tanggal terbentuk harus berupa tanggal yang valid.',
            'tanggal_terbentuk.before_or_equal' => 'Tanggal terbentuk tidak boleh di masa depan.',
        ];
    }

    protected function casts(): array
    {
        return [
            'rt_id' => 'int',
        ];
    }
}
