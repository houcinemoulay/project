<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\FiltersMedicalQueries;
use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    use ApiResponses;
    use FiltersMedicalQueries;

    public function index(Request $request)
    {
        $query = MedicalRecord::with(['patient', 'doctor.user']);

        $this->filterByPatient($query, $request);
        $this->scopeToOwnDoctor($query, $request->user());

        return $this->ok($query->orderBy('visit_date', 'desc')->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'    => 'required|exists:patients,id',
            'diagnosis'     => 'required|string|max:500',
            'notes'         => 'nullable|string',
            'visit_type'    => 'nullable|in:consultation,follow_up,emergency,routine',
            'temperature'   => 'nullable|numeric|between:30,45',
            'blood_pressure'=> 'nullable|string|max:20',
            'heart_rate'    => 'nullable|integer|between:20,300',
            'weight'        => 'nullable|numeric|between:0,500',
            'height'        => 'nullable|numeric|between:0,300',
            'visit_date'    => 'required|date',
        ]);

        $record = MedicalRecord::create(array_merge($validated, [
            'doctor_id' => $this->currentDoctorId($request),
        ]));

        return $this->created($record->load(['patient', 'doctor.user']), 'Medical record created.');
    }

    public function show(MedicalRecord $medicalRecord)
    {
        return $this->ok($medicalRecord->load(['patient', 'doctor.user', 'ordonnances']));
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $validated = $request->validate([
            'diagnosis'     => 'sometimes|string|max:500',
            'notes'         => 'nullable|string',
            'visit_type'    => 'nullable|in:consultation,follow_up,emergency,routine',
            'temperature'   => 'nullable|numeric|between:30,45',
            'blood_pressure'=> 'nullable|string|max:20',
            'heart_rate'    => 'nullable|integer|between:20,300',
            'weight'        => 'nullable|numeric|between:0,500',
            'height'        => 'nullable|numeric|between:0,300',
            'visit_date'    => 'sometimes|date',
        ]);

        $medicalRecord->update($validated);

        return $this->ok($medicalRecord->fresh(['patient', 'doctor.user']), 'Medical record updated.');
    }

    public function destroy(MedicalRecord $medicalRecord)
    {
        $medicalRecord->delete();

        return $this->message('Medical record deleted.');
    }
}
