<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\FiltersMedicalQueries;
use App\Http\Controllers\Controller;
use App\Models\DoctorUnavailability;
use Illuminate\Http\Request;

class DoctorUnavailabilityController extends Controller
{
    use ApiResponses;
    use FiltersMedicalQueries;

    public function index(Request $request)
    {
        $query = DoctorUnavailability::query();

        if ($request->user()->isDoctor()) {
            $this->scopeToOwnDoctor($query, $request->user());
        } elseif ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        return $this->ok($query->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:255',
        ]);

        $unavailability = DoctorUnavailability::create(array_merge($validated, [
            'doctor_id' => $this->currentDoctorId($request),
        ]));

        return $this->created($unavailability, 'Unavailability added.');
    }

    public function destroy(DoctorUnavailability $doctorUnavailability)
    {
        $doctorUnavailability->delete();

        return $this->message('Unavailability removed.');
    }
}
