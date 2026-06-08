<?php

declare(strict_types=1);

namespace App\Http\Requests\Reporting;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // already checked by middleware
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|in:penduduk,kk,inconsistency,events',
            'search' => 'nullable|string|max:100',
            'rt_id' => 'nullable|integer|exists:rts,id',
            'event_type' => 'nullable|string|exists:event_types,kode',
            'status_data' => 'nullable|in:DRAFT,VERIFIED,VOID',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:5|max:100',
        ];
    }
}
