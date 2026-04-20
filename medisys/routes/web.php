<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\FraudAttemptController;

Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'));
Route::get('/public-booking', fn() => view('appointments.public-booking'));
Route::get('/lang/{lang}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('lang.switch');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'send'])->middleware('throttle:5,1')->name('contact.send');

// Admin contact messages routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{id}', [ContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::post('/contact-messages/{id}/mark-read', [ContactMessageController::class, 'markAsRead'])->name('contact-messages.mark-read');
    Route::post('/contact-messages/{id}/mark-unread', [ContactMessageController::class, 'markAsUnread'])->name('contact-messages.mark-unread');
    Route::delete('/contact-messages/{id}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
    
    // Fraud attempts routes
    Route::get('/fraud-attempts', [FraudAttemptController::class, 'index'])->name('fraud-attempts.index');
    Route::get('/fraud-attempts/{id}', [FraudAttemptController::class, 'show'])->name('fraud-attempts.show');
    Route::delete('/fraud-attempts/{id}', [FraudAttemptController::class, 'destroy'])->name('fraud-attempts.destroy');
});

// Staff dashboard routes
Route::get('/dashboard',       fn() => view('dashboard'));
Route::get('/patients',        fn() => view('patients.index'));
Route::get('/doctors',         fn() => view('doctors.index'));
Route::get('/medical-records', fn() => view('medical-records.index'));
Route::get('/ordonnances',     fn() => view('ordonnances.index'));
Route::get('/pharmacies',      fn() => view('pharmacies.index'));
Route::get('/laboratories',    fn() => view('laboratories.index'));
Route::get('/appointments',    fn() => view('appointments.index'));

// Patient-specific
Route::get('/patient-profile',     fn() => view('patient.profile'));

// Doctor viewing patient without logout
Route::get('/doctor-patient-view', fn() => view('doctor.patient-view'));

// Pharmacy portal
Route::get('/pharmacy-dashboard', fn() => view('pharmacy.dashboard'));

// Lab portal
Route::get('/lab-dashboard', fn() => view('lab.dashboard'));

Route::get('/logout', fn() => redirect('/login'));
