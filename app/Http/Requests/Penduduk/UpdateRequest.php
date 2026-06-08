<?php

declare(strict_types=1);

namespace App\Http\Requests\Penduduk;

use App\Http\Requests\BaseRequest;

class UpdatePendudukRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $penduduk = $this->route('penduduk');
        return $this->user()->can('update', $penduduk);
    }

    public function rules(): array
    {
        // Hanya allow update field optional/non-critical
        return [
            'nama_ayah' => 'nullable|string|max:200',
            'nama_ibu' => 'nullable|string|max:200',
            'pendidikan_id' => 'nullable|exists:pendidikans,kode',
            'pekerjaan_id' => 'nullable|exists:pekerjaans,kode',
            'pendapatan_range_id' => 'nullable|exists:pendapatan_ranges,id',
            'golongan_darah_id' => 'nullable|exists:golongan_darahs,kode',
            'no_hp' => 'nullable|string|max:20|regex:/^[0-9+]{10,20}$/',
            'email' => 'nullable|email|max:100',
        ];
    }

    protected function casts(): array
    {
        return [
            'pendapatan_range_id' => 'int',
        ];
    }
}
