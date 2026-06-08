<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Http\Requests\BaseRequest;

class RejectEventRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user()->can('verify', $event);
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi',
        ];
    }

    protected function casts(): array
    {
        return [];
    }
}
