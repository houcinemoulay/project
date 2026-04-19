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

                <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px; font-size: 15px;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
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
@endsection
