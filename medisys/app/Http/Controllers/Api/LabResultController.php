<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\StoresUploadedFiles;
use App\Http\Controllers\Controller;
use App\Models\LabResult;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LabResultController extends Controller
{
    use StoresUploadedFiles;

    /** List lab results for a patient */
    public function index(Request $request)
    {
        $query = LabResult::with(['laboratory', 'patient']);

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Lab users only see their own uploads
        $user = $request->user();
        if ($user->role === 'lab' && $user->laboratory) {
            $query->where('laboratory_id', $user->laboratory->id);
        }

        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    /** All lab results uploaded by this lab (for the History tab) */
    public function history(Request $request)
    {
        $user = $request->user();

        $query = LabResult::with(['patient']);

        // Lab users see only their own uploads
        if ($user->role === 'lab' && $user->laboratory) {
            $query->where('laboratory_id', $user->laboratory->id);
        }

        // Admin/doctor see all
        $results = $query->latest()->get();

        return response()->json(['success' => true, 'data' => $results]);
    }

    /** Upload a lab result (image or PDF) */
    public function store(Request $request)
    {
        $request->validate([
            'file'       => 'required|mimes:jpeg,png,jpg,gif,pdf|max:10240',
            'patient_id' => 'required|exists:patients,id',
            'title'      => 'nullable|string|max:255',
            'note'       => 'nullable|string|max:1000',
        ]);

        $file     = $request->file('file');
        $ext      = strtolower($file->getClientOriginalExtension());
        $fileType = in_array($ext, ['jpg','jpeg','png','gif']) ? 'image' : 'pdf';

        $path = $this->storeUploadedFile($file, 'lab-results');

        $user = $request->user();
        $labId = null;
        if ($user->role === 'lab' && $user->laboratory) {
            $labId = $user->laboratory->id;
        }

        $result = LabResult::create([
            'patient_id'    => $request->patient_id,
            'laboratory_id' => $labId,
            'file_path'     => $path,
            'file_type'     => $fileType,
            'title'         => $request->title,
            'note'          => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $result,
            'url'     => asset('storage/' . $path),
        ], 201);
    }

    /** Delete a lab result */
    public function destroy(LabResult $labResult)
    {
        if (Storage::disk('public')->exists($labResult->file_path)) {
            Storage::disk('public')->delete($labResult->file_path);
        }
        $labResult->delete();
        return response()->json(['success' => true]);
    }
}
