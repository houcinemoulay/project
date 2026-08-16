<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\StoresUploadedFiles;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    use StoresUploadedFiles;

    public function index()
    {
        $doctors = Doctor::with('user')
            ->where('is_active', true)
            ->get()
            ->map(fn($d) => $this->formatDoctor($d));

        return response()->json(['success' => true, 'data' => $doctors]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8',
            'specialty'      => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'bio'            => 'nullable|string',
            'photo'          => 'nullable|image|max:2048',
            'working_days'   => 'nullable|array',
            'working_days.*' => 'string',
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end'   => 'nullable|date_format:H:i',
            'treatment_time' => 'nullable|integer|min:1',
        ]);

        $photoPath = $request->hasFile('photo')
            ? $this->storeUploadedFile($request->file('photo'), 'doctors')
            : null;

        $doctor = DB::transaction(function () use ($validated, $photoPath) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'doctor',
            ]);

            return Doctor::create([
                'user_id'        => $user->id,
                'specialty'      => $validated['specialty'],
                'phone'          => $validated['phone'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'bio'            => $validated['bio'] ?? null,
                'photo'          => $photoPath,
                'working_days'   => $validated['working_days'] ?? null,
                'working_hours_start' => $validated['working_hours_start'] ?? null,
                'working_hours_end'   => $validated['working_hours_end'] ?? null,
                'treatment_time' => $validated['treatment_time'] ?? 30,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Doctor created successfully.',
            'data'    => $this->formatDoctor($doctor->load('user')),
        ], 201);
    }

    public function show(Doctor $doctor)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->formatDoctor($doctor->load('user')),
        ]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'email'          => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($doctor->user_id)],
            'specialty'      => 'sometimes|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'bio'            => 'nullable|string',
            'is_active'      => 'sometimes|boolean',
            'password'       => 'nullable|string|min:8',
            'photo'          => 'nullable|image|max:2048',
            'paid_amount'    => 'nullable|numeric|min:0',
            'working_days'   => 'nullable|array',
            'working_days.*' => 'string',
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end'   => 'nullable|date_format:H:i',
            'treatment_time' => 'nullable|integer|min:1',
        ]);

        // Update user details
        $userUpdate = [];
        if (isset($validated['name'])) $userUpdate['name'] = $validated['name'];
        if (isset($validated['email'])) $userUpdate['email'] = $validated['email'];
        if (!empty($validated['password'])) $userUpdate['password'] = Hash::make($validated['password']);

        if (!empty($userUpdate)) {
            $doctor->user->update($userUpdate);
        }

        // Update doctor profile
        $doctorUpdate = [];
        foreach (['specialty', 'phone', 'license_number', 'bio', 'is_active', 'paid_amount', 'working_days', 'working_hours_start', 'working_hours_end', 'treatment_time'] as $field) {
            if (array_key_exists($field, $validated)) {
                $doctorUpdate[$field] = $validated[$field];
            }
        }

        if ($request->hasFile('photo')) {
            $doctorUpdate['photo'] = $this->storeUploadedFile($request->file('photo'), 'doctors');
        }

        if (!empty($doctorUpdate)) {
            $doctor->update($doctorUpdate);
        }

        return response()->json([
            'success' => true,
            'message' => 'Doctor updated successfully.',
            'data'    => $this->formatDoctor($doctor->fresh('user')),
        ]);
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->user->delete(); // cascade deletes doctor
        return response()->json(['success' => true, 'message' => 'Doctor deleted successfully.']);
    }

    private function formatDoctor(Doctor $doctor): array
    {
        return [
            'id'             => $doctor->id,
            'name'           => $doctor->user->name,
            'email'          => $doctor->user->email,
            'specialty'      => $doctor->specialty,
            'phone'          => $doctor->phone,
            'license_number' => $doctor->license_number,
            'bio'            => $doctor->bio,
            'is_active'      => $doctor->is_active,
            'photo'          => $doctor->photo ? asset('storage/' . $doctor->photo) : null,
            'working_days'   => $doctor->working_days,
            'working_hours_start' => $doctor->working_hours_start ? substr($doctor->working_hours_start, 0, 5) : null,
            'working_hours_end'   => $doctor->working_hours_end ? substr($doctor->working_hours_end, 0, 5) : null,
            'treatment_time' => $doctor->treatment_time,
        ];
    }
}
