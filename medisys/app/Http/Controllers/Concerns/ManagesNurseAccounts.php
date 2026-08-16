<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

trait ManagesNurseAccounts
{
    /**
     * Validation rules for the nurse account form. On update the password
     * becomes optional and uniqueness ignores the nurse being edited.
     */
    protected function nurseRules(?User $nurse = null): array
    {
        $unique = fn () => $nurse
            ? Rule::unique('users')->ignore($nurse->id)
            : Rule::unique('users');

        return [
            'name'           => 'required|string|max:255',
            'email'          => ['required', 'string', 'email', 'max:255', $unique()],
            'username'       => ['required', 'string', 'max:255', $unique()],
            'password'       => ($nurse ? 'nullable' : 'required') . '|string|min:8|confirmed',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'department'     => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
        ];
    }

    /**
     * User columns for a nurse account. The password is only included when
     * supplied, so updates keep the existing one.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function nurseUserAttributes(array $validated, bool $creating): array
    {
        $attributes = [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'username' => $validated['username'],
            'phone'    => $validated['phone'] ?? null,
            'address'  => $validated['address'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $attributes['password'] = Hash::make($validated['password']);
        }

        if ($creating) {
            $attributes['role'] = 'nurse';
            $attributes['code'] = 'NURSE' . strtoupper(uniqid());
        }

        return $attributes;
    }

    /**
     * Nurse-specific profile columns.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function nurseProfileAttributes(array $validated): array
    {
        return [
            'department'     => $validated['department'] ?? null,
            'license_number' => $validated['license_number'] ?? null,
        ];
    }

    /** Base query restricted to nurse users. */
    protected function nurseQuery()
    {
        return User::where('role', 'nurse');
    }
}
