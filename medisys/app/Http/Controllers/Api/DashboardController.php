<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Ordonnance;
use App\Models\ContactMessage;

class DashboardController extends Controller
{
    const OPERATION_PRICE = 39; // DA per prescription
    const APPOINTMENT_PRICE = 49; // DA per appointment

    public function stats(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        // Per-doctor counts (Prescriptions @ 45DA, Appointments @ 90DA)
        $doctorOps = Doctor::with('user')
            ->withCount(['ordonnances as ords_count' => fn($q) => $q->withTrashed()])
            ->withCount(['appointments as apps_count' => fn($q) => $q->where('status', '!=', 'cancelled')])
            ->where('is_active', true)
            ->get()
            ->map(function($d) {
                $revOrds = $d->ords_count * self::OPERATION_PRICE;
                $revApps = $d->apps_count * self::APPOINTMENT_PRICE;
                $revenue = $revOrds + $revApps;
                $paid = $d->paid_amount ?? 0;
                return [
                    'id'          => $d->id,
                    'name'        => $d->user->name ?? '—',
                    'specialty'   => $d->specialty,
                    'ordonnances' => $d->ords_count,
                    'appointments'=> $d->apps_count,
                    'revenue'     => $revenue,
                    'paid'        => $paid,
                    'debt'        => $revenue - $paid,
                ];
            });

        $totalOrdsCount = Ordonnance::withTrashed()->count();
        $totalAppsCount = Appointment::where('status', '!=', 'cancelled')->count();
        
        $totalRevenue = ($totalOrdsCount * self::OPERATION_PRICE) + ($totalAppsCount * self::APPOINTMENT_PRICE);
        $totalPaid = Doctor::sum('paid_amount');
        $totalDebt = max(0, $totalRevenue - $totalPaid); // Prevent negative total debt if overpaid overall, though it's up to preference. We'll just do $totalRevenue - $totalPaid.
        $totalDebt = $totalRevenue - $totalPaid;

        $unreadContactMessages = ContactMessage::where('status', 'new')->count();
        $totalContactMessages = ContactMessage::count();

        $stats = [
            'patients'      => Patient::count(),
            'doctors'       => Doctor::where('is_active', true)->count(),
            'records_today' => MedicalRecord::whereDate('visit_date', today())->count(),
            'appointments_today' => Appointment::whereDate('scheduled_at', today())
                                     ->whereIn('status', ['pending', 'confirmed'])->count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'total_ordonnances' => $totalOrdsCount,
            'total_appointments' => $totalAppsCount,
            'total_ordonnance_revenue' => $totalOrdsCount * self::OPERATION_PRICE,
            'total_appointment_revenue' => $totalAppsCount * self::APPOINTMENT_PRICE,
            'total_revenue'     => $totalRevenue,
            'total_paid'        => $totalPaid,
            'total_debt'        => $totalDebt,
            'contact_messages_unread' => $unreadContactMessages,
            'contact_messages_total'  => $totalContactMessages,
            'prices' => [
                'ordonnance'  => self::OPERATION_PRICE,
                'appointment' => self::APPOINTMENT_PRICE
            ],
            'doctor_operations' => $doctorOps,
            'recent_records' => MedicalRecord::with(['patient', 'doctor.user'])
                                    ->latest()
                                    ->limit(5)
                                    ->get(),
            'upcoming_appointments' => Appointment::with(['patient', 'doctor.user'])
                                    ->where('status', 'pending')
                                    ->where('scheduled_at', '>=', now())
                                    ->orderBy('scheduled_at')
                                    ->limit(5)
                                    ->get(),
            'my_stats' => null,
        ];

        if ($user && $user->isDoctor()) {
            $doctor = $user->doctor;
            if ($doctor) {
                $ords = Ordonnance::withTrashed()->where('doctor_id', $doctor->id)->count();
                $apps = Appointment::where('doctor_id', $doctor->id)->where('status', '!=', 'cancelled')->count();
                $rev = ($ords * self::OPERATION_PRICE) + ($apps * self::APPOINTMENT_PRICE);
                $stats['my_stats'] = [
                    'revenue' => $rev,
                    'paid'    => $doctor->paid_amount ?? 0,
                    'debt'    => $rev - ($doctor->paid_amount ?? 0)
                ];
            }
        }

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
