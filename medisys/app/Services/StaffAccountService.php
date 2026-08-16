<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates and keeps in sync the `users` row backing a staff profile
 * (doctor, laboratory, pharmacy, ...).
 */
class StaffAccountService
{
    /**
     * Login code handed to the staff member, e.g. "LABA1B2C3".
     */
    public static function randomCode(string $prefix): string
    {
        return $prefix . strtoupper(Str::random(6));
    }

    /**
     * @param  array<string, mixed>  $attributes  name/email/password plus any extra user columns
     */
    public function createUser(string $role, array $attributes, ?string $code = null): User
    {
        $attributes['role'] = $role;
        $attributes['password'] = Hash::make($attributes['password']);

        if ($code !== null) {
            $attributes['code'] = $code;
        }

        return User::create($attributes);
    }

    /**
     * Copy the account fields present in $attributes onto the linked user.
     *
     * @param  array<string, mixed>  $attributes
     * @param  list<string>  $fields
     */
    public function syncUser(?User $user, array $attributes, array $fields = ['name', 'email', 'password']): void
    {
        if (!$user) {
            return;
        }

        $update = [];

        foreach ($fields as $field) {
            if (empty($attributes[$field])) {
                continue;
            }

            $update[$field] = $field === 'password'
                ? Hash::make($attributes[$field])
                : $attributes[$field];
        }

        if (!empty($update)) {
            $user->update($update);
        }
    }
}
