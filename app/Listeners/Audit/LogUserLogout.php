<?php

declare(strict_types=1);

namespace App\Listeners\Audit;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Logout;

class LogUserLogout
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function handle(Logout $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;

        if (!$user) {
            return;
        }

        $this->auditLogService->logAuthAction($user, AuditLog::ACTION_LOGOUT);
    }
}
