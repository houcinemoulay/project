@extends('layouts.app')

@section('title', 'Fraud Attempt Details')
@section('page-title', 'Fraud Attempt Details')

@section('content')
<div style="max-width: 1000px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt" style="color: var(--danger); margin-right: 8px;"></i>
                    Fraud Attempt Details
                </h3>
                <a href="{{ route('admin.fraud-attempts.index') }}" class="btn btn-outline">Back to List</a>
            </div>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <!-- Basic Info -->
                <div>
                    <h4 style="margin-bottom: 16px; color: var(--text);">Basic Information</h4>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <strong>IP Address:</strong>
                            <span style="font-family: monospace; background: var(--bg-secondary); padding: 4px 8px; border-radius: 4px;">
                                {{ $fraudAttempt->ip_address }}
                            </span>
                        </div>
                        @if($fraudAttempt->email)
                            <div>
                                <strong>Email:</strong> {{ $fraudAttempt->email }}
                            </div>
                        @endif
                        @if($fraudAttempt->phone)
                            <div>
                                <strong>Phone:</strong> {{ $fraudAttempt->phone }}
                            </div>
                        @endif
                        <div>
                            <strong>Reason:</strong>
                            <span style="background: 
                                @switch($fraudAttempt->reason)
                                    @case('rate_limit') #f59e0b @break
                                    @case('duplicate') #ef4444 @break
                                    @case('honeypot') #8b5cf6 @break
                                    @default #6b7280 @endswitch
                                ; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                {{ strtoupper($fraudAttempt->reason) }}
                            </span>
                        </div>
                        <div>
                            <strong>Date:</strong> {{ $fraudAttempt->created_at->format('M j, Y H:i:s') }}
                        </div>
                        <div>
                            <strong>Blocked:</strong>
                            <span style="background: {{ $fraudAttempt->blocked ? '#ef4444' : '#10b981' }}; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                {{ $fraudAttempt->blocked ? 'YES' : 'NO' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- User Agent -->
                <div>
                    <h4 style="margin-bottom: 16px; color: var(--text);">User Agent</h4>
                    <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px; font-family: monospace; font-size: 12px; word-break: break-all;">
                        {{ $fraudAttempt->user_agent }}
                    </div>
                </div>
            </div>

            <!-- Payload Data -->
            @if($fraudAttempt->payload)
                <div style="margin-top: 32px;">
                    <h4 style="margin-bottom: 16px; color: var(--text);">Submitted Data</h4>
                    <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px;">
                        <pre style="margin: 0; font-family: monospace; font-size: 12px; overflow-x: auto;">{{ json_encode($fraudAttempt->payload, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div style="margin-top: 32px; display: flex; gap: 12px;">
                <form method="POST" action="{{ route('admin.fraud-attempts.destroy', $fraudAttempt->id) }}" onsubmit="return confirm('Are you sure you want to delete this fraud attempt record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Record</button>
                </form>
                <a href="{{ route('admin.fraud-attempts.index') }}" class="btn btn-outline">Back to List</a>
            </div>
        </div>
    </div>
</div>
@endsection
