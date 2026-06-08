<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\Pekerjaan;

use App\Http\Requests\MasterData\MasterReference\UpdateRequest;

class PekerjaanUpdateRequest extends UpdateRequest
{
    protected function getTable(): string
    {
        return 'pekerjaans';
    }

    protected function getRouteKey(): string
    {
        return 'pekerjaan';
    }
}
