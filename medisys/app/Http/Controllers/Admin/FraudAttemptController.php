<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudAttempt;
use Illuminate\Http\Request;

class FraudAttemptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $reason = $request->get('reason', 'all');
        $search = $request->get('search');

        $query = FraudAttempt::orderBy('created_at', 'desc');

        if ($reason !== 'all') {
            $query->where('reason', $reason);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $fraudAttempts = $query->paginate(50);

        return view('admin.fraud-attempts.index', compact('fraudAttempts', 'reason', 'search'));
    }

    public function show($id)
    {
        $fraudAttempt = FraudAttempt::findOrFail($id);
        return view('admin.fraud-attempts.show', compact('fraudAttempt'));
    }

    public function destroy($id)
    {
        $fraudAttempt = FraudAttempt::findOrFail($id);
        $fraudAttempt->delete();

        return back()->with('success', 'Fraud attempt record deleted successfully.');
    }
}
