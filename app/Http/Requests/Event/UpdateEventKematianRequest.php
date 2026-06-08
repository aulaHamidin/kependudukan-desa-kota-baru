<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Models\Penduduk;
use App\Http\Requests\BaseRequest;

class UpdateEventKematianRequest extends BaseRequest
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
            'event_date'            => ['required', 'date', 'before_or_equal:today'],
            'keterangan'            => ['nullable', 'string', 'max:1000'],

            // Detail kematian yang boleh diubah
            'tempat_meninggal'      => ['required', 'string', 'max:200'],
            'jam_meninggal'         => ['nullable', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'sebab_kematian'        => ['nullable', 'string', 'max:100'],
            'penyakit'              => ['nullable', 'string', 'max:200'],
            'keterangan_kematian'   => ['nullable', 'string', 'max:2000'],

            // Pelapor boleh diubah
            'pelapor_id'            => ['nullable', 'integer', 'exists:penduduks,id'],
            'nama_pelapor'          => ['nullable', 'string', 'max:200'],
            'hubungan_pelapor_code' => ['nullable', 'string', 'exists:hubungan_keluarga,kode'],

            // NOTE: penduduk_id, kk_id, pengganti_kepala_id TIDAK boleh diubah saat update.
            // Perubahan pada penduduk/KK hanya bisa via delete DRAFT + create baru.
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize jam_meninggal format: add leading zeros if needed
        if ($this->has('jam_meninggal') && $this->jam_meninggal) {
            $time = $this->jam_meninggal;
            if (preg_match('/^(\d{1,2}):(\d{1,2})$/', $time, $matches)) {
                $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $minute = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                $this->merge(['jam_meninggal' => "$hour:$minute"]);
            }
        }
    }

    public function messages(): array
    {
        return [
            'event_date.required'        => 'Tanggal kematian wajib diisi.',
            'event_date.before_or_equal' => 'Tanggal kematian tidak boleh di masa depan.',
            'tempat_meninggal.required'  => 'Tempat meninggal wajib diisi.',
            'jam_meninggal.regex'        => 'Format jam meninggal tidak valid. Contoh: 14:30 atau 9:15',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $event = $this->route('event');
            if (!$event || !is_object($event)) {
                return;
            }
            
            /** @var \App\Models\Event $event */
            $pendudukId = $event->penduduk_id;
            $rtId = $event->rt_id;

            $this->validatePelapor($validator, $pendudukId, $rtId);
        });
    }

    /**
     * Validasi pelapor_id:
     * 1. Bukan almarhum itu sendiri
     * 2. Status penduduk AKTIF
     * 3. Berada di RT yang sama dengan almarhum
     *
     * penduduk_id dan rt_id diambil dari event yang di-route (tidak bisa diubah via update).
     */
    private function validatePelapor($validator, ?int $pendudukId, ?int $rtId): void
    {
        $pelaporId = $this->input('pelapor_id');

        if (!$pelaporId) return;

        // Guard: pelapor tidak boleh almarhum itu sendiri
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
            'pelapor_id' => 'int',
        ];
    }
}