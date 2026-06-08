<?php

declare(strict_types=1);

namespace App\Http\Requests\DataInti\KkMember;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $member = $this->route('kk_member');

        return $member ? ($this->user()?->can('update', $member) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'kartu_keluarga_id' => ['required', 'integer', Rule::exists('kartu_keluargas', 'id')->whereNull('deleted_at')],
            'penduduk_id' => ['required', 'integer', Rule::exists('penduduks', 'id')->whereNull('deleted_at')],
            'hubungan_keluarga_code' => ['required', 'string', Rule::exists('hubungan_keluarga', 'kode')],
            'is_kepala_keluarga' => ['boolean'],
            'tanggal_masuk' => ['required', 'date'],
            'tanggal_keluar' => ['nullable', 'date', 'after_or_equal:tanggal_masuk'],
            'status' => ['nullable', 'string', 'max:20'],
            'kk_asal_id' => ['nullable', 'integer', Rule::exists('kartu_keluargas', 'id')->whereNull('deleted_at')],
            'event_keluar_id' => ['nullable', 'integer', Rule::exists('events', 'id')],
            'alasan_keluar' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'kartu_keluarga_id.required' => 'Kartu keluarga wajib dipilih.',
            'kartu_keluarga_id.exists' => 'Kartu keluarga tidak ditemukan.',
            'penduduk_id.required' => 'Penduduk wajib dipilih.',
            'penduduk_id.exists' => 'Penduduk tidak ditemukan.',
            'hubungan_keluarga_code.required' => 'Hubungan keluarga wajib dipilih.',
            'hubungan_keluarga_code.exists' => 'Hubungan keluarga tidak valid.',
            'is_kepala_keluarga.boolean' => 'Kepala keluarga harus berupa nilai boolean.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_keluar.after_or_equal' => 'Tanggal keluar harus setelah atau sama dengan tanggal masuk.',
            'status.max' => 'Status anggota maksimal 20 karakter.',
            'kk_asal_id.exists' => 'KK asal tidak ditemukan.',
            'event_keluar_id.exists' => 'Event keluar tidak valid.',
            'alasan_keluar.max' => 'Alasan keluar maksimal 500 karakter.',
        ];
    }

    protected function casts(): array
    {
        return [
            'kartu_keluarga_id' => 'int',
            'penduduk_id' => 'int',
            'is_kepala_keluarga' => 'bool',
            'kk_asal_id' => 'int',
            'event_keluar_id' => 'int',
        ];
    }
}
