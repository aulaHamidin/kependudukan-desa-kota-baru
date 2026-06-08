<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActive
{
    /**
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            abort(403, 'Akun Anda sedang nonaktif.');
        }

        return $next($request);
    }
}
