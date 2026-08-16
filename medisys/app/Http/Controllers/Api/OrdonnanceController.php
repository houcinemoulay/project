<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePrescriptionExplanationJob;
use App\Models\Ordonnance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Bus;

class OrdonnanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Ordonnance::with(['patient', 'doctor.user', 'medicalRecord']);

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->user() && $request->user()->isDoctor()) {
            $query->where('doctor_id', $request->user()->doctor->id);
        }

        if ($request->user() && $request->user()->isLab()) {
            $query->where('type', 'laboratory');
        }

        if ($request->user() && $request->user()->isPharmacy()) {
            $query->where('type', 'pharmacy');
        }

        if ($request->user() && $request->user()->isNurse()) {
            $query->where('type', 'nurse');
        }

        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medical_record_id'       => 'nullable|exists:medical_records,id',
            'patient_id'              => 'required|exists:patients,id',
            'medications'             => 'required|array|min:1',
            'medications.*.name'      => 'required|string',
            'medications.*.dosage'    => 'nullable|string',
            'medications.*.frequency' => 'nullable|string',
            'medications.*.duration'  => 'nullable|string',
            'instructions'            => 'nullable|string',
            'issued_date'             => 'required|date',
            'valid_until'             => 'nullable|date',
            'type'                    => 'nullable|in:pharmacy,laboratory,nurse',
        ]);

        $doctorId = $request->user()->doctor->id;

        $ordonnance = Ordonnance::create(array_merge($validated, [
            'doctor_id' => $doctorId,
            'type'      => $validated['type'] ?? 'pharmacy',
            'status'    => 'active',
        ]));

        // Dispatch AI explanation job asynchronously
        GeneratePrescriptionExplanationJob::dispatch($ordonnance);

        return response()->json([
            'success' => true,
            'message' => 'Prescription created. AI explanation is being generated.',
            'data'    => $ordonnance->load(['patient', 'doctor.user']),
        ], 201);
    }

    public function show(Ordonnance $ordonnance)
    {
        return response()->json([
            'success' => true,
            'data'    => $ordonnance->load(['patient', 'doctor.user', 'medicalRecord']),
        ]);
    }

    public function update(Request $request, Ordonnance $ordonnance)
    {
        $validated = $request->validate([
            'medications'  => 'sometimes|array|min:1',
            'instructions' => 'nullable|string',
            'valid_until'  => 'nullable|date',
            'status'       => 'sometimes|in:active,expired,dispensed',
        ]);

        $ordonnance->update($validated);

        return response()->json(['success' => true, 'data' => $ordonnance->fresh()]);
    }

    public function destroy(Ordonnance $ordonnance)
    {
        $ordonnance->delete();
        return response()->json(['success' => true, 'message' => 'Prescription deleted.']);
    }

    /**
     * Mark ordonnance as dispensed (delivered) by pharmacy
     */
    public function dispense(Request $request, Ordonnance $ordonnance)
    {
        if ($ordonnance->status === 'dispensed') {
            return response()->json(['success' => false, 'message' => 'Already marked as dispensed.'], 400);
        }

        $ordonnance->update([
            'status'         => 'dispensed',
            'dispensed_by'   => $request->user()->id,
            'dispensed_at'   => now(),
            'dispensed_note' => $request->input('note'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marked as delivered.',
            'data'    => $ordonnance->fresh(['patient', 'doctor.user']),
        ]);
    }

    public function toggleTaken(Request $request, Ordonnance $ordonnance)
    {
        $user = $request->user();
        if ($user instanceof \App\Models\Patient && $ordonnance->patient_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $ordonnance->is_taken = !$ordonnance->is_taken;
        $ordonnance->save();

        return response()->json(['success' => true, 'is_taken' => $ordonnance->is_taken]);
    }
    public function forPatient(Request $request)
    {
        $patient = $request->user(); // Patient model (NFC auth)
        $ordonnances = Ordonnance::with(['doctor.user', 'medicalRecord'])
            ->where('patient_id', $patient->id)
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $ordonnances]);
    }

    /**
     * Generate and download a PDF ordonnance
     */
    public function generatePdf(Ordonnance $ordonnance, Request $request)
    {
        $user = $request->user();
        if ($user instanceof \App\Models\Patient && $ordonnance->patient_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $ordonnance->load(['patient', 'doctor.user']);

        $pdf = Pdf::loadView('pdf.ordonnance', compact('ordonnance'));
        $pdf->setPaper('A4', 'portrait');

        $filename = "ordonnance_{$ordonnance->id}_{$ordonnance->patient->name}.pdf";
        $path     = "ordonnances/{$filename}";

        Storage::put("public/{$path}", $pdf->output());
        $ordonnance->update(['pdf_path' => $path]);

        return $pdf->download($filename);
    }

    /**
     * Get ordonnances for a patient by NFC UID (pharmacy scan)
     */
    public function byNfcUid(Request $request)
    {
        $patient = \App\Models\Patient::where('nfc_uid', $request->nfc_uid)->first();
        if (!$patient) {
            return response()->json(['success' => false, 'message' => 'Patient not found'], 404);
        }

        $ordonnances = Ordonnance::with(['doctor.user', 'medicalRecord'])
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->latest()
            ->get();

        return response()->json([
            'success'     => true,
            'patient'     => $patient,
            'ordonnances' => $ordonnances,
        ]);
    }
}

