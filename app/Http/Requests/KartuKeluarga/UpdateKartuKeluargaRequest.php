<?php

declare(strict_types=1);

namespace App\Http\Requests\KartuKeluarga;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateKartuKeluargaRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $kartuKeluarga = $this->route('kartu_keluarga');

        if (!$kartuKeluarga instanceof \App\Models\KartuKeluarga) {
            return false;
        }

        $kartuKeluarga->load(['rt.rw']);

        return $this->user()?->can('update', $kartuKeluarga) ?? false;
    }

    public function rules(): array
    {
        /** @var \App\Models\KartuKeluarga|null $kk */
        $kk = $this->route('kartu_keluarga');
        $kkId = $kk?->id;

        return [
            'no_kk' => [
                'required',
                'string',
                'size:16',
                'regex:/^\d{16}$/',
                Rule::unique('kartu_keluargas', 'no_kk')
                    ->ignore($kkId)
                    ->whereNull('deleted_at'),
            ],
            'alamat' => ['required', 'string', 'max:500'],
            'rt_id' => ['required', 'integer', Rule::exists('rts', 'id')->whereNull('deleted_at')],
            'status_kk' => ['required', 'string', 'in:AKTIF,NON_AKTIF'],
            'tanggal_terbentuk' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var \App\Models\KartuKeluarga|null $kk */
            $kk = $this->route('kartu_keluarga');

            if (!$kk) {
                return;
            }

            // Validasi: rt_id tidak boleh diubah jika masih ada member aktif
            $newRtId = $this->input('rt_id');
            if ($newRtId && (int) $newRtId !== (int) $kk->rt_id) {
                // Cek apakah ada member aktif
                $hasActiveMembers = $kk->kkMembers()
                    ->where('status', 'AKTIF')
                    ->exists();

                if ($hasActiveMembers) {
                    $validator->errors()->add(
                        'rt_id',
                        'RT tidak dapat diubah karena masih ada anggota aktif dalam KK ini. ' .
                            'Gunakan fitur Pindah RT jika ingin memindahkan seluruh KK ke RT lain.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'no_kk.required' => 'No. KK wajib diisi.',
            'no_kk.size' => 'No. KK harus terdiri dari 16 digit.',
            'no_kk.regex' => 'No. KK harus berupa 16 digit angka.',
            'no_kk.unique' => 'No. KK sudah digunakan oleh KK lain.',
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
