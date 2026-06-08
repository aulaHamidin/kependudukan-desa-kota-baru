<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Models\KkMember;
use App\Models\Penduduk;
use App\Http\Requests\BaseRequest;

class StoreEventKematianRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'rt_id'                 => ['required', 'integer', 'exists:rts,id'],
            'event_date'            => ['required', 'date', 'before_or_equal:today'],
            'keterangan'            => ['nullable', 'string', 'max:1000'],
            'penduduk_id'           => ['required', 'integer', 'exists:penduduks,id'],
            'kk_id'                 => ['required', 'integer', 'exists:kartu_keluargas,id'],
            'tempat_meninggal'      => ['required', 'string', 'max:200'],
            'jam_meninggal'         => ['nullable', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'sebab_kematian'        => ['nullable', 'string', 'max:100'],
            'penyakit'              => ['nullable', 'string', 'max:200'],
            'keterangan_kematian'   => ['nullable', 'string', 'max:2000'],
            'pelapor_id'            => ['nullable', 'integer', 'exists:penduduks,id'],
            'nama_pelapor'          => ['nullable', 'string', 'max:200'],
            'hubungan_pelapor_code' => ['nullable', 'string', 'exists:hubungan_keluarga,kode'],
            'pengganti_kepala_id'   => ['nullable', 'integer', 'exists:penduduks,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'penduduk_id.required'      => 'Penduduk yang meninggal wajib dipilih.',
            'kk_id.required'            => 'Kartu Keluarga wajib dipilih.',
            'tempat_meninggal.required' => 'Tempat meninggal wajib diisi.',
            'event_date.before_or_equal' => 'Tanggal kematian tidak boleh di masa depan.',
            'jam_meninggal.regex'       => 'Format jam meninggal tidak valid. Contoh: 14:30 atau 9:15',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize jam_meninggal: tambahkan leading zero jika perlu
        if ($this->has('jam_meninggal') && !empty($this->jam_meninggal)) {
            $jam = $this->jam_meninggal;

            // Split by colon
            $parts = explode(':', $jam);
            if (count($parts) === 2) {
                $hour = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $minute = str_pad($parts[1], 2, '0', STR_PAD_LEFT);

                $this->merge([
                    'jam_meninggal' => $hour . ':' . $minute
                ]);
            }
        }
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

            // --- Validasi RT penduduk harus sama dengan rt_id event ---
            $penduduk = \App\Models\Penduduk::find($pendudukId);
            if ($penduduk && $penduduk->rt_id != $rtId) {
                $validator->errors()->add(
                    'rt_id',
                    'RT event harus sama dengan RT domisili penduduk.'
                );
                return;
            }

            // --- Validasi RT KK harus sama dengan rt_id event ---
            $kk = \App\Models\KartuKeluarga::find($kkId);
            if ($kk && $kk->rt_id != $rtId) {
                $validator->errors()->add(
                    'kk_id',
                    'KK yang dipilih harus berada di RT yang sama dengan event.'
                );
                return;
            }

            // --- Validasi penduduk harus anggota aktif KK yang dipilih ---
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

            // --- Validasi pengganti kepala keluarga ---
            $isKepala = KkMember::where('penduduk_id', $pendudukId)
                ->where('kartu_keluarga_id', $kkId)
                ->where('is_kepala_keluarga', true)
                ->where('status', 'AKTIF')
                ->exists();

            if ($isKepala) {
                $adaAnggotaLain = KkMember::where('kartu_keluarga_id', $kkId)
                    ->where('status', 'AKTIF')
                    ->where('penduduk_id', '!=', $pendudukId)
                    ->exists();

                if ($adaAnggotaLain && empty($this->input('pengganti_kepala_id'))) {
                    $validator->errors()->add(
                        'pengganti_kepala_id',
                        'Pengganti kepala keluarga wajib dipilih karena almarhum adalah kepala keluarga dan masih ada anggota KK lain.'
                    );
                }

                if (!empty($this->input('pengganti_kepala_id'))) {
                    $penggantiId = (int) $this->input('pengganti_kepala_id');

                    if ($penggantiId === (int) $pendudukId) {
                        $validator->errors()->add('pengganti_kepala_id', 'Pengganti kepala tidak boleh orang yang sama dengan almarhum.');
                        return;
                    }

                    $isActiveMember = KkMember::where('penduduk_id', $penggantiId)
                        ->where('kartu_keluarga_id', $kkId)
                        ->where('status', 'AKTIF')
                        ->exists();

                    if (!$isActiveMember) {
                        $validator->errors()->add('pengganti_kepala_id', 'Pengganti kepala harus anggota aktif KK yang sama.');
                    }
                }
            }

            // --- Validasi pelapor_id ---
            $this->validatePelapor($validator, $pendudukId, $rtId);
        });
    }

    /**
     * Validasi pelapor_id:
     * 1. Bukan almarhum itu sendiri
     * 2. Status penduduk AKTIF
     * 3. Berada di RT yang sama dengan almarhum
     */
    private function validatePelapor($validator, mixed $pendudukId, mixed $rtId): void
    {
        $pelaporId = $this->input('pelapor_id');

        if (!$pelaporId) return;

        // Guard: jika pelapor_id sama dengan almarhum
        if ($pendudukId && (int) $pelaporId === (int) $pendudukId) {
            $validator->errors()->add('pelapor_id', 'Pelapor tidak boleh orang yang sama dengan almarhum.');
            return;
        }

        $pelapor = Penduduk::find((int) $pelaporId);

        if (!$pelapor) return; // sudah di-handle rule exists:penduduks,id

        if ($pelapor->status_kependudukan_code !== 'AKTIF') {
            $validator->errors()->add('pelapor_id', 'Pelapor harus penduduk dengan status AKTIF.');
        }

        if ($rtId && (int) $pelapor->rt_id !== (int) $rtId) {
            $validator->errors()->add('pelapor_id', 'Pelapor harus berada di RT yang sama dengan almarhum.');
        }
    }

    protected function casts(): array
    {
        return [
            'rt_id'               => 'int',
            'penduduk_id'         => 'int',
            'kk_id'               => 'int',
            'pelapor_id'          => 'int',
            'pengganti_kepala_id' => 'int',
        ];
    }
}
