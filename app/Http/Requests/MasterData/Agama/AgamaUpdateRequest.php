<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\Agama;

use App\Http\Requests\MasterData\MasterReference\UpdateRequest;

class AgamaUpdateRequest extends UpdateRequest
{
    protected function getTable(): string
    {
        return 'agamas';
    }

    protected function getRouteKey(): string
    {
        return 'agama';
    }
}
