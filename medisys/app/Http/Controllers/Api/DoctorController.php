<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\StaffAccountService;
use App\Support\PublicFiles;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    use ApiResponses;

    public function __construct(private StaffAccountService $accounts)
    {
    }

    public function index()
    {
        $doctors = Doctor::with('user')
            ->where('is_active', true)
            ->get()
            ->map(fn($d) => $this->formatDoctor($d));

        return $this->ok($doctors);
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

        $user = $this->accounts->createUser('doctor', [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ]);

        $doctor = Doctor::create([
            'user_id'        => $user->id,
            'specialty'      => $validated['specialty'],
            'phone'          => $validated['phone'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
            'bio'            => $validated['bio'] ?? null,
            'photo'          => $request->hasFile('photo') ? PublicFiles::store($request->file('photo'), 'doctors') : null,
            'working_days'   => $validated['working_days'] ?? null,
            'working_hours_start' => $validated['working_hours_start'] ?? null,
            'working_hours_end'   => $validated['working_hours_end'] ?? null,
            'treatment_time' => $validated['treatment_time'] ?? 30,
        ]);

        return $this->created($this->formatDoctor($doctor->load('user')), 'Doctor created successfully.');
    }

    public function show(Doctor $doctor)
    {
        return $this->ok($this->formatDoctor($doctor->load('user')));
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

        $this->accounts->syncUser($doctor->user, $validated);

        // Update doctor profile
        $doctorUpdate = [];
        foreach (['specialty', 'phone', 'license_number', 'bio', 'is_active', 'paid_amount', 'working_days', 'working_hours_start', 'working_hours_end', 'treatment_time'] as $field) {
            if (array_key_exists($field, $validated)) {
                $doctorUpdate[$field] = $validated[$field];
            }
        }

        if ($request->hasFile('photo')) {
            $doctorUpdate['photo'] = PublicFiles::store($request->file('photo'), 'doctors');
        }

        if (!empty($doctorUpdate)) {
            $doctor->update($doctorUpdate);
        }

        return $this->ok($this->formatDoctor($doctor->fresh('user')), 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->user->delete(); // cascade deletes doctor

        return $this->message('Doctor deleted successfully.');
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
            'photo'          => PublicFiles::url($doctor->photo),
            'working_days'   => $doctor->working_days,
            'working_hours_start' => $doctor->working_hours_start ? substr($doctor->working_hours_start, 0, 5) : null,
            'working_hours_end'   => $doctor->working_hours_end ? substr($doctor->working_hours_end, 0, 5) : null,
            'treatment_time' => $doctor->treatment_time,
        ];
    }
}
