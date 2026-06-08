<?php

declare(strict_types=1);

namespace App\Http\Requests\Surat;

use App\Http\Requests\BaseRequest;
use App\Models\JenisSurat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * StoreJenisSuratRequest - Master validation for jenis surat CRUD
 * 
 * Validates jenis surat creation/update (super_admin only).
 * Web-focused validation for master data management.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class StoreJenisSuratRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Only super_admin can manage jenis surat
        return Auth::user()?->hasRole('super_admin');
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        $jenisSuratId = $this->route('jenis_surat')?->kode ?? null;

        return [
            'kode' => [
                'required',
                'string',
                'min:2',
                'max:20',
                'regex:/^[A-Z_]+$/', // Only uppercase letters and underscores
                $jenisSuratId
                    ? Rule::unique('jenis_surat', 'kode')->ignore($jenisSuratId, 'kode')
                    : Rule::unique('jenis_surat', 'kode')
            ],
            'nama' => [
                'required',
                'string',
                'min:5',
                'max:100'
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:500'
            ],
            // Hybrid Template System fields
            'template_category' => [
                'nullable',
                'string',
                Rule::in(array_keys(JenisSurat::TEMPLATE_CATEGORIES))
            ],
            'template_sections' => [
                'nullable',
                'array'
            ],
            'template_sections.header' => ['nullable', 'string', 'max:2000'],
            'template_sections.body' => ['nullable', 'string', 'max:10000'],
            'template_sections.footer' => ['nullable', 'string', 'max:2000'],
            'template_sections.custom_fields' => ['nullable', 'array'],
            'required_fields' => [
                'nullable',
                'array'
            ],
            'required_fields.*' => [
                'string',
                'max:50'
            ],
            'signature_type' => [
                'nullable',
                'string',
                Rule::in(array_keys(JenisSurat::SIGNATURE_TYPES))
            ],
            'format_nomor' => [
                'nullable',
                'string',
                'max:200'
            ],
            'masa_berlaku_hari' => [
                'required',
                'integer',
                'min:0',
                'max:3650' // Max 10 years
            ],
            'persyaratan' => [
                'nullable',
                'array'
            ],
            'persyaratan.*' => [
                'string',
                'max:200'
            ],
            'biaya_admin' => [
                'nullable',
                'numeric',
                'min:0',
                'max:1000000' // Max 1M rupiah
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ],
            'keterangan' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'kode.required' => 'Kode jenis surat harus diisi.',
            'kode.regex' => 'Kode jenis surat hanya boleh huruf besar dan underscore (_).',
            'kode.unique' => 'Kode jenis surat sudah digunakan.',
            'kode.min' => 'Kode jenis surat minimal 2 karakter.',
            'kode.max' => 'Kode jenis surat maksimal 20 karakter.',

            'nama.required' => 'Nama jenis surat harus diisi.',
            'nama.min' => 'Nama jenis surat minimal 5 karakter.',
            'nama.max' => 'Nama jenis surat maksimal 100 karakter.',

            'template_category.in' => 'Kategori template tidak valid. Pilihan: ' . implode(', ', array_keys(JenisSurat::TEMPLATE_CATEGORIES)),
            'signature_type.in' => 'Tipe tanda tangan tidak valid. Pilihan: ' . implode(', ', array_keys(JenisSurat::SIGNATURE_TYPES)),
            'required_fields.array' => 'Required fields harus berupa array.',
            'template_sections.array' => 'Template sections harus berupa array.',

            'masa_berlaku_hari.required' => 'Masa berlaku (hari) harus diisi.',
            'masa_berlaku_hari.integer' => 'Masa berlaku harus berupa angka.',
            'masa_berlaku_hari.min' => 'Masa berlaku minimal 0 hari (tidak ada masa berlaku).',
            'masa_berlaku_hari.max' => 'Masa berlaku maksimal 3650 hari (10 tahun).',

            'persyaratan.*.string' => 'Setiap persyaratan harus berupa teks.',
            'persyaratan.*.max' => 'Setiap persyaratan maksimal 200 karakter.',

            'biaya_admin.numeric' => 'Biaya admin harus berupa angka.',
            'biaya_admin.min' => 'Biaya admin minimal 0 rupiah.',
            'biaya_admin.max' => 'Biaya admin maksimal 1.000.000 rupiah.',

            'is_active.boolean' => 'Status aktif harus berupa true/false.'
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'kode' => 'kode jenis surat',
            'nama' => 'nama jenis surat',
            'deskripsi' => 'deskripsi',
            'template_category' => 'kategori template',
            'template_sections' => 'section template',
            'required_fields' => 'field yang wajib',
            'signature_type' => 'tipe tanda tangan',
            'format_nomor' => 'format nomor',
            'masa_berlaku_hari' => 'masa berlaku (hari)',
            'persyaratan' => 'persyaratan',
            'biaya_admin' => 'biaya admin',
            'is_active' => 'status aktif',
            'keterangan' => 'keterangan'
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Clean and format kode (uppercase)
        if ($this->has('kode')) {
            $this->merge([
                'kode' => strtoupper(trim($this->kode))
            ]);
        }

        // Clean nama
        if ($this->has('nama')) {
            $this->merge([
                'nama' => trim($this->nama)
            ]);
        }

        // Clean template_category (lowercase)
        if ($this->has('template_category') && !empty($this->template_category)) {
            $this->merge([
                'template_category' => strtolower(trim($this->template_category))
            ]);
        }

        // Convert empty persyaratan to null
        if ($this->has('persyaratan') && empty($this->persyaratan)) {
            $this->merge(['persyaratan' => null]);
        }

        // Set default values
        if (!$this->has('masa_berlaku_hari')) {
            $this->merge(['masa_berlaku_hari' => 0]);
        }

        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    /**
     * Get validated data with additional fields
     */
    public function getValidatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Add audit fields
        $validated['created_by'] = Auth::id();
        $validated['updated_at'] = now();

        // Convert persyaratan to JSON if array
        if (isset($validated['persyaratan']) && is_array($validated['persyaratan'])) {
            $validated['persyaratan'] = json_encode($validated['persyaratan']);
        }

        return $validated;
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Add context for master data validation errors
        session()->flash('validation_context', [
            'form_section' => 'jenis_surat_form',
            'error_count' => $validator->errors()->count(),
            'is_master_data' => true
        ]);

        parent::failedValidation($validator);
    }

    protected function casts(): array
    {
        return [
            'masa_berlaku_hari' => 'int',
            'biaya_admin' => 'float',
        ];
    }
}
