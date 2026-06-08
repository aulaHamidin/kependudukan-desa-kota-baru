<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class VoidEventRequest extends BaseRequest
{
    public function authorize(): bool
    {
        // Authorization ditangani di Controller via $this->authorize('void', $event)
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'void_reason.required' => 'Alasan void wajib diisi.',
            'void_reason.min'      => 'Alasan void minimal 10 karakter.',
            'void_reason.max'      => 'Alasan void maksimal 500 karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'void_reason' => 'alasan void',
        ];
    }

    protected function casts(): array
    {
        return [];
    }
}
