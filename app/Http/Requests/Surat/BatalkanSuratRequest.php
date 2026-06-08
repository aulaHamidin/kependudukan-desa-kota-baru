<?php

declare(strict_types=1);

namespace App\Http\Requests\Surat;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Auth;

/**
 * BatalkanSuratRequest - Web form validation for cancelling surat
 * 
 * Validates surat cancellation with required reason.
 * Only admin_desa and super_admin can cancel surat in their territory.
 * 
 * @author System Generator
 * @since 2026-02-20
 */
class BatalkanSuratRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // Only admin_desa can cancel surat (super_admin is monitoring-only per Policy)
        return Auth::user()?->hasRole('admin_desa');
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'alasan_batal' => [
                'required',
                'string',
                'min:20',
                'max:500'
            ],
            'konfirmasi_batal' => [
                'required',
                'accepted'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'alasan_batal.required' => 'Alasan pembatalan harus diisi.',
            'alasan_batal.min' => 'Alasan pembatalan minimal 20 karakter untuk dokumentasi yang jelas.',
            'alasan_batal.max' => 'Alasan pembatalan maksimal 500 karakter.',

            'konfirmasi_batal.required' => 'Konfirmasi pembatalan harus dicentang.',
            'konfirmasi_batal.accepted' => 'Anda harus mengkonfirmasi pembatalan surat ini.'
        ];
    }

    /**
     * Get custom attributes for validator errors
     */
    public function attributes(): array
    {
        return [
            'alasan_batal' => 'alasan pembatalan',
            'konfirmasi_batal' => 'konfirmasi pembatalan'
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Clean alasan_batal
        if ($this->has('alasan_batal')) {
            $this->merge([
                'alasan_batal' => trim($this->alasan_batal)
            ]);
        }
    }

    /**
     * Get validated data with additional audit fields
     */
    public function getValidatedWithAudit(): array
    {
        $validated = $this->only(['alasan_batal']);

        // Add audit fields
        $validated['cancelled_by'] = Auth::id();
        $validated['cancelled_at'] = now();
        $validated['status'] = 'BATAL';

        return $validated;
    }

    /**
     * Handle a failed validation attempt  
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        // Add context for better error display
        session()->flash('validation_context', [
            'form_section' => 'surat_batal_form',
            'error_count' => $validator->errors()->count()
        ]);

        parent::failedValidation($validator);
    }

    protected function casts(): array
    {
        return [
            'konfirmasi_batal' => 'bool',
        ];
    }
}
