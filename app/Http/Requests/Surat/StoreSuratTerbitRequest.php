<?php

declare(strict_types=1);

namespace App\Http\Requests\Surat;

use App\Http\Requests\BaseRequest;
use App\Models\{JenisSurat, Penduduk, SuratTerbit};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * StoreSuratTerbitRequest - Web form validation for surat generation
 * 
 * Validates surat creation with territory scope and business rules.
 * Web-focused: returns validation errors for form display.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class StoreSuratTerbitRequest extends BaseRequest
{
    /**
     * Define type casts for validated fields.
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'penduduk_id' => 'int',
            'masa_berlaku_khusus' => 'int',
        ];
    }

    /**
     * Determine if the user is authorized to make this request
     * 
     * ✅ FIXED: Delegate to SuratTerbitPolicy instead of hardcoding role check
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', SuratTerbit::class);
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'jenis_surat_kode' => [
                'required',
                'string',
                'max:20',
                Rule::exists('jenis_surat', 'kode')->where('is_active', true),
                'template_exists' // Custom rule - uses hybrid template system
            ],
            'penduduk_id' => [
                'required',
                'integer',
                Rule::exists('penduduks', 'id')->where('status_kependudukan_code', 'AKTIF'),
                'in_user_territory' // Custom rule
            ],
            'keperluan' => [
                'required',
                'string',
                'min:10',
                'max:500'
            ],
            'tanggal_terbit' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:today'
            ],
            'keterangan_tambahan' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'masa_berlaku_khusus' => [
                'nullable',
                'integer',
                'min:1',
                'max:3650' // Max 10 years
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'jenis_surat_kode.required' => 'Jenis surat harus dipilih.',
            'jenis_surat_kode.exists' => 'Jenis surat tidak valid atau tidak aktif.',
            'jenis_surat_kode.template_exists' => 'Template untuk jenis surat ini belum tersedia.',

            'penduduk_id.required' => 'Penduduk harus dipilih.',
            'penduduk_id.exists' => 'Penduduk tidak ditemukan atau tidak aktif.',
            'penduduk_id.in_user_territory' => 'Penduduk tidak berada dalam wilayah kerja Anda.',

            'keperluan.required' => 'Keperluan surat harus diisi.',
            'keperluan.min' => 'Keperluan surat minimal 10 karakter.',
            'keperluan.max' => 'Keperluan surat maksimal 500 karakter.',

            'tanggal_terbit.required' => 'Tanggal terbit harus diisi.',
            'tanggal_terbit.before_or_equal' => 'Tanggal terbit tidak boleh di masa depan.',

            'masa_berlaku_khusus.integer' => 'Masa berlaku harus berupa angka.',
            'masa_berlaku_khusus.min' => 'Masa berlaku minimal 1 hari.',
            'masa_berlaku_khusus.max' => 'Masa berlaku maksimal 3650 hari (10 tahun).'
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'jenis_surat_kode' => 'jenis surat',
            'penduduk_id' => 'penduduk',
            'keperluan' => 'keperluan surat',
            'tanggal_terbit' => 'tanggal terbit',
            'keterangan_tambahan' => 'keterangan tambahan',
            'masa_berlaku_khusus' => 'masa berlaku khusus'
        ];
    }

    /**
     * Configure the validator instance
     * 
     * Custom rules:
     * - template_exists: Uses hybrid template system via JenisSurat::isReadyForGeneration()
     * - in_user_territory: Territory-aware penduduk access check
     */
    public function withValidator($validator): void
    {
        $validator->addExtension('template_exists', function ($attribute, $value, $parameters, $validator) {
            $jenisSurat = JenisSurat::where('kode', $value)->first();

            if (!$jenisSurat) {
                return false;
            }

            // ✅ FIXED: Use hybrid template system method instead of checking old template_filename
            // isReadyForGeneration() checks: is_active + hasTemplate() (via whitelist validation)
            return $jenisSurat->isReadyForGeneration();
        });

        $validator->addExtension('in_user_territory', function ($attribute, $value, $parameters, $validator) {
            $user = Auth::user();

            // Super admin can access all territories
            if ($user->hasRole('super_admin')) {
                return true;
            }

            $penduduk = Penduduk::with(['rt.rw.desa'])->find($value);

            if (!$penduduk) {
                return false;
            }

            // Admin desa can only access penduduk in their desa
            if ($user->hasRole('admin_desa')) {
                return $user->desa_id === $penduduk->rt?->rw?->desa_id;
            }

            return false;
        });
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Set default tanggal_terbit if not provided
        if (!$this->has('tanggal_terbit') || empty($this->tanggal_terbit)) {
            $this->merge([
                'tanggal_terbit' => now()->toDateString()
            ]);
        }

        // Clean and format keperluan
        if ($this->has('keperluan')) {
            $this->merge([
                'keperluan' => trim($this->keperluan)
            ]);
        }
    }

    /**
     * Get validated data with additional computed fields
     */
    public function getValidatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Add computed fields
        $validated['tahun'] = (int) date('Y', strtotime($validated['tanggal_terbit']));
        $validated['bulan'] = (int) date('n', strtotime($validated['tanggal_terbit']));
        $validated['created_by'] = Auth::id();

        return $validated;
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // For web requests, redirect back with errors
        if ($this->expectsJson()) {
            parent::failedValidation($validator);
        }

        // Add additional context to session for better UX
        session()->flash('validation_context', [
            'form_section' => 'surat_terbit_create',
            'error_count' => $validator->errors()->count(),
            'field_errors' => $validator->errors()->keys()
        ]);

        parent::failedValidation($validator);
    }
}
