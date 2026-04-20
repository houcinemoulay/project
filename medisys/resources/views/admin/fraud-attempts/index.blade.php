@extends('layouts.app')

@section('title', 'Fraud Attempts')
@section('page-title', 'Fraud Attempts Management')

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt" style="color: var(--danger); margin-right: 8px;"></i>
                    Fraud Attempts
                </h3>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div style="display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;">
                <form method="GET" style="display: flex; gap: 12px; flex: 1; min-width: 300px;">
                    <select name="reason" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: white;">
                        <option value="all" {{ $reason == 'all' ? 'selected' : '' }}>All Reasons</option>
                        <option value="rate_limit" {{ $reason == 'rate_limit' ? 'selected' : '' }}>Rate Limit</option>
                        <option value="duplicate" {{ $reason == 'duplicate' ? 'selected' : '' }}>Duplicate</option>
                        <option value="honeypot" {{ $reason == 'honeypot' ? 'selected' : '' }}>Honeypot</option>
                        <option value="suspicious" {{ $reason == 'suspicious' ? 'selected' : '' }}>Suspicious</option>
                    </select>
                    <input type="text" name="search" placeholder="Search IP, email, phone..." value="{{ $search }}" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; flex: 1;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>

            <!-- Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>IP Address</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Reason</th>
                        <th>User Agent</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fraudAttempts as $attempt)
                        <tr>
                            <td style="font-family: monospace; font-size: 12px;">{{ $attempt->ip_address }}</td>
                            <td>{{ $attempt->email ?? '-' }}</td>
                            <td>{{ $attempt->phone ?? '-' }}</td>
                            <td>
                                <span style="background: 
                                    @switch($attempt->reason)
                                        @case('rate_limit') #f59e0b @break
                                        @case('duplicate') #ef4444 @break
                                        @case('honeypot') #8b5cf6 @break
                                        @default #6b7280 @endswitch
                                    ; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                    {{ strtoupper($attempt->reason) }}
                                </span>
                            </td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
                                {{ $attempt->user_agent }}
                            </td>
                            <td>{{ $attempt->created_at->format('M j, Y H:i') }}</td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="{{ route('admin.fraud-attempts.show', $attempt->id) }}" class="btn btn-outline btn-sm">View</a>
                                    <form method="POST" action="{{ route('admin.fraud-attempts.destroy', $attempt->id) }}" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                No fraud attempts found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($fraudAttempts->hasPages())
                <div style="margin-top: 24px;">
                    {{ $fraudAttempts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
