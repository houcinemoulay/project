<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SHIFA — Public Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --primary: #0f3460;
  --secondary: #533483;
  --accent: #e94560;
  --bg: #f8fafc;
  --text: #1e293b;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Inter', sans-serif;
  background-color: var(--bg);
  color: var(--text);
  line-height: 1.6;
}
.navbar {
  background: white; padding: 20px 40px;
  display: flex; justify-content: space-between; align-items: center;
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.logo { font-size: 24px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 10px; }
.container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
.booking-card {
  background: white; border-radius: 24px; padding: 40px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.08);
}
h1 { font-size: 28px; font-weight: 800; margin-bottom: 8px; color: var(--primary); text-align: center; }
p.subtitle { color: #64748b; text-align: center; margin-bottom: 40px; }

.form-group { margin-bottom: 24px; }
.form-label { display: block; font-size: 14px; font-weight: 600; color: #334155; margin-bottom: 8px; }
.form-control {
  width: 100%; padding: 14px 16px; border: 1.5px solid #e2e8f0;
  border-radius: 12px; font-size: 15px; font-family: inherit; color: var(--text);
  transition: all 0.2s; background: #f8fafc;
}
.form-control:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(15,52,96,0.06); }

.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }

.slots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; margin-top: 10px; }
.slot-btn {
  padding: 10px; border: 1.5px solid #e2e8f0; border-radius: 8px;
  background: white; color: var(--text); font-size: 13px; font-weight: 600;
  cursor: pointer; transition: all 0.2s; text-align: center;
}
.slot-btn:hover { border-color: var(--primary); color: var(--primary); background: #f0f4ff; }
.slot-btn.selected { background: var(--primary); color: white; border-color: var(--primary); }

.btn-book {
  width: 100%; padding: 16px; background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white; border: none; border-radius: 14px; font-size: 16px; font-weight: 700;
  cursor: pointer; transition: all 0.2s; margin-top: 20px;
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.btn-book:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(15,52,96,0.2); }
.btn-book:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; box-shadow: none; }

