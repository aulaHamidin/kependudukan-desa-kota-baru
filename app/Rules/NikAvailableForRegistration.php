<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\ViewerRegistrationService;
use Illuminate\Contracts\Validation\Rule;

class NikAvailableForRegistration implements Rule
{
    public function passes($attribute, $value): bool
    {
        $service = app(ViewerRegistrationService::class);
        $check = $service->checkNikAvailability((string) $value);

        return $check['available'];
    }

    public function message(): string
    {
        return 'NIK tidak valid atau sudah terdaftar.';
    }
}
