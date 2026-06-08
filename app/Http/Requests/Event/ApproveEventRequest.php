<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Http\Requests\BaseRequest;

class ApproveEventRequest extends BaseRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');
        return $this->user()->can('verify', $event);
    }

    public function rules(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [];
    }
}
