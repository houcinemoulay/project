<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SearchesColumns;
use App\Http\Controllers\Controller;
use App\Models\FraudAttempt;
use Illuminate\Http\Request;

class FraudAttemptController extends Controller
{
    use SearchesColumns;

    public function index(Request $request)
    {
        $reason = $request->get('reason', 'all');
        $search = $request->get('search');

        $query = FraudAttempt::orderBy('created_at', 'desc');

        if ($reason !== 'all') {
            $query->where('reason', $reason);
        }

        $this->applySearch($query, $search, ['ip_address', 'email', 'phone']);

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
