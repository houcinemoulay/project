<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\StoresUploadedFiles;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    use StoresUploadedFiles;

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo'     => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'type'      => 'required|in:doctor,patient',
            'entity_id' => 'required|integer',
        ]);

        if ($request->type === 'doctor') {
            $model = \App\Models\Doctor::findOrFail($request->entity_id);
        } else {
            $model = \App\Models\Patient::findOrFail($request->entity_id);
        }

        $previousPhoto = $model->photo;
        $path = $this->storeUploadedFile($request->file('photo'), 'photos/' . $request->type);

        $model->photo = $path;
        $model->save();

        if ($previousPhoto && Storage::disk('public')->exists($previousPhoto)) {
            Storage::disk('public')->delete($previousPhoto);
        }

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

        $path = $this->storeUploadedFile($request->file('file'), 'lab-results');

        return response()->json([
            'success' => true,
            'url'     => asset('storage/' . $path),
            'path'    => $path,
            'note'    => $request->note,
        ]);
    }
}
