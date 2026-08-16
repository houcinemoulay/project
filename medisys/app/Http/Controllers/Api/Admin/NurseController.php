<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Concerns\ManagesNurseAccounts;
use App\Http\Controllers\Concerns\SearchesColumns;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NurseController extends Controller
{
    use ApiResponses;
    use ManagesNurseAccounts;
    use SearchesColumns;

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Display a listing of nurses for admin dashboard.
     */
    public function index(Request $request)
    {
        $query = $this->nurseQuery()
            ->select(['id', 'name', 'email', 'username', 'phone', 'department', 'license_number', 'address', 'code', 'created_at']);

        $this->applySearch($query, $request->search, ['name', 'email', 'department', 'username']);

        // Apply department filter
        if ($request->department) {
            $query->where('department', $request->department);
        }

        // Apply pagination
        $perPage = $request->per_page ?? 10;
        $nurses = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->ok($nurses->items(), extra: [
            'pagination' => [
                'current_page' => $nurses->currentPage(),
                'last_page' => $nurses->lastPage(),
                'per_page' => $nurses->perPage(),
                'total' => $nurses->total(),
                'from' => $nurses->firstItem(),
                'to' => $nurses->lastItem()
            ],
        ]);
    }

    /**
     * Get nurse statistics.
     */
    public function statistics()
    {
        $totalNurses = $this->nurseQuery()->count();
        $activeNurses = $this->nurseQuery()->count(); // All nurses are considered active
        $newThisMonth = $this->nurseQuery()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $departments = $this->nurseQuery()
            ->whereNotNull('department')
            ->distinct('department')
            ->count();

        return $this->ok(extra: [
            'total' => $totalNurses,
            'active' => $activeNurses,
            'new_this_month' => $newThisMonth,
            'departments' => $departments,
        ]);
    }

    /**
     * Get a specific nurse.
     */
    public function show($id)
    {
        $nurse = $this->findNurse($id);

        if (!$nurse) {
            return $this->notFound('Nurse not found');
        }

        return $this->ok($nurse);
    }

    /**
     * Store a new nurse.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->nurseRules());

        $nurse = User::create($this->nurseUserAttributes($validated, creating: true));

        // Store additional nurse metadata
        $nurse->update($this->nurseProfileAttributes($validated));

        return $this->ok($nurse, 'Nurse created successfully');
    }

    /**
     * Update a nurse.
     */
    public function update(Request $request, $id)
    {
        $nurse = $this->findNurse($id);

        if (!$nurse) {
            return $this->notFound('Nurse not found');
        }

        $validated = $request->validate($this->nurseRules($nurse));

        $nurse->update(array_merge(
            $this->nurseUserAttributes($validated, creating: false),
            $this->nurseProfileAttributes($validated)
        ));

        return $this->ok($nurse, 'Nurse updated successfully');
    }

    /**
     * Delete a nurse.
     */
    public function destroy($id)
    {
        $nurse = $this->findNurse($id);

        if (!$nurse) {
            return $this->notFound('Nurse not found');
        }

        $nurse->delete();

        return $this->message('Nurse deleted successfully');
    }

    private function findNurse($id): ?User
    {
        return $this->nurseQuery()->where('id', $id)->first();
    }
}
