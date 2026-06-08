<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Enums\StatusKelahiran;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreEventKelahiranRequest extends BaseRequest
{
    /**
     * Define type casts for validated fields.
     */
    protected function casts(): array
    {
        return [
            'rt_id' => 'int',
            'anak_ke' => 'int',
            'ayah_id' => 'int',
            'ibu_id' => 'int',
            'kk_tujuan_id' => 'int',
            'agama_id' => 'string',
            'berat_badan_kg' => 'float',
            'panjang_badan_cm' => 'float',
        ];
    }

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rt_id' => ['required', 'integer', 'exists:rts,id'],
            'event_date' => ['required', 'date', 'before_or_equal:today'],
            'keterangan' => ['nullable', 'string', 'max:1000'],

            // Data Bayi
            'nama_bayi' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'status_kelahiran' => ['required', Rule::enum(StatusKelahiran::class)],
            'agama_id' => ['required', 'exists:agamas,kode'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'jam_lahir' => ['nullable', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'anak_ke' => ['nullable', 'integer', 'min:1', 'max:20'],
            'berat_badan_kg'   => ['nullable', 'numeric', 'min:0.5', 'max:10'],
            'panjang_badan_cm' => ['nullable', 'numeric', 'min:20',  'max:80'],

            // Data Orang Tua - CRITICAL VALIDATION
            'ayah_id' => [
                'nullable',
                'integer',
                'exists:penduduks,id',
                'required_without:nama_ayah',
            ],
            'nama_ayah' => [
                'nullable',
                'string',
                'max:255',
                'required_without:ayah_id',
                'prohibited_unless:ayah_id,null',
            ],

            'ibu_id' => [
                'nullable',
                'integer',
                'exists:penduduks,id',
                'required_without:nama_ibu',
            ],
            'nama_ibu' => [
                'nullable',
                'string',
                'max:255',
                'required_without:ibu_id',
                'prohibited_unless:ibu_id,null',
            ],

            // Data Kelahiran
            'penolong_kelahiran' => ['nullable', Rule::in(['DOKTER', 'BIDAN', 'DUKUN', 'LAINNYA'])],
            'nama_penolong' => ['nullable', 'string', 'max:255'],

            // KK Tujuan - WAJIB
            'kk_tujuan_id' => ['required', 'integer', 'exists:kartu_keluargas,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'status_kelahiran.required' => 'Status kelahiran bayi wajib dipilih.',
            'status_kelahiran.in' => 'Status kelahiran harus HIDUP atau MATI.',
            'agama_id.required' => 'Agama bayi wajib dipilih.',
            'agama_id.exists' => 'Agama yang dipilih tidak valid.',

            'ayah_id.required_without' => 'Pilih ayah dari daftar penduduk atau isi nama ayah secara manual.',
            'nama_ayah.required_without' => 'Nama ayah wajib diisi jika ayah bukan penduduk desa.',
            'nama_ayah.prohibited_if' => 'Jangan isi nama ayah jika ayah sudah dipilih dari daftar penduduk.',

            'ibu_id.required_without' => 'Pilih ibu dari daftar penduduk atau isi nama ibu secara manual.',
            'nama_ibu.required_without' => 'Nama ibu wajib diisi jika ibu bukan penduduk desa.',
            'nama_ibu.prohibited_if' => 'Jangan isi nama ibu jika ibu sudah dipilih dari daftar penduduk.',

            'kk_tujuan_id.required' => 'KK tujuan wajib dipilih untuk bayi yang baru lahir.',
            'kk_tujuan_id.exists' => 'KK tujuan yang dipilih tidak valid.',
            'event_date.before_or_equal' => 'Tanggal kelahiran tidak boleh di masa depan.',
            'nama_bayi.required' => 'Nama bayi wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin bayi wajib dipilih.',
            'tempat_lahir.required' => 'Tempat lahir bayi wajib diisi.',
            'jam_lahir.regex' => 'Format jam lahir tidak valid. Contoh: 14:30 atau 9:15',
            'anak_ke.integer' => 'Anak ke harus berupa angka.',
            'anak_ke.min' => 'Anak ke minimal adalah 1.',
            'anak_ke.max' => 'Anak ke maksimal adalah 20.',
            'berat_badan_kg.min'    => 'Berat badan minimal adalah 0.5 kg.',
            'berat_badan_kg.max'    => 'Berat badan maksimal adalah 10 kg.',
            'panjang_badan_cm.min'  => 'Panjang badan minimal adalah 20 cm.',
            'panjang_badan_cm.max'  => 'Panjang badan maksimal adalah 80 cm.',
            'penolong_kelahiran.in' => 'Penolong kelahiran harus salah satu dari: DOKTER, BIDAN, DUKUN, LAINNYA.',
            'nama_penolong.max' => 'Nama penolong kelahiran maksimal 255 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize jam_lahir format: add leading zeros if needed
        if ($this->has('jam_lahir') && $this->jam_lahir) {
            $time = $this->jam_lahir;
            if (preg_match('/^(\d{1,2}):(\d{1,2})$/', $time, $matches)) {
                $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $minute = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $this->merge(['jam_lahir' => "$hour:$minute"]);
            }
        }

        // Mutual exclusivity: ayah_id vs nama_ayah
        // Jika ayah_id diisi → nama_ayah harus null (nama diambil dari data penduduk)
        // Jika nama_ayah diisi → ayah_id harus null (ayah bukan penduduk desa)
        if ($this->filled('ayah_id')) {
            $this->merge(['nama_ayah' => null]);
        } elseif ($this->filled('nama_ayah')) {
            $this->merge(['ayah_id' => null]);
        }

        // Mutual exclusivity: ibu_id vs nama_ibu
        if ($this->filled('ibu_id')) {
            $this->merge(['nama_ibu' => null]);
        } elseif ($this->filled('nama_ibu')) {
            $this->merge(['ibu_id' => null]);
        }
    }

    /**
     * Additional validation after basic rules pass
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate: Ayah & Ibu tidak boleh sama
            if ($this->ayah_id && $this->ibu_id && $this->ayah_id === $this->ibu_id) {
                $validator->errors()->add('ibu_id', 'Ayah dan ibu tidak boleh orang yang sama.');
            }

            // Validate: KK tujuan harus di RT yang sama dengan event
            $kkTujuan = \App\Models\KartuKeluarga::find($this->kk_tujuan_id);
            if ($kkTujuan && $kkTujuan->rt_id != $this->rt_id) {
                $validator->errors()->add('kk_tujuan_id', 'KK tujuan harus berada di RT yang sama dengan event kelahiran.');
            }

            // Validate: KK tujuan harus punya kepala keluarga aktif
            if ($this->kk_tujuan_id) {
                $hasKepala = \App\Models\KkMember::where('kartu_keluarga_id', $this->kk_tujuan_id)
                    ->where('status', 'AKTIF')
                    ->where('is_kepala_keluarga', true)
                    ->exists();

                if (!$hasKepala) {
                    $validator->errors()->add('kk_tujuan_id', 'KK tujuan tidak memiliki kepala keluarga aktif. Silakan perbaiki data KK terlebih dahulu.');
                }
            }

            // Validate: Jika orang tua adalah penduduk
            if ($this->ayah_id) {
                $ayah = \App\Models\Penduduk::find($this->ayah_id);

                if ($ayah) {
                    // Ayah harus punya KK aktif
                    $kkAyah = \App\Models\KkMember::where('penduduk_id', $ayah->id)
                        ->where('status', 'AKTIF')
                        ->first();

                    if (!$kkAyah) {
                        $validator->errors()->add('ayah_id', 'Ayah harus memiliki keanggotaan KK aktif.');
                    }

                    // RT ayah harus sama dengan RT event
                    if ($ayah->rt_id != $this->rt_id) {
                        $validator->errors()->add('ayah_id', 'RT ayah harus sama dengan RT kelahiran.');
                    }

                    // Ayah harus di KK yang sama dengan KK tujuan bayi
                    if ($kkAyah && $kkAyah->kartu_keluarga_id != $this->kk_tujuan_id) {
                        $validator->errors()->add('ayah_id', 'Ayah harus berada di KK yang sama dengan KK tujuan bayi.');
                    }

                    // Validasi umur ayah (opsional tapi recommended)
                    $umurAyah = \Carbon\Carbon::parse($this->event_date)->diffInYears($ayah->tgl_lahir);
                    if ($umurAyah < 16) {
                        $validator->errors()->add('ayah_id', "Umur ayah ({$umurAyah} tahun) terlalu muda untuk memiliki anak.");
                    }
                }
            }

            if ($this->ibu_id) {
                $ibu = \App\Models\Penduduk::find($this->ibu_id);

                if ($ibu) {
                    // Ibu harus punya KK aktif
                    $kkIbu = \App\Models\KkMember::where('penduduk_id', $ibu->id)
                        ->where('status', 'AKTIF')
                        ->first();

                    if (!$kkIbu) {
                        $validator->errors()->add('ibu_id', 'Ibu harus memiliki keanggotaan KK aktif.');
                    }

                    // RT ibu harus sama dengan RT event
                    if ($ibu->rt_id != $this->rt_id) {
                        $validator->errors()->add('ibu_id', 'RT ibu harus sama dengan RT kelahiran.');
                    }

                    // Ibu harus di KK yang sama dengan KK tujuan bayi
                    if ($kkIbu && $kkIbu->kartu_keluarga_id != $this->kk_tujuan_id) {
                        $validator->errors()->add('ibu_id', 'Ibu harus berada di KK yang sama dengan KK tujuan bayi.');
                    }

                    // Validasi umur ibu (biologis range)
                    $umurIbu = \Carbon\Carbon::parse($this->event_date)->diffInYears($ibu->tgl_lahir);
                    if ($umurIbu < 15 || $umurIbu > 55) {
                        $validator->errors()->add('ibu_id', "Umur ibu ({$umurIbu} tahun) tidak dalam rentang normal untuk melahirkan (15-55 tahun).");
                    }
                }
            }
        });
    }
}
