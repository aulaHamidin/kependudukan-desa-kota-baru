<?php

declare(strict_types=1);

namespace App\Events\Audit;

class DataImported
{
    /**
     * @param class-string $model
     */
    public function __construct(
        public string $model,
        public int $count
    ) {}
}
