<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\ChecksRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    use ChecksRoles;

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->hasRole($request->user(), ['admin'])) {
            return $this->denyForbidden($request, 'Access denied. Admin role required.');
        }

        return $next($request);
    }
}
