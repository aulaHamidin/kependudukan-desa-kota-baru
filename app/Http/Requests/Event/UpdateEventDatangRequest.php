<?php

declare(strict_types = 1)
;

namespace App\Http\Requests\Event;

use App\Http\Requests\BaseRequest;

class UpdateEventDatangRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user()->can('update', $event);
    }

    public function rules(): array
    {
        $event = $this->route('event');
        $user = $this->user();

        $rules = [
            // Event Datang
            'event_date' => 'required|date|before_or_equal:today',
            'alamat_asal' => 'required|string|max:500',
            'kk_tujuan_id' => [
                'nullable',
                'exists:kartu_keluargas,id',
                function ($attribute, $value, $fail) {
            // Skip validation if no KK selected
            if (!$value) {
                return;
            }

            $rtId = $this->input('rt_id');
            if (!$rtId) {
                return;
            }

            $kk = \App\Models\KartuKeluarga::find($value);
            if ($kk && $kk->rt_id != $rtId) {
                return $fail('KK yang dipilih harus berada di RT yang sama dengan RT penduduk. Silakan pilih KK yang sesuai atau ubah RT penduduk.');
            }
        },
            ],
            'alasan_datang' => 'required|string|max:200',
            'keterangan_alasan' => 'nullable|string|max:1000',

            // Data Penduduk Wajib
            'nama_lengkap' => 'required|string|max:200',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tgl_lahir' => 'required|date|before:today',
            'agama_id' => 'required|exists:agamas,kode',
            'status_perkawinan' => 'required|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati',

            // Data Penduduk Opsional
            'nama_ayah' => 'nullable|string|max:200',
            'nama_ibu' => 'nullable|string|max:200',
            'pendidikan_id' => 'nullable|exists:pendidikans,kode',
            'pekerjaan_id' => 'nullable|exists:pekerjaans,kode',
            'pendapatan_range_id' => 'nullable|exists:pendapatan_ranges,id',
            'golongan_darah_id' => 'nullable|exists:golongan_darahs,kode',
            'no_hp' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+]{10,20}$/',
                'unique:penduduks,no_hp,' . ($event->penduduk->id ?? 'NULL') . ',id,deleted_at,NULL',
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
                'unique:penduduks,email,' . ($event->penduduk->id ?? 'NULL') . ',id,deleted_at,NULL',
            ],
        ];

        // RT validation: immutable - harus sama dengan RT event asli.
        // Service (DatangService::updateEventDatang) juga menolak perubahan RT.
        // Validasi ini memastikan error message yang konsisten dan informatif di layer Request.
        $rules['rt_id'] = [
            'required',
            'exists:rts,id',
            function ($attribute, $value, $fail) use ($event) {
            // Type check: ensure $event is Event model
            if (!($event instanceof \App\Models\Event)) {
                return $fail('Data event tidak valid.');
            }

            // RT tidak boleh diubah sama sekali (business rule: hapus & buat ulang jika RT salah)
            if ((int)$value !== (int)$event->rt_id) {
                return $fail('RT tidak dapat diubah. Hapus event dan buat ulang jika RT salah.');
            }

            // KK consistency validation: RT penduduk harus sama dengan RT KK
            $kkTujuanId = $this->input('kk_tujuan_id');
            if ($kkTujuanId) {
                $kk = \App\Models\KartuKeluarga::find($kkTujuanId);
                if ($kk && $kk->rt_id != $value) {
                    return $fail('RT penduduk harus sama dengan RT dari KK yang dipilih. Silakan sesuaikan RT penduduk dengan KK yang dipilih.');
                }
            }
        },
        ];

        // Conditional rules for pindah_masuk
        if ($this->input('jenis_kedatangan') === 'pindah_masuk') {
            $rules['no_surat_pindah'] = 'required|string|max:50';
            $rules['tanggal_surat_pindah'] = 'required|date|before_or_equal:event_date';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'jenis_kedatangan.required' => 'Jenis kedatangan wajib dipilih',
            'event_date.required' => 'Tanggal datang wajib diisi',
            'event_date.before_or_equal' => 'Tanggal datang tidak boleh melebihi hari ini',
            'alamat_asal.required' => 'Alamat asal wajib diisi',
            'alamat_asal.max' => 'Alamat asal maksimal 500 karakter',
            'kk_tujuan_id.exists' => 'Kartu Keluarga tujuan tidak ditemukan',
            'alasan_datang.required' => 'Alasan datang wajib diisi',
            'alasan_datang.max' => 'Alasan datang maksimal 200 karakter',
            'keterangan_alasan.max' => 'Keterangan alasan maksimal 1000 karakter',
            'no_surat_pindah.required' => 'Nomor surat pindah wajib diisi untuk Pindah Masuk',
            'no_surat_pindah.max' => 'Nomor surat pindah maksimal 50 karakter',
            'tanggal_surat_pindah.required' => 'Tanggal surat pindah wajib diisi untuk Pindah Masuk',
            'tanggal_surat_pindah.before_or_equal' => 'Tanggal surat pindah tidak boleh setelah tanggal datang',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'nama_lengkap.max' => 'Nama lengkap maksimal 200 karakter',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi',
            'tempat_lahir.max' => 'Tempat lahir maksimal 100 karakter',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi',
            'tgl_lahir.before' => 'Tanggal lahir harus sebelum hari ini',
            'agama_id.required' => 'Agama wajib dipilih',
            'agama_id.exists' => 'Agama tidak ditemukan',
            'status_perkawinan.required' => 'Status perkawinan wajib dipilih',
            'status_perkawinan.in' => 'Status perkawinan tidak valid',
            'rt_id.required' => 'RT tujuan wajib dipilih',
            'rt_id.exists' => 'RT tujuan tidak ditemukan',
            'nama_ayah.max' => 'Nama ayah maksimal 200 karakter',
            'nama_ibu.max' => 'Nama ibu maksimal 200 karakter',
            'pendidikan_id.exists' => 'Pendidikan tidak ditemukan',
            'pekerjaan_id.exists' => 'Pekerjaan tidak ditemukan',
            'pendapatan_range_id.exists' => 'Rentang pendapatan tidak ditemukan',
            'golongan_darah_id.exists' => 'Golongan darah tidak ditemukan',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter',
            'no_hp.regex' => 'Nomor HP harus berupa angka dan dapat diawali dengan +, dengan panjang 10-20 karakter',
            'no_hp.unique' => 'Nomor HP sudah terdaftar oleh penduduk lain',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 100 karakter',
            'email.unique' => 'Email sudah terdaftar oleh penduduk lain',
        ];
    }

    protected function casts(): array
    {
        return [
            'kk_tujuan_id' => 'int',
            'rt_id' => 'int',
            'pendapatan_range_id' => 'int',
        ];
    }
}
