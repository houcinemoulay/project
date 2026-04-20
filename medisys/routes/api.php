<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\MedicalRecordController;
use App\Http\Controllers\Api\OrdonnanceController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\LaboratoryController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\LabResultController;
use App\Http\Controllers\Api\DoctorUnavailabilityController;

// ── Public ──────────────────────────────────────────────
Route::post('/login',      [AuthController::class, 'login']);
Route::post('/nfc-login',  [AuthController::class, 'nfcLogin']);
Route::get('/public/doctors', [DoctorController::class, 'index']); // For selecting doctor
Route::get('/public/available-slots', [AppointmentController::class, 'getAvailableSlots']);
Route::post('/public/book-appointment', [AppointmentController::class, 'publicBook'])
    ->middleware(['throttle:5,1', 'suspicious.booking']);

// Patient lookup by NFC UID — no token creation, safe for pharmacy/lab sessions
Route::middleware(['auth:sanctum'])->post('/nfc-lookup', [AuthController::class, 'nfcLookup']);

// ── Authenticated (all staff roles) ─────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'stats']);

    // Photo upload
    Route::post('/upload/photo', [UploadController::class, 'uploadPhoto']);

    // Patients
    Route::apiResource('patients', PatientController::class);
    Route::get('/patients/{patient}/history', [PatientController::class, 'history']);

    // Medical Records
    Route::apiResource('medical-records', MedicalRecordController::class);

    // Ordonnances
    Route::apiResource('ordonnances', OrdonnanceController::class);
    Route::get('/ordonnances/{ordonnance}/pdf', [OrdonnanceController::class, 'generatePdf']);
    Route::post('/ordonnances/by-nfc', [OrdonnanceController::class, 'byNfcUid']);
    Route::patch('/ordonnances/{ordonnance}/dispense', [OrdonnanceController::class, 'dispense']);
    Route::patch('/ordonnances/{ordonnance}/toggle-taken', [OrdonnanceController::class, 'toggleTaken']);

    // Appointments
    Route::apiResource('appointments', AppointmentController::class);

    // Doctors (read for all, write for admin)
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);
    Route::post('/doctors', [DoctorController::class, 'store']);
    Route::patch('/doctors/{doctor}', [DoctorController::class, 'update']);
    Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);

    // Lab Results — lab can upload, doctor/admin can view
    Route::get('/lab-results', [LabResultController::class, 'index']);
    Route::get('/lab-results/history', [LabResultController::class, 'history']);
    Route::post('/lab-results', [LabResultController::class, 'store']);
    Route::delete('/lab-results/{labResult}', [LabResultController::class, 'destroy']);

    // Doctor Unavailability
    Route::apiResource('doctor-unavailabilities', DoctorUnavailabilityController::class);

    // Profile info for pharmacy / lab
    Route::get('/my-profile', function (\Illuminate\Http\Request $req) {
        $user = $req->user()->load(['pharmacy', 'laboratory', 'doctor']);
        return response()->json(['success' => true, 'data' => $user]);
    });

    // Contact messages (admin access checked in controller)
    Route::get('/contact-messages', [App\Http\Controllers\Api\ContactMessageController::class, 'index']);
    Route::get('/my-contact-messages', [App\Http\Controllers\Api\ContactMessageController::class, 'myMessages']);

    // Admin-only management
    Route::middleware(['role:admin'])->group(function () {
        Route::post('/doctors', [DoctorController::class, 'store']);
        Route::put('/doctors/{doctor}', [DoctorController::class, 'update']);
        Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);
        Route::apiResource('pharmacies', PharmacyController::class);
        Route::apiResource('laboratories', LaboratoryController::class);
    });
});

// ── Patient (NFC authenticated) ──────────────────────────
Route::middleware(['auth:sanctum'])->prefix('patient')->group(function () {
    Route::get('/profile', [PatientController::class, 'profile']);
    Route::post('/appointments/book', [AppointmentController::class, 'patientBook']);
    Route::get('/ordonnances', [OrdonnanceController::class, 'forPatient']);
    Route::get('/lab-results', function (\Illuminate\Http\Request $req) {
        $patient = $req->user();
        $results = \App\Models\LabResult::with('laboratory')
            ->where('patient_id', $patient->id)
            ->latest()->get();
        return response()->json(['success' => true, 'data' => $results]);
    });
});
