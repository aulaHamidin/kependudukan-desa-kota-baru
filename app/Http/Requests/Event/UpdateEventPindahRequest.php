<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateEventPindahRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event
            ? ($this->user()?->can('update', $event) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'event_date'         => ['required', 'date', 'before_or_equal:today'],
            'keterangan'         => ['nullable', 'string', 'max:1000'],

            'alamat_tujuan'      => ['required', 'string', 'max:500'],
            'rt_tujuan'          => ['nullable', 'string', 'max:10'],
            'rw_tujuan'          => ['nullable', 'string', 'max:10'],
            'desa_tujuan'        => ['nullable', 'string', 'max:255'],
            'kecamatan_tujuan'   => ['required', 'string', 'max:255'],
            'kabupaten_tujuan'   => ['required', 'string', 'max:255'],
            'provinsi_tujuan'    => ['required', 'string', 'max:255'],
            'kode_pos_tujuan'    => ['nullable', 'string', 'max:10'],

            'alasan_pindah'      => ['required', Rule::in([
                'PEKERJAAN', 'PENDIDIKAN', 'KEAMANAN',
                'KESEHATAN', 'PERKAWINAN', 'LAINNYA',
            ])],
            'keterangan_alasan'  => ['nullable', 'string', 'max:1000'],

            // NOTE: penduduk_id, kk_id, rt_id, jenis_kepindahan, pengganti_kepala_id
            // TIDAK boleh diubah saat update — hanya data tujuan & alasan yang bisa diedit
        ];
    }

    public function messages(): array
    {
        return [
            'event_date.required'        => 'Tanggal pindah wajib diisi.',
            'event_date.before_or_equal' => 'Tanggal pindah tidak boleh di masa depan.',
            'alamat_tujuan.required'     => 'Alamat tujuan wajib diisi.',
            'alamat_tujuan.max'          => 'Alamat tujuan maksimal 500 karakter.',
            'kecamatan_tujuan.required'  => 'Kecamatan tujuan wajib diisi.',
            'kabupaten_tujuan.required'  => 'Kabupaten tujuan wajib diisi.',
            'provinsi_tujuan.required'   => 'Provinsi tujuan wajib diisi.',
            'alasan_pindah.required'     => 'Alasan pindah wajib dipilih.',
            'alasan_pindah.in'           => 'Alasan pindah tidak valid.',
            'keterangan_alasan.max'      => 'Keterangan alasan maksimal 1000 karakter.',
        ];
    }

    protected function casts(): array
    {
        return [];
    }
}