<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\SearchesColumns;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\RecommendationService;
use App\Support\PublicFiles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    use ApiResponses;
    use SearchesColumns;

    private const DETAIL_RELATIONS = ['medicalRecords.doctor.user', 'ordonnances', 'appointments.doctor.user'];

    public function index(Request $request)
    {
        $query = Patient::query();

        $this->applySearch($query, $request->search, ['name', 'phone'], ['nfc_uid']);

        return $this->ok($query->latest()->paginate(15));
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

        $patient = Patient::create($this->withPhoto($request, $validated));

        return $this->created($patient, 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        return $this->ok($patient->load(self::DETAIL_RELATIONS));
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

        $patient->update($this->withPhoto($request, $validated));

        return $this->ok($patient->fresh(), 'Patient updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return $this->message('Patient deleted successfully.');
    }

    public function history(Patient $patient)
    {
        $history = $patient->medicalRecords()
            ->with(['doctor.user', 'ordonnances'])
            ->orderBy('visit_date', 'desc')
            ->get();

        return $this->ok(extra: [
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

        return $this->ok($patient->load(self::DETAIL_RELATIONS));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withPhoto(Request $request, array $data): array
    {
        if ($request->hasFile('photo')) {
            $data['photo'] = PublicFiles::store($request->file('photo'), 'patients');
        }

        return $data;
    }

    /**
     * Get recommendations for a patient
     */
    public function recommendations(Patient $patient)
    {
        try {
            $result = (new RecommendationService())->generateRecommendations($patient);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? []
            ]);

        } catch (\Exception $e) {
            return $this->failure('Failed to generate recommendations: ' . $e->getMessage(), 500);
        }
    }
}
