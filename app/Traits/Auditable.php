<?php

declare(strict_types=1);

namespace App\Traits;

use App\Observers\AuditLogObserver;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::observe(AuditLogObserver::class);
    }
}
