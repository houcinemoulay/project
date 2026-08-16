<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesStaff;
use App\Models\Vital;
use App\Models\Patient;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VitalController extends Controller
{
    use AuthorizesStaff;

    /**
     * Show the form for creating a new vital.
     */
    public function create(Patient $patient)
    {
        $this->authorizeClinicalStaff();

        return view('vitals.create', compact('patient'));
    }

    /**
     * Store a newly created vital in storage.
     */
    public function store(Request $request, Patient $patient)
    {
        $this->authorizeClinicalStaff();

        $validated = $request->validate([
            'blood_pressure_systolic' => 'required|integer|min:50|max:250',
            'blood_pressure_diastolic' => 'required|integer|min:30|max:150',
            'glucose_level' => 'nullable|numeric|min:20|max:600',
            'heart_rate' => 'required|integer|min:40|max:200',
            'temperature' => 'required|numeric|min:30|max:45',
            'oxygen_saturation' => 'required|integer|min:70|max:100',
            'weight' => 'nullable|numeric|min:1|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        $vital = Vital::create([
            'patient_id' => $patient->id,
            'nurse_id' => Auth::id(),
            'blood_pressure_systolic' => $validated['blood_pressure_systolic'],
            'blood_pressure_diastolic' => $validated['blood_pressure_diastolic'],
            'glucose_level' => $validated['glucose_level'] ?? null,
            'heart_rate' => $validated['heart_rate'],
            'temperature' => $validated['temperature'],
            'oxygen_saturation' => $validated['oxygen_saturation'],
            'weight' => $validated['weight'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Generate alerts for abnormal values
        $alerts = AlertService::checkVitals($vital);

        $message = 'Vitals recorded successfully for ' . $patient->name;
        if (count($alerts) > 0) {
            $message .= ' (' . count($alerts) . ' alert' . (count($alerts) > 1 ? 's' : '') . ' generated)';
        }

        return redirect()->route('vitals.index', $patient->id)
            ->with('success', $message);
    }

    /**
     * Display a listing of vitals for a specific patient.
     */
    public function index(Patient $patient)
    {
        $this->authorizeClinicalStaff();

        $vitals = Vital::forPatient($patient->id)
            ->latest()
            ->with('nurse')
            ->paginate(10);

        return view('vitals.index', compact('patient', 'vitals'));
    }

    /**
     * Display patient profile with vitals for staff.
     */
    public function showPatient(Patient $patient)
    {
        $this->authorizeClinicalStaff();

        $latestVitals = Vital::forPatient($patient->id)
            ->latest()
            ->with('nurse')
            ->take(5)
            ->get();

        $vitalsCount = Vital::forPatient($patient->id)->count();

        return view('vitals.patient-show', compact('patient', 'latestVitals', 'vitalsCount'));
    }
}
