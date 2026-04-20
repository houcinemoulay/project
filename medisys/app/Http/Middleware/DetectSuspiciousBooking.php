<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FraudAttempt;
use Carbon\Carbon;

class DetectSuspiciousBooking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $email = $request->input('email');
        $phone = $request->input('phone');

        // Check honeypot field
        if ($request->input('website') || $request->input('confirm_email')) {
            $this->logFraudAttempt($ip, $userAgent, $request->all(), $email, $phone, 'honeypot');
            abort(403, 'Suspicious activity detected');
        }

        // Check rate limiting per IP (max 5 bookings per minute)
        $recentAttempts = FraudAttempt::where('ip_address', $ip)
            ->where('created_at', '>', Carbon::now()->subMinutes(1))
            ->count();

        if ($recentAttempts >= 5) {
            $this->logFraudAttempt($ip, $userAgent, $request->all(), $email, $phone, 'rate_limit');
            abort(429, 'Too many booking attempts. Please try again later.');
        }

        // Check duplicate bookings (same email/phone within 5 minutes)
        if ($email) {
            $recentEmail = FraudAttempt::where('email', $email)
                ->where('created_at', '>', Carbon::now()->subMinutes(5))
                ->count();

            if ($recentEmail >= 1) {
                $this->logFraudAttempt($ip, $userAgent, $request->all(), $email, $phone, 'duplicate');
                abort(429, 'Duplicate booking attempt detected.');
            }
        }

        if ($phone) {
            $recentPhone = FraudAttempt::where('phone', $phone)
                ->where('created_at', '>', Carbon::now()->subMinutes(5))
                ->count();

            if ($recentPhone >= 1) {
                $this->logFraudAttempt($ip, $userAgent, $request->all(), $email, $phone, 'duplicate');
                abort(429, 'Duplicate booking attempt detected.');
            }
        }

        return $next($request);
    }

    private function logFraudAttempt($ip, $userAgent, $payload, $email, $phone, $reason)
    {
        FraudAttempt::create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'payload' => $payload,
            'email' => $email,
            'phone' => $phone,
            'reason' => $reason,
        ]);
    }
}
