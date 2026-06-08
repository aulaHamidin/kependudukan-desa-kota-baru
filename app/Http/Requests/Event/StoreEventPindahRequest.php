<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Models\KkMember;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreEventPindahRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rt_id'       => ['required', 'integer', 'exists:rts,id'],
            'event_date'  => ['required', 'date', 'before_or_equal:today'],
            'keterangan'  => ['nullable', 'string', 'max:1000'],

            'penduduk_id' => ['required', 'integer', 'exists:penduduks,id'],
            'kk_id'       => ['required', 'integer', 'exists:kartu_keluargas,id'],

            'alamat_tujuan'     => ['required', 'string', 'max:500'],
            'rt_tujuan'         => ['nullable', 'string', 'max:10'],
            'rw_tujuan'         => ['nullable', 'string', 'max:10'],
            'desa_tujuan'       => ['nullable', 'string', 'max:255'],
            'kecamatan_tujuan'  => ['required', 'string', 'max:255'],
            'kabupaten_tujuan'  => ['required', 'string', 'max:255'],
            'provinsi_tujuan'   => ['required', 'string', 'max:255'],
            'kode_pos_tujuan'   => ['nullable', 'string', 'max:10'],

            'alasan_pindah'      => ['required', Rule::in([
                'PEKERJAAN', 'PENDIDIKAN', 'KEAMANAN',
                'KESEHATAN', 'PERKAWINAN', 'LAINNYA',
            ])],
            'keterangan_alasan'  => ['nullable', 'string', 'max:1000'],
            'jenis_kepindahan'   => ['required', Rule::in(['INDIVIDU'])],

            'pengganti_kepala_id' => ['nullable', 'integer', 'exists:penduduks,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $pendudukId = $this->input('penduduk_id');
            $kkId       = $this->input('kk_id');
            $rtId       = $this->input('rt_id');

            if (!$pendudukId || !$kkId || !$rtId) {
                return;
            }

            // 1. Cek RT penduduk harus sama dengan rt_id event
            $penduduk = \App\Models\Penduduk::find($pendudukId);
            if ($penduduk && $penduduk->rt_id != $rtId) {
                $validator->errors()->add(
                    'rt_id',
                    'RT event harus sama dengan RT domisili penduduk.'
                );
                return;
            }

            // 2. Cek RT KK harus sama dengan rt_id event
            $kk = \App\Models\KartuKeluarga::find($kkId);
            if ($kk && $kk->rt_id != $rtId) {
                $validator->errors()->add(
                    'kk_id',
                    'KK yang dipilih harus berada di RT yang sama dengan event.'
                );
                return;
            }

            // 3. Penduduk harus anggota aktif KK yang dipilih
            $isMember = KkMember::where('penduduk_id', $pendudukId)
                ->where('kartu_keluarga_id', $kkId)
                ->where('status', 'AKTIF')
                ->exists();

            if (!$isMember) {
                $validator->errors()->add(
                    'penduduk_id',
                    'Penduduk bukan anggota aktif dari KK yang dipilih.'
                );
                return;
            }

            // 4. Cek kepala keluarga
            $isKepala = KkMember::where('penduduk_id', $pendudukId)
                ->where('kartu_keluarga_id', $kkId)
                ->where('is_kepala_keluarga', true)
                ->where('status', 'AKTIF')
                ->exists();

            if ($isKepala) {
                // Jika hanya 1 anggota aktif (kepala itu sendiri), boleh pindah tanpa pengganti
                $totalAktif = KkMember::where('kartu_keluarga_id', $kkId)
                    ->where('status', 'AKTIF')
                    ->count();

                if ($totalAktif === 1) {
                    // KK akan otomatis NON_AKTIF di Action, skip validasi pengganti
                    return;
                }

                // Lebih dari 1 anggota, wajib ada pengganti
                if (empty($this->input('pengganti_kepala_id'))) {
                    $validator->errors()->add(
                        'pengganti_kepala_id',
                        'Pengganti kepala keluarga wajib dipilih karena penduduk yang pindah adalah kepala keluarga.'
                    );
                    return;
                }

                $penggantiId = (int) $this->input('pengganti_kepala_id');

                if ($penggantiId === (int) $pendudukId) {
                    $validator->errors()->add(
                        'pengganti_kepala_id',
                        'Pengganti kepala keluarga tidak boleh orang yang sama.'
                    );
                    return;
                }

                $penggantiIsActiveMember = KkMember::where('penduduk_id', $penggantiId)
                    ->where('kartu_keluarga_id', $kkId)
                    ->where('status', 'AKTIF')
                    ->exists();

                if (!$penggantiIsActiveMember) {
                    $validator->errors()->add(
                        'pengganti_kepala_id',
                        'Pengganti kepala keluarga harus merupakan anggota aktif KK yang sama.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'penduduk_id.required'      => 'Penduduk yang akan pindah wajib dipilih.',
            'penduduk_id.exists'         => 'Penduduk tidak ditemukan.',
            'kk_id.required'             => 'Kartu Keluarga asal wajib dipilih.',
            'kk_id.exists'               => 'Kartu Keluarga tidak ditemukan.',
            'rt_id.required'             => 'RT wajib dipilih.',
            'rt_id.exists'               => 'RT tidak ditemukan.',
            'alamat_tujuan.required'     => 'Alamat tujuan wajib diisi.',
            'kecamatan_tujuan.required'  => 'Kecamatan tujuan wajib diisi.',
            'kabupaten_tujuan.required'  => 'Kabupaten tujuan wajib diisi.',
            'provinsi_tujuan.required'   => 'Provinsi tujuan wajib diisi.',
            'alasan_pindah.required'     => 'Alasan pindah wajib dipilih.',
            'alasan_pindah.in'           => 'Alasan pindah tidak valid.',
            'jenis_kepindahan.required'  => 'Jenis kepindahan wajib dipilih.',
            'jenis_kepindahan.in'        => 'Jenis kepindahan tidak valid.',
            'event_date.before_or_equal' => 'Tanggal pindah tidak boleh di masa depan.',
        ];
    }

    protected function casts(): array
    {
        return [
            'rt_id'               => 'int',
            'penduduk_id'         => 'int',
            'kk_id'               => 'int',
            'pengganti_kepala_id' => 'int',
        ];
    }
}