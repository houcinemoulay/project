<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\FiltersMedicalQueries;
use App\Http\Controllers\Controller;
use App\Jobs\GeneratePrescriptionExplanationJob;
use App\Models\Ordonnance;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrdonnanceController extends Controller
{
    use ApiResponses;
    use FiltersMedicalQueries;

    /** Prescription type each portal role is allowed to see. */
    private const TYPE_BY_ROLE = [
        'isLab'      => 'laboratory',
        'isPharmacy' => 'pharmacy',
        'isNurse'    => 'nurse',
    ];

    public function index(Request $request)
    {
        $query = Ordonnance::with(['patient', 'doctor.user', 'medicalRecord']);
        $user = $request->user();

        $this->filterByPatient($query, $request);
        $this->scopeToOwnDoctor($query, $user);

        foreach (self::TYPE_BY_ROLE as $check => $type) {
            if ($user && $user->{$check}()) {
                $query->where('type', $type);
            }
        }

        return $this->ok($query->latest()->get());
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

        $ordonnance = Ordonnance::create(array_merge($validated, [
            'doctor_id' => $this->currentDoctorId($request),
            'type'      => $validated['type'] ?? 'pharmacy',
            'status'    => 'active',
        ]));

        // Dispatch AI explanation job asynchronously
        GeneratePrescriptionExplanationJob::dispatch($ordonnance);

        return $this->created(
            $ordonnance->load(['patient', 'doctor.user']),
            'Prescription created. AI explanation is being generated.'
        );
    }

    public function show(Ordonnance $ordonnance)
    {
        return $this->ok($ordonnance->load(['patient', 'doctor.user', 'medicalRecord']));
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

        return $this->ok($ordonnance->fresh());
    }

    public function destroy(Ordonnance $ordonnance)
    {
        $ordonnance->delete();

        return $this->message('Prescription deleted.');
    }

    /**
     * Mark ordonnance as dispensed (delivered) by pharmacy
     */
    public function dispense(Request $request, Ordonnance $ordonnance)
    {
        if ($ordonnance->status === 'dispensed') {
            return $this->failure('Already marked as dispensed.');
        }

        $ordonnance->update([
            'status'         => 'dispensed',
            'dispensed_by'   => $request->user()->id,
            'dispensed_at'   => now(),
            'dispensed_note' => $request->input('note'),
        ]);

        return $this->ok($ordonnance->fresh(['patient', 'doctor.user']), 'Marked as delivered.');
    }

    public function toggleTaken(Ordonnance $ordonnance)
    {
        $ordonnance->is_taken = !$ordonnance->is_taken;
        $ordonnance->save();

        return $this->ok(extra: ['is_taken' => $ordonnance->is_taken]);
    }

    public function forPatient(Request $request)
    {
        $patient = $request->user(); // Patient model (NFC auth)

        return $this->ok($this->prescriptionsFor($patient->id)->get());
    }

    /**
     * Generate and download a PDF ordonnance
     */
    public function generatePdf(Ordonnance $ordonnance, Request $request)
    {
        // Allow token via query string for direct link access
        if ($request->has('token') && !$request->bearerToken()) {
            $request->headers->set('Authorization', 'Bearer ' . $request->token);
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
        $patient = Patient::where('nfc_uid', $request->nfc_uid)->first();

        if (!$patient) {
            return $this->notFound('Patient not found');
        }

        return $this->ok(extra: [
            'patient'     => $patient,
            'ordonnances' => $this->prescriptionsFor($patient->id)->where('status', 'active')->get(),
        ]);
    }

    private function prescriptionsFor(int $patientId)
    {
        return Ordonnance::with(['doctor.user', 'medicalRecord'])
            ->where('patient_id', $patientId)
            ->latest();
    }
}

