<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PindahRtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // handled by policy di controller
    }

    public function rules(): array
    {
        $kk = $this->route('kk');
        $currentRtId = null;
        
        if ($kk && is_object($kk)) {
            /** @var \App\Models\KartuKeluarga $kk */
            $currentRtId = $kk->rt_id;
        }

        return [
            'rt_id_tujuan' => [
                'required',
                'integer',
                Rule::exists('rts', 'id')->whereNull('deleted_at'),
                $currentRtId ? Rule::notIn([$currentRtId]) : '',
            ],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rt_id_tujuan.required'  => 'RT tujuan wajib dipilih.',
            'rt_id_tujuan.exists'    => 'RT tujuan tidak ditemukan atau sudah tidak aktif.',
            'rt_id_tujuan.not_in'    => 'RT tujuan harus berbeda dari RT saat ini.',
        ];
    }
}