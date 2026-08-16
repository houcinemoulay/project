<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesNurseAccounts;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NurseController extends Controller
{
    use ManagesNurseAccounts;

    public function __construct()
    {
        // Middleware is handled in routes/web.php
    }

    /**
     * Display the nurse management interface.
     */
    public function interface()
    {
        return view('admin.nurses.interface');
    }

    /**
     * Display a listing of nurses.
     */
    public function index()
    {
        $nurses = $this->nurseQuery()
            ->with('nurse')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.nurses.index', compact('nurses'));
    }

    /**
     * Show the form for creating a new nurse.
     */
    public function create()
    {
        return view('admin.nurses.create');
    }

    /**
     * Store a newly created nurse in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->nurseRules());

        $nurse = User::create($this->nurseUserAttributes($validated, creating: true));

        // Create nurse profile linked to user
        $nurse->nurse()->create($this->nurseProfileAttributes($validated));

        return redirect()
            ->route('admin.nurses.index')
            ->with('success', 'Nurse account created successfully.');
    }

    /**
     * Display the specified nurse.
     */
    public function show(User $nurse)
    {
        if ($nurse->role !== 'nurse') {
            abort(404);
        }

        $nurse->load('nurse');
        return view('admin.nurses.show', compact('nurse'));
    }

    /**
     * Show the form for editing the specified nurse.
     */
    public function edit(User $nurse)
    {
        if ($nurse->role !== 'nurse') {
            abort(404);
        }

        $nurse->load('nurse');
        return view('admin.nurses.edit', compact('nurse'));
    }

    /**
     * Update the specified nurse in storage.
     */
    public function update(Request $request, User $nurse)
    {
        if ($nurse->role !== 'nurse') {
            abort(404);
        }

        $validated = $request->validate($this->nurseRules($nurse));

        $nurse->update($this->nurseUserAttributes($validated, creating: false));

        // Update nurse profile
        $nurse->nurse()->updateOrCreate(
            ['user_id' => $nurse->id],
            $this->nurseProfileAttributes($validated)
        );

        return redirect()
            ->route('admin.nurses.index')
            ->with('success', 'Nurse account updated successfully.');
    }

    /**
     * Remove the specified nurse from storage.
     */
    public function destroy(User $nurse)
    {
        if ($nurse->role !== 'nurse') {
            abort(404);
        }

        $nurse->delete();

        return redirect()
            ->route('admin.nurses.index')
            ->with('success', 'Nurse account deleted successfully.');
    }
}
