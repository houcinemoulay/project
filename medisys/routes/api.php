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
use App\Http\Controllers\GoogleAIController;

// ── Public ──────────────────────────────────────────────
Route::post('/login',      [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/nfc-login',  [AuthController::class, 'nfcLogin'])->middleware('throttle:10,1');
Route::get('/public/doctors', [DoctorController::class, 'index']); // For selecting doctor
Route::get('/public/available-slots', [AppointmentController::class, 'getAvailableSlots']);
Route::post('/public/book-appointment', [AppointmentController::class, 'publicBook'])
    ->middleware(['throttle:5,1', 'suspicious.booking']);

// Public AI chat — rate limited to curb abuse of the upstream AI quota
Route::post('/medical-chat', [GoogleAIController::class, 'sendMessage'])->middleware('throttle:10,1');

// ── Authenticated (staff and patients) ──────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Photo upload (patients may only replace their own photo)
    Route::post('/upload/photo', [UploadController::class, 'uploadPhoto']);

    // Patients may read their own recommendations and tick off their own prescriptions
    Route::get('/patients/{patient}/recommendations', [PatientController::class, 'recommendations']);
    Route::patch('/ordonnances/{ordonnance}/toggle-taken', [OrdonnanceController::class, 'toggleTaken']);
    Route::get('/ordonnances/{ordonnance}/pdf', [OrdonnanceController::class, 'generatePdf']);
});

// ── Authenticated (all staff roles) ─────────────────────
Route::middleware(['auth:sanctum', 'staff'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'stats']);

    // Patient lookup by NFC UID — no token creation, safe for pharmacy/lab sessions
    Route::post('/nfc-lookup', [AuthController::class, 'nfcLookup']);

    // Patients
    Route::apiResource('patients', PatientController::class);
    Route::get('/patients/{patient}/history', [PatientController::class, 'history']);

    // Medical Records (read for all, delete for admins and doctors only)
    Route::get('/medical-records', [MedicalRecordController::class, 'index']);
    Route::get('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'show']);
    Route::post('/medical-records', [MedicalRecordController::class, 'store']);
    Route::put('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'update']);
    Route::middleware(['role:admin,doctor'])->group(function () {
        Route::delete('/medical-records/{medicalRecord}', [MedicalRecordController::class, 'destroy']);
    });

    // Ordonnances (read for all, create/update for doctors and admins only)
    Route::get('/ordonnances', [OrdonnanceController::class, 'index']);
    Route::get('/ordonnances/{ordonnance}', [OrdonnanceController::class, 'show']);
    Route::middleware(['role:doctor,admin'])->group(function () {
        Route::post('/ordonnances', [OrdonnanceController::class, 'store']);
        Route::put('/ordonnances/{ordonnance}', [OrdonnanceController::class, 'update']);
        Route::delete('/ordonnances/{ordonnance}', [OrdonnanceController::class, 'destroy']);
    });
    Route::post('/ordonnances/by-nfc', [OrdonnanceController::class, 'byNfcUid']);
    Route::patch('/ordonnances/{ordonnance}/dispense', [OrdonnanceController::class, 'dispense']);

    // Appointments
    Route::apiResource('appointments', AppointmentController::class);

    // Doctors (read for all staff, write for admin — see admin-only group below)
    Route::get('/doctors', [DoctorController::class, 'index']);
    Route::get('/doctors/{doctor}', [DoctorController::class, 'show']);

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
        Route::match(['put', 'patch'], '/doctors/{doctor}', [DoctorController::class, 'update']);
        Route::delete('/doctors/{doctor}', [DoctorController::class, 'destroy']);
        Route::apiResource('pharmacies', PharmacyController::class);
        Route::apiResource('laboratories', LaboratoryController::class);
        
        // Nurse management
        Route::get('/admin/nurses/statistics', [App\Http\Controllers\Api\Admin\NurseController::class, 'statistics']);
        Route::get('/admin/nurses', [App\Http\Controllers\Api\Admin\NurseController::class, 'index']);
        Route::get('/admin/nurses/{id}', [App\Http\Controllers\Api\Admin\NurseController::class, 'show']);
        Route::post('/admin/nurses', [App\Http\Controllers\Api\Admin\NurseController::class, 'store']);
        Route::put('/admin/nurses/{id}', [App\Http\Controllers\Api\Admin\NurseController::class, 'update']);
        Route::delete('/admin/nurses/{id}', [App\Http\Controllers\Api\Admin\NurseController::class, 'destroy']);
    });

});

// ── Patient (NFC authenticated) ──────────────────────────
Route::middleware(['auth:sanctum', 'patient'])->prefix('patient')->group(function () {
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
