<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditLogObserver
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function created(Model $model): void
    {
        $this->auditLogService->logModelAction($model, AuditLog::ACTION_CREATE);
    }

    public function updated(Model $model): void
    {
        $this->auditLogService->logModelAction($model, AuditLog::ACTION_UPDATE);
    }

    public function deleted(Model $model): void
    {
        $this->auditLogService->logModelAction($model, AuditLog::ACTION_DELETE);
    }
}
