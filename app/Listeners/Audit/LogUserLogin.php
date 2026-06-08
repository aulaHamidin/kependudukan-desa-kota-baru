<?php

declare(strict_types=1);

namespace App\Listeners\Audit;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Login;

class LogUserLogin
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function handle(Login $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;

        if (!$user) {
            return;
        }

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ])->saveQuietly();

        $this->auditLogService->logAuthAction($user, AuditLog::ACTION_LOGIN);
    }
}
