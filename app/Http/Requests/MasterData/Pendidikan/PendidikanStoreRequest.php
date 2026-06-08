<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\Pendidikan;

use App\Http\Requests\MasterData\MasterReference\StoreRequest;

class PendidikanStoreRequest extends StoreRequest
{
    protected function getTable(): string
    {
        return 'pendidikans';
    }
}
