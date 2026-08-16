<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Support\PublicFiles;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    use ApiResponses;

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo'     => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'type'      => 'required|in:doctor,patient',
            'entity_id' => 'required|integer',
        ]);

        $modelClass = $request->type === 'doctor' ? Doctor::class : Patient::class;
        $model = $modelClass::findOrFail($request->entity_id);

        $model->photo = PublicFiles::replace(
            $model->photo,
            $request->file('photo'),
            'photos/' . $request->type
        );
        $model->save();

        return $this->ok(extra: [
            'url'  => PublicFiles::url($model->photo),
            'path' => $model->photo,
        ]);
    }

    public function uploadLabResult(Request $request)
    {
        $request->validate([
            'file'       => 'required|mimes:jpeg,png,jpg,gif,pdf|max:10240',
            'patient_id' => 'required|exists:patients,id',
            'note'       => 'nullable|string|max:500',
        ]);

        $path = PublicFiles::store($request->file('file'), 'lab-results');

        return $this->ok(extra: [
            'url'  => PublicFiles::url($path),
            'path' => $path,
            'note' => $request->note,
        ]);
    }
}
