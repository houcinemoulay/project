<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\StoresUploadedFiles;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    use StoresUploadedFiles;

    public function index(Request $request)
    {
        $query = Patient::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%")
                  ->orWhere('nfc_uid', $search);
            });
        }

        $patients = $query->latest()->paginate(15);

        return response()->json(['success' => true, 'data' => $patients]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'age'               => 'required|integer|min:0|max:150',
            'gender'            => 'required|in:male,female,other',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email',
            'nfc_uid'           => 'nullable|string|unique:patients,nfc_uid',
            'blood_type'        => 'nullable|string|max:5',
            'allergies'         => 'nullable|string',
            'address'           => 'nullable|string',
            'date_of_birth'     => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:255',
            'photo'             => 'nullable|image|max:2048',
        ]);

        $data = $validated;
        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storeUploadedFile($request->file('photo'), 'patients');
        }
        $patient = Patient::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully.',
            'data'    => $patient,
        ], 201);
    }

    public function show(Patient $patient)
    {
        return response()->json([
            'success' => true,
            'data'    => $patient->load(['medicalRecords.doctor.user', 'ordonnances', 'appointments.doctor.user']),
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'name'              => 'sometimes|string|max:255',
            'age'               => 'sometimes|integer|min:0|max:150',
            'gender'            => 'sometimes|in:male,female,other',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email',
            'nfc_uid'           => ['nullable', 'string', Rule::unique('patients', 'nfc_uid')->ignore($patient->id)],
            'blood_type'        => 'nullable|string|max:5',
            'allergies'         => 'nullable|string',
            'address'           => 'nullable|string',
            'date_of_birth'     => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:255',
            'is_active'         => 'boolean',
            'photo'             => 'nullable|image|max:2048',
        ]);

        $data = $validated;
        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storeUploadedFile($request->file('photo'), 'patients');
        }
        $patient->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully.',
            'data'    => $patient->fresh(),
        ]);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['success' => true, 'message' => 'Patient deleted successfully.']);
    }

    public function history(Patient $patient)
    {
        $history = $patient->medicalRecords()
            ->with(['doctor.user', 'ordonnances'])
            ->orderBy('visit_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'patient' => [
                'id'   => $patient->id,
                'name' => $patient->name,
                'age'  => $patient->age,
            ],
            'history' => $history,
        ]);
    }

    public function profile(Request $request)
    {
        // For authenticated patient (NFC login)
        $patient = $request->user();

        return response()->json([
            'success' => true,
            'data'    => $patient->load(['medicalRecords.doctor.user', 'ordonnances', 'appointments.doctor.user']),
        ]);
    }

    /**
     * Get recommendations for a patient
     */
    public function recommendations(Patient $patient)
    {
        try {
            $recommendationService = new RecommendationService();
            $result = $recommendationService->generateRecommendations($patient);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? []
            ], $result['success'] ? 200 : 422);

        } catch (\Throwable $e) {
            Log::error('Failed to generate patient recommendations', [
                'patient_id' => $patient->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate recommendations. Please try again later.'
            ], 500);
        }
    }
}
