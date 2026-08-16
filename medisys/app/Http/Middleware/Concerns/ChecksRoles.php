<?php

namespace App\Http\Middleware\Concerns;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait ChecksRoles
{
    /**
     * @param  list<string>  $roles
     */
    protected function hasRole(mixed $user, array $roles): bool
    {
        return $user !== null && in_array($user->role ?? null, $roles, true);
    }

    /** JSON 401 for API clients, redirect to the login page otherwise. */
    protected function denyUnauthenticated(Request $request, string $redirectMessage): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login')->with('error', $redirectMessage);
    }

    /** JSON 403 for API clients, a 403 error page otherwise. */
    protected function denyForbidden(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        abort(403, $message);
    }
}
