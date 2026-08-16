<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Query filters shared by the patient-scoped resources (records, prescriptions, unavailabilities).
 */
trait FiltersMedicalQueries
{
    protected function filterByPatient(Builder $query, Request $request): void
    {
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
    }

    /**
     * Doctors only see their own rows; other roles are left untouched.
     */
    protected function scopeToOwnDoctor(Builder $query, ?Authenticatable $user): void
    {
        if ($user && $user->isDoctor()) {
            $query->where('doctor_id', $user->doctor->id);
        }
    }

    protected function currentDoctorId(Request $request): int
    {
        return $request->user()->doctor->id;
    }
}
