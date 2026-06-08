<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTerritorialRole
{
    /**
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthorized.');
        }

        $allowed = ['super_admin', 'admin_desa', 'admin_rw', 'admin_rt', 'viewer'];

        foreach ($allowed as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Akses dibatasi berdasarkan wilayah.');
    }
}
