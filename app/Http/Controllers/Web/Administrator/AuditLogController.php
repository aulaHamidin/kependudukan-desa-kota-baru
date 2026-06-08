<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Administrator;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user')->latest('created_at');

        $action = trim((string) $request->get('aksi'));
        if ($action !== '') {
            $query->where('aksi', $action);
        }

        $model = trim((string) $request->get('model'));
        if ($model !== '') {
            $query->where('model', $model);
        }

        $userFilter = trim((string) $request->get('user_id'));
        if ($userFilter !== '') {
            if ($userFilter === 'system') {
                $query->where('actor_type', 'system');
            } else {
                $query->where('user_id', $userFilter);
            }
        }

        $startDate = trim((string) $request->get('start_date'));
        $endDate = trim((string) $request->get('end_date'));

        if ($startDate !== '' && $endDate !== '') {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
        } elseif ($startDate !== '') {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        } elseif ($endDate !== '') {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }

        $search = trim((string) $request->get('search'));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('aksi', 'like', '%' . $search . '%')
                    ->orWhere('model', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('username', 'like', '%' . $search . '%');
                    });
            });
        }

        $logs = $query->paginate(10)->withQueryString();

        $userOptions = AuditLog::query()
            ->with('user')
            ->select('user_id', 'actor_type')
            ->distinct()
            ->get()
            ->mapWithKeys(function (AuditLog $log) {
                if ($log->actor_type === 'system') {
                    return ['system' => 'System'];
                }

                return [$log->user_id => $log->user?->name ?? ('User #' . $log->user_id)];
            })
            ->unique()
            ->sort();

        return view('administrator.audit_log.index', [
            'logs' => $logs,
            'aksiOptions' => AuditLog::ACTION_LABELS,
            'modelOptions' => AuditLog::MODEL_LABELS,
            'userOptions' => $userOptions,
        ]);
    }

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load('user');

        return view('administrator.audit_log.show', [
            'log' => $auditLog,
        ]);
    }
}
