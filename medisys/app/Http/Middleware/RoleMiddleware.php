<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\ChecksRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generic role middleware: checks the authenticated user/patient's role.
 * Usage in routes: middleware('role:admin') or middleware('role:nurse,admin')
 */
class RoleMiddleware
{
    use ChecksRoles;

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return $this->denyUnauthenticated($request, 'Please login to access this page.');
        }

        if ($this->hasRole($request->user(), $roles)) {
            return $next($request);
        }

        return $this->denyForbidden($request, 'Access denied. Required role: ' . implode(', ', $roles) . '.');
    }
}
