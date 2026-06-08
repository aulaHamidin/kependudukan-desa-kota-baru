<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allow only admin_desa role.
 */
class EnsureAdminDesa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('admin_desa')) {
            abort(403, 'Hanya admin desa yang diizinkan.');
        }

        return $next($request);
    }
}
