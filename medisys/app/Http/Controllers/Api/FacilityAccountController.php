<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Services\StaffAccountService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CRUD shared by the facility resources (laboratories, pharmacies): each one is a
 * profile row plus a `users` row used to log into the facility portal.
 */
abstract class FacilityAccountController extends Controller
{
    use ApiResponses;

    public function __construct(protected StaffAccountService $accounts)
    {
    }

    /** Eloquent model holding the facility profile. */
    abstract protected function modelClass(): string;

    /** Role and login-code prefix given to the facility user. */
    abstract protected function role(): string;

    abstract protected function codePrefix(): string;

    /** Human readable name used in messages, e.g. "Laboratory". */
    abstract protected function label(): string;

    /** Validation rules specific to this facility, merged over the shared ones. */
    protected function storeRules(): array
    {
        return [];
    }

    protected function updateRules(): array
    {
        return [];
    }

    protected function facilityIndex(): JsonResponse
    {
        return $this->ok($this->modelClass()::with('user')->get());
    }

    protected function facilityStore(Request $request): JsonResponse
    {
        $validated = $request->validate(array_merge([
            'name'                => 'required|string|max:255',
            'address'             => 'required|string',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'required|email|unique:users,email',
            'password'            => 'required|string|min:6',
            'license_number'      => 'nullable|string|max:100',
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end'   => 'nullable|date_format:H:i',
        ], $this->storeRules()));

        $code = StaffAccountService::randomCode($this->codePrefix());

        $user = $this->accounts->createUser($this->role(), [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
        ], $code);

        $profile = $validated;
        unset($profile['password']);
        $profile['user_id'] = $user->id;

        $facility = $this->modelClass()::create($profile);

        return $this->created($facility, extra: ['code' => $code]);
    }

    protected function facilityShow(Model $facility): JsonResponse
    {
        return $this->ok($facility->load('user'));
    }

    protected function facilityUpdate(Request $request, Model $facility): JsonResponse
    {
        $validated = $request->validate(array_merge([
            'name'                => 'sometimes|string|max:255',
            'address'             => 'sometimes|string',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email',
            'password'            => 'nullable|string|min:6',
            'license_number'      => 'nullable|string|max:100',
            'is_active'           => 'boolean',
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end'   => 'nullable|date_format:H:i',
        ], $this->updateRules()));

        $facility->update($validated);
        $this->accounts->syncUser($facility->user, $validated);

        return $this->ok($facility->load('user'));
    }

    protected function facilityDestroy(Model $facility): JsonResponse
    {
        $facility->delete();

        return $this->message($this->label() . ' deleted.');
    }
}
