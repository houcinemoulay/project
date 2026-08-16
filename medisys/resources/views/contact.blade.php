@extends('layouts.minimal')

@section('title', 'Contact Us')
@section('page-title', 'Contact Us')

@section('content')
<div class="contact-container">
    <!-- Contact Form Section -->
    <div class="contact-form-section">
        <div class="contact-card">
            <div class="card-header">
                <h3><i class="fas fa-envelope"></i> Send us a Message</h3>
                <p>Choose a message template or write your own</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/contact" method="POST" class="contact-form">
                @csrf
                
                <!-- Message Type Selection -->
                <div class="form-group">
                    <label class="form-label">What type of message would you like to send?</label>
                    <div class="message-types">
                        <div class="message-type-option" onclick="selectMessageType('general')">
                            <div class="type-icon">
                                <i class="fas fa-comment"></i>
                            </div>
                            <div class="type-content">
                                <h4>General Inquiry</h4>
                                <p>General questions about our services</p>
                            </div>
                        </div>
                        
                        <div class="message-type-option" onclick="selectMessageType('technical')">
                            <div class="type-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="type-content">
                                <h4>Technical Support</h4>
                                <p>Having trouble with the system?</p>
                            </div>
                        </div>
                        
                        <div class="message-type-option" onclick="selectMessageType('appointment')">
                            <div class="type-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="type-content">
                                <h4>Appointment Issues</h4>
                                <p>Problems with booking or appointments</p>
                            </div>
                        </div>
                        
                        <div class="message-type-option" onclick="selectMessageType('account')">
                            <div class="type-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="type-content">
                                <h4>Account Help</h4>
                                <p>Login, registration, or profile issues</p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="message_type" id="message_type" value="general">
                </div>

                <!-- Personal Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter your full name" required value="{{ old('name') }}">
                        @error('name') <span class="error-text">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="your.email@example.com" required value="{{ old('email') }}">
                        @error('email') <span class="error-text">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Subject -->
                <div class="form-group">
                    <label class="form-label">Subject *</label>
                    <input type="text" name="subject" class="form-control" id="subject" placeholder="How can we help you?" required value="{{ old('subject') }}">
                    @error('subject') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <!-- Message Template -->
                <div class="form-group">
                    <label class="form-label">Message *</label>
                    <textarea name="message" class="form-control" id="message" rows="6" placeholder="Tell us more about your issue..." required>{{ old('message') }}</textarea>
                    @error('message') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <!-- Honeypot fields (hidden from humans) -->
                <div style="display: none;">
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
                    <input type="email" name="confirm_email" value="" tabindex="-1" autocomplete="off">
                </div>

                <!-- Submit Button -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="contact-info-section">
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="info-content">
                    <h4>Email Support</h4>
                    <p>support@medisys.com</p>
                    <small>We respond within 24 hours</small>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="info-content">
                    <h4>Phone Support</h4>
                    <p>+213 21 23 45 67</p>
                    <small>Mon-Fri, 9AM-5PM</small>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="info-content">
                    <h4>Response Time</h4>
                    <p>Within 24 hours</p>
                    <small>We're committed to quick responses</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contact-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.contact-header {
  text-align: center;
  margin-bottom: 40px;
}

.contact-header h1 {
  font-size: 36px;
  color: #1e293b;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.contact-header p {
  font-size: 18px;
  color: #64748b;
  margin: 0;
}

.contact-form-section {
  margin-bottom: 40px;
}

.contact-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  overflow: hidden;
}

.card-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 30px;
  text-align: center;
}

.card-header h3 {
  margin: 0 0 10px 0;
  font-size: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.card-header p {
  margin: 0;
  opacity: 0.9;
}

.contact-form {
  padding: 40px;
}

.message-types {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.message-type-option {
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.message-type-option:hover {
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.message-type-option.selected {
  border-color: #667eea;
  background: #f8fafc;
}

.type-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px;
  font-size: 20px;
}

.type-content h4 {
  margin: 0 0 5px 0;
  font-size: 16px;
  color: #1e293b;
}

.type-content p {
  margin: 0;
  font-size: 14px;
  color: #64748b;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.form-group {
  margin-bottom: 25px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #374151;
}

.form-control {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.error-text {
  color: #ef4444;
  font-size: 12px;
  margin-top: 5px;
  display: block;
}

.alert {
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.alert-success {
  background: #f0fdf4;
  color: #166534;
  border: 1px solid #bbf7d0;
}

.alert-error {
  background: #fff1f2;
  color: #9f1239;
  border: 1px solid #fecdd3;
}

.alert-warning {
  background: #fffbeb;
  color: #92400e;
  border: 1px solid #fde68a;
}

.form-actions {
  display: flex;
  justify-content: center;
  margin-top: 30px;
}

.btn {
  padding: 14px 32px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.contact-info-section {
  margin-top: 40px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.info-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 20px;
}

.info-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
}

.info-content h4 {
  margin: 0 0 5px 0;
  font-size: 18px;
  color: #1e293b;
}

.info-content p {
  margin: 0 0 5px 0;
  font-size: 16px;
  color: #374151;
  font-weight: 600;
}

.info-content small {
  color: #64748b;
  font-size: 14px;
}

@media (max-width: 768px) {
  .contact-header h1 {
    font-size: 28px;
  }
  
  .message-types {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .contact-form {
    padding: 20px;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .info-card {
    flex-direction: column;
    text-align: center;
  }
}
</style>

<script>
// Message templates
const messageTemplates = {
  general: {
    subject: 'General Inquiry',
    message: 'Hello,\n\nI have a general question about your services and would like to learn more about what you offer.\n\nCould you please provide me with more information?\n\nThank you.'
  },
  technical: {
    subject: 'Technical Support Request',
    message: 'Hello,\n\nI am experiencing technical issues with the system. Here are the details:\n\n[Please describe your technical issue here]\n\nCould you please assist me with resolving this problem?\n\nThank you.'
  },
  appointment: {
    subject: 'Appointment Related Issue',
    message: 'Hello,\n\nI need help with an appointment-related matter. Here\'s what I need assistance with:\n\n[Please describe your appointment issue]\n\nCould you please help me with this?\n\nThank you.'
  },
  account: {
    subject: 'Account Help Request',
    message: 'Hello,\n\nI need assistance with my account. Here\'s what I\'m having trouble with:\n\n[Please describe your account issue]\n\nCould you please help me resolve this?\n\nThank you.'
  }
};

// Select message type
function selectMessageType(type) {
  // Remove selected class from all options
  document.querySelectorAll('.message-type-option').forEach(option => {
    option.classList.remove('selected');
  });
  
  // Add selected class to clicked option
  event.currentTarget.classList.add('selected');
  
  // Update hidden field
  document.getElementById('message_type').value = type;
  
  // Fill in template
  const template = messageTemplates[type];
  document.getElementById('subject').value = template.subject;
  document.getElementById('message').value = template.message;
}

// Initialize with general message type
document.addEventListener('DOMContentLoaded', function() {
  selectMessageType('general');
});
</script>
@endsection
