<?php

declare(strict_types=1);

namespace App\Listeners\Audit;

use App\Events\Audit\DataImported;
use App\Models\AuditLog;
use App\Services\AuditLogService;

class LogDataImport
{
    public function __construct(private AuditLogService $auditLogService) {}

    public function handle(DataImported $event): void
    {
        $this->auditLogService->logImportAction($event->model, $event->count, AuditLog::ACTION_IMPORT);
    }
}
