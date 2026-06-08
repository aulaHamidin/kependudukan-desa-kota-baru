<?php

declare(strict_types=1);

namespace App\Http\Requests\MasterData\Pekerjaan;

use App\Http\Requests\MasterData\MasterReference\StoreRequest;

class PekerjaanStoreRequest extends StoreRequest
{
    protected function getTable(): string
    {
        return 'pekerjaans';
    }
}
