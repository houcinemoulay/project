@extends('layouts.app')

@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages Management')

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">
                    <i class="fas fa-envelope-open-text" style="color: var(--accent); margin-right: 8px;"></i>
                    Contact Messages
                </h3>
                <div style="display: flex; gap: 12px; align-items: center;">
                    @if($unreadCount > 0)
                        <span style="background: var(--accent); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            {{ $unreadCount }} Unread
                        </span>
                    @endif
                    <a href="{{ route('contact.show') }}" class="btn btn-outline-primary btn-sm" style="padding: 8px 16px;">
                        <i class="fas fa-external-link-alt"></i> View Contact Form
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div style="display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; padding: 16px; background: var(--bg-secondary); border-radius: 8px;">
                <form method="GET" style="display: flex; gap: 12px; flex: 1; min-width: 300px;">
                    <select name="status" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; background: white;">
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Messages</option>
                        <option value="new" {{ $status == 'new' ? 'selected' : '' }}>New Messages</option>
                        <option value="read" {{ $status == 'read' ? 'selected' : '' }}>Read Messages</option>
                    </select>
                    <input type="text" name="search" placeholder="Search email or subject..." value="{{ $search }}" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; flex: 1;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline">Clear</a>
                </form>
            </div>

            @if(session('success'))
                <div style="background: #f0fdf4; color: #166534; padding: 16px; border-radius: 0; margin: 0; border-left: 4px solid #22c55e; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg); border-bottom: 2px solid var(--border);">
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: var(--text);">Status</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: var(--text);">Name</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: var(--text);">Email</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: var(--text);">Role</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: var(--text);">Subject</th>
                            <th style="padding: 16px; text-align: left; font-weight: 600; color: var(--text);">Date</th>
                            <th style="padding: 16px; text-align: center; font-weight: 600; color: var(--text);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr style="border-bottom: 1px solid var(--border); {{ $message->status === 'new' ? 'background: #fef3c7;' : '' }}">
                                <td style="padding: 16px;">
                                    @if($message->status === 'new')
                                        <span style="background: var(--accent); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                            NEW
                                        </span>
                                    @else
                                        <span style="background: var(--success); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                            READ
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 16px; font-weight: 500;">
                                    {{ $message->name }}
                                    @if($message->user_id)
                                        <i class="fas fa-user-check" style="color: var(--success); margin-left: 6px; font-size: 12px;" title="Registered User"></i>
                                    @endif
                                </td>
                                <td style="padding: 16px; color: var(--text-muted); font-size: 14px;">
                                    {{ $message->email }}
                                </td>
                                <td style="padding: 16px;">
                                    @if($message->user)
                                        <span style="background: 
                                            @switch($message->user->role)
                                                @case('admin') var(--danger) @break
                                                @case('doctor') var(--success) @break
                                                @case('patient') var(--primary) @break
                                                @case('pharmacy') var(--warning) @break
                                                @case('lab') var(--info) @break
                                                @default var(--secondary) @endswitch
                                            ; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                            {{ ucfirst($message->user->role) }}
                                        </span>
                                    @else
                                        <span style="background: var(--secondary); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                            Guest
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 16px; font-weight: 500;">
                                    {{ Str::limit($message->subject, 50) }}
                                </td>
                                <td style="padding: 16px; color: var(--text-muted); font-size: 14px;">
                                    {{ $message->created_at->format('M j, Y H:i') }}
                                </td>
                                <td style="padding: 16px; text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <a href="{{ route('admin.contact-messages.show', $message) }}" 
                                           class="btn btn-sm btn-primary" 
                                           style="padding: 6px 12px; font-size: 12px;"
                                           title="View Message">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($message->status === 'read')
                                            <form method="POST" action="{{ route('admin.contact-messages.mark-unread', $message) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" 
                                                        style="padding: 6px 12px; font-size: 12px;"
                                                        title="Mark as Unread"
                                                        onclick="return confirm('Mark this message as unread?')">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.contact-messages.mark-read', $message) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        style="padding: 6px 12px; font-size: 12px;"
                                                        title="Mark as Read"
                                                        onclick="return confirm('Mark this message as read?')">
                                                    <i class="fas fa-envelope-open"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    style="padding: 6px 12px; font-size: 12px;"
                                                    title="Delete Message"
                                                    onclick="return confirm('Are you sure you want to delete this message?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 48px; text-align: center; color: var(--text-muted);">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                                    <div style="font-size: 18px; margin-bottom: 8px;">No contact messages yet</div>
                                    <div style="font-size: 14px;">Messages from the contact form will appear here</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($messages->hasPages())
                <div style="padding: 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
                    {{ $messages->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
