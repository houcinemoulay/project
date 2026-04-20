<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage as ContactMail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
            'g-recaptcha-response' => 'required|string',
        ]);

        // Honeypot check — bots fill hidden fields
        if ($request->filled('website') || $request->filled('confirm_email')) {
            return redirect()->back()->with('error', 'Suspicious activity detected.');
        }

        // Verify reCAPTCHA
        $recaptchaSecret   = config('services.recaptcha.secret');
        $recaptchaResponse = $request->input('g-recaptcha-response');

        if ($recaptchaSecret) {
            $verify = @file_get_contents(
                "https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}"
                . "&response={$recaptchaResponse}&remoteip=" . $request->ip()
            );
            $keys = $verify ? json_decode($verify, true) : null;

            if (!$keys || !($keys['success'] ?? false)) {
                return back()
                    ->withErrors(['recaptcha' => 'reCAPTCHA verification failed. Please try again.'])
                    ->withInput();
            }
        }

        // Determine authenticated user info
        $userId   = null;
        $userRole = null;

        if (Auth::check()) {
            $userId   = Auth::id();
            $userRole = Auth::user()->role ?? null;
        }

        // Sanitize and store
        ContactMessage::create([
            'name'      => htmlspecialchars($validated['name'], ENT_QUOTES, 'UTF-8'),
            'email'     => strtolower(trim($validated['email'])),
            'subject'   => htmlspecialchars($validated['subject'], ENT_QUOTES, 'UTF-8'),
            'message'   => htmlspecialchars($validated['message'], ENT_QUOTES, 'UTF-8'),
            'user_id'   => $userId,
            'user_role' => $userRole,
            'status'    => 'new',
        ]);

        try {
            Mail::to('alexandermail4334@gmail.com')->send(new ContactMail($validated));
            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            return back()->with('success', 'Your message has been saved. We will respond soon!');
        }
    }
}
