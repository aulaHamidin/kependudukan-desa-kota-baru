<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\Pendidikan;

use App\Http\Requests\MasterData\MasterReference\UpdateRequest;

class PendidikanUpdateRequest extends UpdateRequest
{
    protected function getTable(): string
    {
        return 'pendidikans';
    }

    protected function getRouteKey(): string
    {
        return 'pendidikan';
    }
}
