<?php

namespace App\Http\Middleware;

use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts patient-portal routes to requests authenticated as a patient (NFC token).
 */
class EnsurePatientUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if (!$user instanceof Patient) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Patient account required.',
            ], 403);
        }

        return $next($request);
    }
}