.alert { padding: 16px; border-radius: 12px; margin-bottom: 24px; font-size: 14px; display: none; }
.alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.alert-error { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
</style>
</head>
<body>

<nav class="navbar">
  <div class="logo"><i class="fas fa-hospital-alt"></i> SHIFA</div>
  <div style="display:flex; gap:20px; align-items:center;">
    <a href="/contact" style="text-decoration:none; color:#64748b; font-weight:600; font-size:14px;"><i class="fas fa-envelope"></i> Contact Us</a>
    <a href="/login" style="text-decoration:none; color:#64748b; font-weight:600; font-size:14px;"><i class="fas fa-arrow-left"></i> Back to Login</a>
  </div>
</nav>

<div class="container">
  <div class="booking-card">
    <h1>Book an Appointment</h1>
    <p class="subtitle">Quick and easy booking without an account</p>

    <div class="alert alert-success" id="success-msg"></div>
    <div class="alert alert-error" id="error-msg"></div>

    <form id="booking-form">
      <div class="grid">
        <div class="form-group">
          <label class="form-label">Your Full Name</label>
          <input type="text" id="guest_name" class="form-control" placeholder="John Doe" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="text" id="guest_phone" class="form-control" placeholder="0555 XX XX XX" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Select Doctor</label>
        <select id="doctor_id" class="form-control" required>
          <option value="">Choose a doctor...</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Select Date</label>
        <input type="date" id="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
      </div>

      <div id="slots-section" style="display:none;" class="form-group">
        <label class="form-label">Available Time Slots</label>
        <div class="slots-grid" id="slots-container"></div>
        <input type="hidden" id="selected_time" required>
      </div>

      <div class="form-group">
        <label class="form-label">Reason for Visit (Optional)</label>
        <textarea id="reason" class="form-control" rows="3" placeholder="Brief description..."></textarea>
      </div>

      <button type="submit" class="btn-book" id="submit-btn" disabled>
        <i class="fas fa-calendar-check"></i> Confirm Booking
      </button>
    </form>
  </div>
</div>

<script>
let doctors = [];

async function loadDoctors() {
  const r = await fetch('/api/public/doctors');
  const d = await r.json();
  doctors = d.data || [];
  const select = document.getElementById('doctor_id');
  doctors.forEach(doc => {
    const opt = document.createElement('option');
    opt.value = doc.id;
    opt.textContent = `${doc.name} (${doc.specialty})`;
    select.appendChild(opt);
  });
}

async function loadSlots() {
  const doctorId = document.getElementById('doctor_id').value;
  const date = document.getElementById('booking_date').value;
  
  if (!doctorId || !date) return;

  const container = document.getElementById('slots-container');
  container.innerHTML = '<p style="font-size:12px; color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Checking availability...</p>';
  document.getElementById('slots-section').style.display = 'block';
  document.getElementById('selected_time').value = '';
  document.getElementById('submit-btn').disabled = true;

  try {
    const r = await fetch(`/api/public/available-slots?doctor_id=${doctorId}&date=${date}`);
    const d = await r.json();
    const slots = d.slots || [];
    const suggestion = d.suggestion;

    if (slots.length === 0) {
      let html = '<p style="grid-column:1/-1; color:#e94560; font-size:13px; font-weight:600;">No slots available for this day.</p>';
      if (suggestion) {
        html += `<div style="grid-column:1/-1; margin-top:12px; padding:16px; background:#f0f4ff; border-radius:12px; border:1px solid #dbeafe;">
          <p style="font-size:13px; color:#1e40af; margin-bottom:10px;"><i class="fas fa-lightbulb"></i> Next available: <strong>${suggestion.date} at ${suggestion.time}</strong></p>
          <button type="button" class="slot-btn selected" style="width:auto; padding:8px 16px;" onclick="applySuggestion('${suggestion.date}', '${suggestion.time}')">Switch to this slot</button>
        </div>`;
      }
      container.innerHTML = html;
    } else {
      container.innerHTML = slots.map(s => `<button type="button" class="slot-btn" onclick="selectSlot(this, '${s}')">${s}</button>`).join('');
    }
  } catch (e) {
    container.innerHTML = '<p style="color:#e94560;">Error loading slots.</p>';
  }
}

function selectSlot(btn, time) {
  document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('selected_time').value = time;
  document.getElementById('submit-btn').disabled = false;
}

function applySuggestion(date, time) {
  document.getElementById('booking_date').value = date;
  loadSlots().then(() => {
    // Find the button and select it
    const buttons = document.querySelectorAll('.slot-btn');
    for (let b of buttons) {
      if (b.textContent === time) {
        selectSlot(b, time);
        break;
      }
    }
  });
}

document.getElementById('doctor_id').addEventListener('change', loadSlots);
document.getElementById('booking_date').addEventListener('change', loadSlots);

document.getElementById('booking-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('submit-btn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;
  hideAlerts();

  const payload = {
    guest_name: document.getElementById('guest_name').value,
    guest_phone: document.getElementById('guest_phone').value,
    doctor_id: document.getElementById('doctor_id').value,
    scheduled_at: `${document.getElementById('booking_date').value} ${document.getElementById('selected_time').value}:00`,
    reason: document.getElementById('reason').value
  };

  try {
    const r = await fetch('/api/public/book-appointment', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: JSON.stringify(payload)
    });
    const d = await r.json();
    if (d.success) {
      showSuccess('Appointment booked successfully! We will contact you shortly.');
      document.getElementById('booking-form').reset();
      document.getElementById('slots-section').style.display = 'none';
    } else {
      showError(d.message || 'Error booking appointment.');
    }
  } catch (e) {
    showError('Connection error.');
  } finally {
    btn.innerHTML = '<i class="fas fa-calendar-check"></i> Confirm Booking';
  }
});

function showError(msg) { const el = document.getElementById('error-msg'); el.textContent = msg; el.style.display = 'block'; }
function showSuccess(msg) { const el = document.getElementById('success-msg'); el.textContent = msg; el.style.display = 'block'; }
function hideAlerts() { document.getElementById('error-msg').style.display = 'none'; document.getElementById('success-msg').style.display = 'none'; }

loadDoctors();
</script>

</body>
</html>
