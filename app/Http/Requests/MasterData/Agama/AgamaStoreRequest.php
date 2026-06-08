<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\Agama;

use App\Http\Requests\MasterData\MasterReference\StoreRequest;

class AgamaStoreRequest extends StoreRequest
{
    protected function getTable(): string
    {
        return 'agamas';
    }
}
