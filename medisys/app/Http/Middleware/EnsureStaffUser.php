<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects requests authenticated as a patient (NFC token) on staff-only routes.
 * Patients authenticate against the Patient model, staff against the User model.
 */
class EnsureStaffUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if (!$user instanceof User) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Staff account required.',
            ], 403);
        }

        return $next($request);
    }
}
