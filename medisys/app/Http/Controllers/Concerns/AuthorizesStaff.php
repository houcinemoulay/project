<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Auth;

trait AuthorizesStaff
{
    /**
     * Roles allowed to record and read clinical data (vitals, nurse notes).
     *
     * @return list<string>
     */
    protected function clinicalRoles(): array
    {
        return ['nurse', 'admin', 'doctor'];
    }

    protected function authorizeClinicalStaff(): void
    {
        abort_unless(in_array(Auth::user()?->role, $this->clinicalRoles(), true), 403, 'Unauthorized access');
    }
}
