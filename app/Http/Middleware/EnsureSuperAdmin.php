<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSuperAdmin
{
    /**
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || strtolower((string) $user->role) !== 'super_admin') {
            abort(403, 'Akses dibatasi untuk super admin.');
        }

        return $next($request);
    }
}
