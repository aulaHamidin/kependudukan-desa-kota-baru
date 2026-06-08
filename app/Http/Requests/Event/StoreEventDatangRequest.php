<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreEventDatangRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Event::class);
    }

    public function rules(): array
    {
        $rules = [
            // Event Datang
            'jenis_kedatangan' => 'required|in:pendatang_baru,pindah_masuk,kembali',
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
            'nik' => [
                'required',
                'string',
                'size:16',
                'regex:/^[0-9]{16}$/',
                Rule::unique('penduduks', 'nik')->whereNull('deleted_at'),
            ],
            'nama_lengkap' => 'required|string|max:200',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:100',
            'tgl_lahir' => 'required|date|before:today',
            'agama_id' => 'required|exists:agamas,kode',
            'status_perkawinan' => 'required|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati',
            'rt_id' => [
                'required',
                'exists:rts,id',
                function ($attribute, $value, $fail) {
                    $kkTujuanId = $this->input('kk_tujuan_id');

                    // Skip validation if no KK selected
                    if (!$kkTujuanId) {
                        return;
                    }

                    $kk = \App\Models\KartuKeluarga::find($kkTujuanId);
                    if ($kk && $kk->rt_id != $value) {
                        return $fail('RT penduduk harus sama dengan RT dari KK yang dipilih. Silakan sesuaikan RT penduduk dengan KK yang dipilih.');
                    }
                },
            ],

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
                Rule::unique('penduduks', 'no_hp')->whereNull('deleted_at'),
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('penduduks', 'email')->whereNull('deleted_at'),
            ],
        ];

        // Conditional rules for pindah_masuk
        if ($this->input('jenis_kedatangan') === 'pindah_masuk') {
            $rules['no_surat_pindah'] = 'required|string|max:50';
            $rules['tanggal_surat_pindah'] = 'required|date|before_or_equal:event_date';
        }

        // Conditional rules for kembali — penduduk_id wajib dan tidak perlu data penduduk baru
        if ($this->input('jenis_kedatangan') === 'kembali') {
            $rules['penduduk_id'] = 'required|integer|exists:penduduks,id';
            // NIK tidak wajib untuk KEMBALI karena penduduk sudah ada
            $rules['nik'] = 'nullable|string|size:16|regex:/^[0-9]{16}$/';
            // Data penduduk tidak wajib untuk KEMBALI (sudah ada di DB)
            $rules['nama_lengkap'] = 'nullable|string|max:200';
            $rules['jenis_kelamin'] = 'nullable|in:L,P';
            $rules['tempat_lahir'] = 'nullable|string|max:100';
            $rules['tgl_lahir'] = 'nullable|date|before:today';
            $rules['agama_id'] = 'nullable|exists:agamas,kode';
            $rules['status_perkawinan'] = 'nullable|in:Belum Kawin,Kawin,Cerai Hidup,Cerai Mati';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'jenis_kedatangan.required' => 'Jenis kedatangan wajib dipilih',
            'event_date.required' => 'Tanggal datang wajib diisi',
            'event_date.before_or_equal' => 'Tanggal datang tidak boleh di masa depan',
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus 16 digit',
            'nik.regex' => 'NIK harus berupa angka',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            'no_surat_pindah.required' => 'Nomor surat pindah wajib diisi untuk Pindah Masuk',
            'tanggal_surat_pindah.required' => 'Tanggal surat pindah wajib diisi untuk Pindah Masuk',
            'tanggal_surat_pindah.before_or_equal' => 'Tanggal surat pindah tidak boleh setelah tanggal datang',
            'alamat_asal.required' => 'Alamat asal wajib diisi',
            'alasan_datang.required' => 'Alasan datang wajib diisi',
            'keterangan_alasan.max' => 'Keterangan alasan maksimal 1000 karakter',
            'agama_id.required' => 'Agama wajib dipilih',
            'agama_id.exists' => 'Agama yang dipilih tidak valid',
            'status_perkawinan.required' => 'Status perkawinan wajib dipilih',
            'status_perkawinan.in' => 'Status perkawinan tidak valid',
            'rt_id.required' => 'RT tujuan wajib dipilih',
            'rt_id.exists' => 'RT tujuan yang dipilih tidak valid',
            'kk_tujuan_id.exists' => 'KK tujuan yang dipilih tidak valid',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi',
            'tempat_lahir.max' => 'Tempat lahir maksimal 100 karakter',
            'tgl_lahir.required' => 'Tanggal lahir wajib diisi',
            'tgl_lahir.date' => 'Tanggal lahir tidak valid',
            'tgl_lahir.before' => 'Tanggal lahir harus sebelum hari ini',
            'nama_ayah.max' => 'Nama ayah maksimal 200 karakter',
            'nama_ibu.max' => 'Nama ibu maksimal 200 karakter',
            'pendidikan_id.exists' => 'Pendidikan yang dipilih tidak valid',
            'pekerjaan_id.exists' => 'Pekerjaan yang dipilih tidak valid',
            'pendapatan_range_id.exists' => 'Pendapatan yang dipilih tidak valid',
            'golongan_darah_id.exists' => 'Golongan darah yang dipilih tidak valid',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter',
            'no_hp.regex' => 'Nomor HP harus berupa angka dan boleh diawali +',
            'no_hp.unique' => 'Nomor HP sudah terdaftar oleh penduduk lain',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 100 karakter',
            'email.unique' => 'Email sudah terdaftar oleh penduduk lain',
            'nik.unique' => 'NIK sudah terdaftar',
            'nama_lengkap.max' => 'Nama lengkap maksimal 200 karakter',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid',
            'alamat_asal.max' => 'Alamat asal maksimal 500 karakter',
            'alasan_datang.max' => 'Alasan datang maksimal 200 karakter',
            'no_surat_pindah.max' => 'Nomor surat pindah maksimal 50 karakter',
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
