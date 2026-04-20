@extends('layouts.app')

@section('title', 'Contact Us')
@section('page-title', 'Contact Us')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-envelope-open-text" style="color: var(--accent); margin-right: 8px;"></i> Send us a Message</h3>
        </div>
        <div class="card-body" style="padding: 30px;">
            @if(session('success'))
                <div style="background: #f0fdf4; color: #166534; padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div style="background: #fff1f2; color: #9f1239; padding: 16px; border-radius: 12px; margin-bottom: 24px; border: 1px solid #fecdd3; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="/contact" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" required value="{{ old('name') }}">
                        @error('name') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com" required value="{{ old('email') }}">
                        @error('email') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="How can we help you?" required value="{{ old('subject') }}">
                    @error('subject') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="6" placeholder="Write your message here..." required style="resize: vertical;">{{ old('message') }}</textarea>
                    @error('message') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <!-- Honeypot fields (hidden from humans) -->
                <div style="display: none;">
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
                    <input type="email" name="confirm_email" value="" tabindex="-1" autocomplete="off">
                </div>

                <!-- reCAPTCHA -->
                <div class="form-group">
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.sitekey') }}"></div>
                    @error('recaptcha') <span style="color: var(--danger); font-size: 11px;">{{ $message }}</span> @enderror
                </div>

                <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 15px;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Contact Messages (for authenticated users) -->
    <div id="recent-messages-section" style="margin-top: 32px; display: none;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history" style="color: var(--accent); margin-right: 8px;"></i>
                    Your Recent Messages
                </h3>
                <span style="color: var(--text-muted); font-size: 14px;">Last 5 messages</span>
            </div>
            <div class="card-body" style="padding: 0;">
                <div id="recent-messages-loading" style="padding: 24px; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-spinner fa-spin"></i> Loading your recent messages...
                </div>
                <div id="recent-messages-list" style="display: none;">
                    <!-- Messages will be loaded here -->
                </div>
                <div id="recent-messages-empty" style="display: none; padding: 24px; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                    <div style="font-size: 18px; margin-bottom: 8px;">No messages yet</div>
                    <div style="font-size: 14px;">Send your first message using the form above</div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 32px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-envelope"></i></div>
            <div>
                <div class="stat-label">Direct Email</div>
                <div class="stat-value" style="font-size: 14px; margin-top: 4px;">alexandermail4334@gmail.com</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-label">Response Time</div>
                <div class="stat-value" style="font-size: 14px; margin-top: 4px;">Within 24 hours</div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Load user's recent contact messages
async function loadRecentMessages() {
    try {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            return; // Not authenticated
        }

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
        };

        const response = await fetch('/api/my-contact-messages', { headers });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load messages');
        }
        
        const messages = result.data;
        const section = document.getElementById('recent-messages-section');
        const loading = document.getElementById('recent-messages-loading');
        const list = document.getElementById('recent-messages-list');
        const empty = document.getElementById('recent-messages-empty');
        
        // Hide loading
        loading.style.display = 'none';
        
        if (messages && messages.length > 0) {
            // Show messages
            list.innerHTML = messages.map(msg => `
                <div style="padding: 16px; border-bottom: 1px solid var(--border); ${!msg.is_read ? 'background: #fef3c7;' : ''}">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div style="font-weight: 600; color: var(--text);">${msg.subject}</div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            ${!msg.is_read 
                                ? '<span style="background: var(--accent); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">NEW</span>'
                                : '<span style="background: var(--success); color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">READ</span>'
                            }
                        </div>
                    </div>
                    <div style="color: var(--text-muted); font-size: 14px; line-height: 1.5; white-space: pre-wrap;">${msg.message}</div>
                    <div style="margin-top: 12px; font-size: 12px; color: var(--text-muted);">
                        <i class="fas fa-clock"></i> ${msg.created_at}
                    </div>
                </div>
            `).join('');
            list.style.display = 'block';
        } else {
            // Show empty state
            empty.style.display = 'block';
        }
        
        // Show the section
        section.style.display = 'block';
        
    } catch (error) {
        console.error('Error loading recent messages:', error);
        const loading = document.getElementById('recent-messages-loading');
        loading.innerHTML = '<i class="fas fa-exclamation-circle" style="color: var(--danger);"></i> Error loading messages';
    }
}

// Load messages when page loads
document.addEventListener('DOMContentLoaded', loadRecentMessages);
</script>
@endpush
@endsection
