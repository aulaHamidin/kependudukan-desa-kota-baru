<?php

declare(strict_types=1);

namespace App\Http\Requests\DataInti\KkMember;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class LeaveRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $member = $this->route('kk_member');

        return $member ? ($this->user()?->can('update', $member) ?? false) : false;
    }

    public function rules(): array
    {
        return [
            'tanggal_keluar' => ['required', 'date'],
            'status' => ['nullable', 'string', 'max:20'],
            'kk_asal_id' => ['nullable', 'integer', Rule::exists('kartu_keluargas', 'id')->whereNull('deleted_at')],
            'event_keluar_id' => ['nullable', 'integer', Rule::exists('events', 'id')],
            'alasan_keluar' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_keluar.required' => 'Tanggal keluar wajib diisi.',
            'status.max' => 'Status anggota maksimal 20 karakter.',
            'kk_asal_id.exists' => 'KK asal tidak ditemukan.',
            'event_keluar_id.exists' => 'Event keluar tidak valid.',
            'alasan_keluar.max' => 'Alasan keluar maksimal 500 karakter.',
        ];
    }

    protected function casts(): array
    {
        return [
            'kk_asal_id' => 'int',
            'event_keluar_id' => 'int',
        ];
    }
}
