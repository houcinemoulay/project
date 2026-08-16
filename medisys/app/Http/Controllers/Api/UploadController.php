<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo'     => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'type'      => 'required|in:doctor,patient',
            'entity_id' => 'required|integer',
        ]);

        // A patient (NFC token) may only replace their own photo
        $authenticated = $request->user();
        if ($authenticated instanceof Patient
            && ($request->type !== 'patient' || (int) $request->entity_id !== $authenticated->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        if ($request->type === 'doctor') {
            $model = \App\Models\Doctor::findOrFail($request->entity_id);
        } else {
            $model = Patient::findOrFail($request->entity_id);
        }

        $path = $request->file('photo')->store('photos/' . $request->type, 'public');

        // Delete old photo
        if ($model->photo && Storage::disk('public')->exists($model->photo)) {
            Storage::disk('public')->delete($model->photo);
        }

        $model->photo = $path;
        $model->save();

        return response()->json([
            'success' => true,
            'url'     => asset('storage/' . $path),
            'path'    => $path,
        ]);
    }

    public function uploadLabResult(Request $request)
    {
        $request->validate([
            'file'       => 'required|mimes:jpeg,png,jpg,gif,pdf|max:10240',
            'patient_id' => 'required|exists:patients,id',
            'note'       => 'nullable|string|max:500',
        ]);

        $path = $request->file('file')->store('lab-results', 'public');

        return response()->json([
            'success' => true,
            'url'     => asset('storage/' . $path),
            'path'    => $path,
            'note'    => $request->note,
        ]);
    }
}
