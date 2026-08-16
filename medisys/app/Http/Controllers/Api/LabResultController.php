<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\LabResult;
use App\Support\PublicFiles;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class LabResultController extends Controller
{
    use ApiResponses;

    /** List lab results for a patient */
    public function index(Request $request)
    {
        $query = LabResult::with(['laboratory', 'patient']);

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $this->scopeToOwnLaboratory($query, $request->user());

        return $this->ok($query->latest()->get());
    }

    /** All lab results uploaded by this lab (for the History tab) */
    public function history(Request $request)
    {
        $query = LabResult::with(['patient']);

        // Admin/doctor see all
        $this->scopeToOwnLaboratory($query, $request->user());

        return $this->ok($query->latest()->get());
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

        $file = $request->file('file');
        $path = PublicFiles::store($file, 'lab-results');

        $result = LabResult::create([
            'patient_id'    => $request->patient_id,
            'laboratory_id' => $this->ownLaboratoryId($request->user()),
            'file_path'     => $path,
            'file_type'     => PublicFiles::kind($file),
            'title'         => $request->title,
            'note'          => $request->note,
        ]);

        return $this->created($result, extra: ['url' => PublicFiles::url($path)]);
    }

    /** Delete a lab result */
    public function destroy(LabResult $labResult)
    {
        PublicFiles::delete($labResult->file_path);
        $labResult->delete();

        return $this->ok();
    }

    /** Lab users only see their own uploads; admins and doctors see everything. */
    private function scopeToOwnLaboratory(Builder $query, ?Authenticatable $user): void
    {
        $laboratoryId = $this->ownLaboratoryId($user);

        if ($laboratoryId !== null) {
            $query->where('laboratory_id', $laboratoryId);
        }
    }

    private function ownLaboratoryId(?Authenticatable $user): ?int
    {
        return $user && $user->role === 'lab' && $user->laboratory ? $user->laboratory->id : null;
    }
}
