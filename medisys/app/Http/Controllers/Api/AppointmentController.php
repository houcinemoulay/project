<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Services\BookingFraudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor.user']);

        if ($request->user()->isDoctor()) {
            $query->where('doctor_id', $request->user()->doctor->id);
        }
        // Admin can see all

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('scheduled_at')->get()]);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        if ($request->user() && $request->user()->isDoctor()) {
            $input['doctor_id'] = $request->user()->doctor->id;
        }

        $validated = validator($input, [
            'patient_id'   => 'required|exists:patients,id',
            'doctor_id'    => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date|after:now',
            'reason'       => 'nullable|string|max:500',
            'notes'        => 'nullable|string',
            'status'       => 'nullable|in:pending,confirmed,completed,cancelled'
        ])->validate();

        // Check if slot is already taken
        $isTaken = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('scheduled_at', $validated['scheduled_at'])
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($isTaken) {
            return response()->json(['success' => false, 'message' => 'This time slot is already taken.'], 422);
        }

        // Check if doctor is working at this time
        $doc = \App\Models\Doctor::find($validated['doctor_id']);
        if ($doc) {
            $time = date('H:i:s', strtotime($validated['scheduled_at']));
            $day = date('l', strtotime($validated['scheduled_at']));
            
            if ($doc->working_days && !in_array($day, $doc->working_days)) {
                return response()->json(['success' => false, 'message' => "The doctor doesn't work on {$day}s."], 422);
            }
            if ($doc->working_hours_start && ($time < $doc->working_hours_start || $time > $doc->working_hours_end)) {
                return response()->json(['success' => false, 'message' => "The doctor is only available between {$doc->working_hours_start} and {$doc->working_hours_end}."], 422);
            }
        }

        // Get client IP address for fraud detection
        $clientIp = $request->ip();
        
        $appointment = Appointment::create($validated);
        
        // Detect fraud — a failure here must not hide the created appointment,
        // but it must be visible in the logs and in the response.
        $fraudResult = null;
        try {
            $fraudService = new BookingFraudService();
            $fraudResult = $fraudService->detectFraud(
                $clientIp,
                $input['patient_email'] ?? '',
                $input['patient_phone'] ?? '',
                $input['patient_id'] ?? null
            );

            $appointment->update([
                'is_suspicious' => $fraudResult['is_suspicious'],
                'fraud_score' => $fraudResult['fraud_score'],
                'fraud_reason' => $fraudResult['indicators'][0]['reason'] ?? null
            ]);
        } catch (\Throwable $e) {
            Log::error('Fraud detection failed for appointment', [
                'appointment_id' => $appointment->id,
                'ip'             => $clientIp,
                'message'        => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Appointment scheduled.',
            'data'    => $appointment->load(['patient', 'doctor.user']),
            'fraud_detection' => $fraudResult,
            'fraud_detection_available' => $fraudResult !== null
        ], 201);
    }

    public function show(Appointment $appointment)
    {
        return response()->json(['success' => true, 'data' => $appointment->load(['patient', 'doctor.user'])]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date',
            'reason'       => 'nullable|string|max:500',
            'status'       => 'sometimes|in:pending,confirmed,completed,cancelled',
            'notes'        => 'nullable|string',
        ]);

        $appointment->update($validated);
        return response()->json(['success' => true, 'data' => $appointment->fresh()]);
    }

    public function patientBook(Request $request)
    {
        $patient = $request->user();
        if (!$patient instanceof Patient) return response()->json(['error' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'doctor_id'    => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date|after:now',
            'reason'       => 'nullable|string|max:500',
        ]);

        // Validation checks
        $isTaken = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('scheduled_at', $validated['scheduled_at'])
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($isTaken) return response()->json(['success' => false, 'message' => 'Time slot taken.'], 422);

        $doc = \App\Models\Doctor::findOrFail($validated['doctor_id']);
        $time = date('H:i:s', strtotime($validated['scheduled_at']));
        $day = date('l', strtotime($validated['scheduled_at']));
        if ($doc->working_days && !in_array($day, $doc->working_days)) return response()->json(['success'=>false, 'message'=>"Not working on {$day}"], 422);
        if ($doc->working_hours_start && ($time < $doc->working_hours_start || $time > $doc->working_hours_end)) return response()->json(['success'=>false, 'message'=>"Outside hours"], 422);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $validated['doctor_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Appointment requested.', 'data' => $appointment]);
    }

    public function publicBook(Request $request)
    {
        // Honeypot check — bots fill hidden fields
        if ($request->filled('website') || $request->filled('confirm_email')) {
            return response()->json(['success' => false, 'message' => 'Invalid request.'], 403);
        }

        $rules = [
            'doctor_id'    => 'required|exists:doctors,id',
            'scheduled_at' => 'required|date|after:now',
            'guest_name'   => 'required|string|min:2|max:255',
            'guest_phone'  => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s\(\)]{7,20}$/'],
            'reason'       => 'nullable|string|max:500',
        ];

        // Require reCAPTCHA if configured
        $recaptchaSecret = config('services.recaptcha.secret');
        if ($recaptchaSecret) {
            $rules['g-recaptcha-response'] = 'required|string';
        }

        $validated = $request->validate($rules);

        // Verify reCAPTCHA with Google
        if ($recaptchaSecret && $request->filled('g-recaptcha-response')) {
            try {
                $verification = Http::timeout(10)
                    ->asForm()
                    ->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret'   => $recaptchaSecret,
                        'response' => $request->input('g-recaptcha-response'),
                        'remoteip' => $request->ip(),
                    ])
                    ->throw()
                    ->json();
            } catch (\Throwable $e) {
                Log::error('Public booking: reCAPTCHA verification request failed', [
                    'ip'      => $request->ip(),
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Could not verify reCAPTCHA right now. Please try again in a moment.',
                ], 503);
            }

            if (!($verification['success'] ?? false)) {
                Log::warning('Public booking: reCAPTCHA verification rejected', [
                    'ip'          => $request->ip(),
                    'error_codes' => $verification['error-codes'] ?? [],
                ]);

                return response()->json(['success' => false, 'message' => 'reCAPTCHA verification failed.'], 422);
            }
        }

        // Check duplicate booking: same phone within 5 minutes
        $recentByPhone = Appointment::where('guest_phone', $validated['guest_phone'])
            ->where('created_at', '>', now()->subMinutes(5))
            ->exists();
        if ($recentByPhone) {
            return response()->json(['success' => false, 'message' => 'A booking with this phone number was recently submitted. Please wait a few minutes.'], 429);
        }

        // Check slot availability
        $isTaken = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('scheduled_at', $validated['scheduled_at'])
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($isTaken) return response()->json(['success' => false, 'message' => 'Time slot taken.'], 422);

        $doc = \App\Models\Doctor::findOrFail($validated['doctor_id']);
        $time = date('H:i:s', strtotime($validated['scheduled_at']));
        $day = date('l', strtotime($validated['scheduled_at']));
        if ($doc->working_days && !in_array($day, $doc->working_days)) return response()->json(['success'=>false, 'message'=>"Not working on {$day}"], 422);
        if ($doc->working_hours_start && ($time < $doc->working_hours_start || $time > $doc->working_hours_end)) return response()->json(['success'=>false, 'message'=>"Outside hours"], 422);

        $appointment = Appointment::create([
            'doctor_id'    => $validated['doctor_id'],
            'scheduled_at' => $validated['scheduled_at'],
            'guest_name'   => htmlspecialchars($validated['guest_name'], ENT_QUOTES, 'UTF-8'),
            'guest_phone'  => preg_replace('/[^0-9+\-\s()]/', '', $validated['guest_phone']),
            'reason'       => isset($validated['reason']) ? htmlspecialchars($validated['reason'], ENT_QUOTES, 'UTF-8') : null,
            'status'       => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Guest appointment requested.']);
    }

    public function getAvailableSlots(Request $request)
    {
        $doc = \App\Models\Doctor::findOrFail($request->doctor_id);
        $date = $request->date; // Y-m-d
        $dayName = date('l', strtotime($date));

        if ($doc->working_days && !in_array($dayName, $doc->working_days)) {
            return response()->json(['success' => true, 'slots' => []]);
        }

        $start = $doc->working_hours_start ?: '08:00:00';
        $end = $doc->working_hours_end ?: '16:00:00';
        $step = $doc->treatment_time ?: 30;

        $slots = [];
        $current = strtotime($date . ' ' . $start);
        $last = strtotime($date . ' ' . $end);

        $existing = Appointment::where('doctor_id', $doc->id)
            ->whereDate('scheduled_at', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('scheduled_at')
            ->map(fn($t) => date('H:i', strtotime($t)))
            ->toArray();

        // Check unavailabilities
        $isOff = \App\Models\DoctorUnavailability::where('doctor_id', $doc->id)
            ->where('start_date', '<=', $date)
            ->where(function($q) use ($date) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $date);
            })->exists();
        
        if ($isOff) return response()->json(['success' => true, 'slots' => []]);

        while ($current < $last) {
            $t = date('H:i', $current);
            if (!in_array($t, $existing)) {
                $slots[] = $t;
            }
            $current = strtotime("+{$step} minutes", $current);
        }

        $suggestion = null;
        if (empty($slots)) {
            // Find next available day
            for ($i = 1; $i <= 14; $i++) {
                $nextDate = date('Y-m-d', strtotime("$date +$i days"));
                $nextDayName = date('l', strtotime($nextDate));
                
                if ($doc->working_days && !in_array($nextDayName, $doc->working_days)) continue;
                
                $isNextOff = \App\Models\DoctorUnavailability::where('doctor_id', $doc->id)
                    ->where('start_date', '<=', $nextDate)
                    ->where(function($q) use ($nextDate) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', $nextDate);
                    })->exists();
                if ($isNextOff) continue;

                $nextExisting = Appointment::where('doctor_id', $doc->id)
                    ->whereDate('scheduled_at', $nextDate)
                    ->where('status', '!=', 'cancelled')
                    ->pluck('scheduled_at')->map(fn($t)=>date('H:i', strtotime($t)))->toArray();

                $nCur = strtotime($nextDate . ' ' . $start);
                $nLast = strtotime($nextDate . ' ' . $end);
                while ($nCur < $nLast) {
                    $nt = date('H:i', $nCur);
                    if (!in_array($nt, $nextExisting)) {
                        $suggestion = ['date' => $nextDate, 'time' => $nt];
                        break 2;
                    }
                    $nCur = strtotime("+{$step} minutes", $nCur);
                }
            }
        }

        return response()->json(['success' => true, 'slots' => $slots, 'suggestion' => $suggestion]);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['success' => true, 'message' => 'Appointment deleted.']);
    }
}
