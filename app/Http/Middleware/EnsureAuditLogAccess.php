<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAuditLogAccess
{
    /**
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $role = strtolower((string) ($user?->role ?? ''));

        if (!in_array($role, ['super_admin', 'admin_desa'], true)) {
            abort(403, 'Akses audit log dibatasi untuk super admin dan admin desa.');
        }

        return $next($request);
    }
}
