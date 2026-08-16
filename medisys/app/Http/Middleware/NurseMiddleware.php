<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Concerns\ChecksRoles;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NurseMiddleware
{
    use ChecksRoles;

    /**
     * Allow nurses and admins into the nurse dashboard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $this->denyUnauthenticated($request, 'Please login to access nurse dashboard.');
        }

        if (!$this->hasRole($request->user(), ['nurse', 'admin'])) {
            return $this->denyForbidden($request, 'Access denied. Nurse or admin role required.');
        }

        return $next($request);
    }
}
