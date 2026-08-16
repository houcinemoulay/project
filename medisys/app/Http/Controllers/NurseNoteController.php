<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesStaff;
use App\Models\NurseNote;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NurseNoteController extends Controller
{
    use AuthorizesStaff;

    /**
     * Store a newly created nurse note.
     */
    public function store(Request $request, Patient $patient)
    {
        $this->authorizeClinicalStaff();

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
            'type' => 'nullable|in:observation,care,medication,other',
        ]);

        $note = NurseNote::create([
            'patient_id' => $patient->id,
            'nurse_id' => Auth::id(),
            'note' => $validated['note'],
            'type' => $validated['type'] ?? 'observation',
        ]);

        return redirect()->back()
            ->with('success', 'Nurse note added successfully for ' . $patient->name);
    }

    /**
     * Display a listing of nurse notes for a specific patient.
     */
    public function index(Patient $patient)
    {
        $this->authorizeClinicalStaff();

        $notes = NurseNote::where('patient_id', $patient->id)
            ->with('nurse')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('nurse-notes.index', compact('patient', 'notes'));
    }
}
