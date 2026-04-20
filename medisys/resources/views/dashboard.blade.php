@extends('layouts.app')
@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-user-injured"></i></div>
    <div><div class="stat-value" id="stat-patients">—</div><div class="stat-label">Total Patients</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fas fa-user-md"></i></div>
    <div><div class="stat-value" id="stat-doctors">—</div><div class="stat-label">Active Doctors</div></div>
  </div>
  <div class="stat-card" id="card-records">
    <div class="stat-icon green"><i class="fas fa-file-medical"></i></div>
    <div><div class="stat-value" id="stat-records">—</div><div class="stat-label">Records Today</div></div>
  </div>
  <div class="stat-card" id="card-apps-today">
    <div class="stat-icon orange"><i class="fas fa-calendar-check"></i></div>
    <div><div class="stat-value" id="stat-appointments">—</div><div class="stat-label">Appointments Today</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-prescription"></i></div>
    <div><div class="stat-value" id="stat-ordonnances">—</div><div class="stat-label">Total Prescriptions</div></div>
  </div>
  <div class="stat-card admin-card" id="stat-card-contact-messages" style="display:none;">
    <div class="stat-icon" style="background:linear-gradient(135deg,#e94560,#f43f5e);color:white;"><i class="fas fa-envelope-open-text"></i></div>
    <div><div class="stat-value" id="stat-contact-messages">—</div><div class="stat-label">Contact Messages</div></div>
  </div>

  <!-- Admin Specific -->
  <div class="stat-card admin-card" id="stat-card-total-apps" style="display:none;">
    <div class="stat-icon" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);color:white;"><i class="fas fa-calendar-alt"></i></div>
    <div><div class="stat-value" id="stat-total-apps">—</div><div class="stat-label">Total Appointments</div></div>
  </div>
  <div class="stat-card admin-card" id="stat-card-rev-apps" style="display:none;">
    <div class="stat-icon" style="background:linear-gradient(135deg,#10b981,#34d399);color:white;"><i class="fas fa-money-bill-wave"></i></div>
    <div><div class="stat-value" id="stat-rev-apps">—</div><div class="stat-label">Appointments Rev. (DA)</div></div>
  </div>
  <div class="stat-card admin-card" id="stat-card-rev-ords" style="display:none;">
    <div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#a78bfa);color:white;"><i class="fas fa-pills"></i></div>
    <div><div class="stat-value" id="stat-rev-ords">—</div><div class="stat-label">Prescriptions Rev. (DA)</div></div>
  </div>
  <div class="stat-card admin-card" id="stat-card-rev" style="display:none;">
    <div class="stat-icon" style="background:linear-gradient(135deg,#059669,#34d399);color:white;"><i class="fas fa-coins"></i></div>
    <div><div class="stat-value" id="stat-revenue">—</div><div class="stat-label">Total Revenue (DA)</div></div>
  </div>
  <!-- Doctor Specific -->
  <div class="stat-card" id="stat-card-my-paid" style="display:none;">
    <div class="stat-icon" style="background:linear-gradient(135deg,#3b82f6,#60a5fa);color:white;"><i class="fas fa-wallet"></i></div>
    <div><div class="stat-value" id="stat-my-paid">—</div><div class="stat-label">My Paid (DA)</div></div>
  </div>
  <div class="stat-card" id="stat-card-my-debt" style="display:none;">
    <div class="stat-icon" style="background:linear-gradient(135deg,#e94560,#f43f5e);color:white;"><i class="fas fa-hand-holding-usd"></i></div>
    <div><div class="stat-value" id="stat-my-debt">—</div><div class="stat-label">My Debt (DA)</div></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <!-- Recent Records -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fas fa-file-medical" style="color:#3b82f6;margin-right:8px;"></i>Recent Medical Records</span>
      <a href="/medical-records" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body">
      <table class="data-table" id="recent-records-table">
        <thead>
          <tr><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Date</th></tr>
        </thead>
        <tbody id="recent-records-body">
          <tr><td colspan="4" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Upcoming Appointments -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i class="fas fa-calendar-alt" style="color:#f59e0b;margin-right:8px;"></i>Upcoming Appointments</span>
      <a href="/appointments" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="card-body">
      <table class="data-table">
        <thead>
          <tr><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th></tr>
        </thead>
        <tbody id="upcoming-appt-body">
          <tr><td colspan="4" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- Operations / Revenue per Doctor (Admin only) -->
<div id="operations-section" class="card" style="margin-top:24px;display:none;">
  <div class="card-header">
    <span class="card-title"><i class="fas fa-chart-bar" style="color:#6366f1;margin-right:8px;"></i>Operations & Revenue per Doctor <span style="font-size:12px;font-weight:400;color:#94a3b8;">(Prescription = 39 DA, Appointment = 49 DA)</span></span>
  </div>
  <div class="card-body">
    <div style="display:flex;gap:20px;flex-wrap:wrap;margin-bottom:20px;">
      <div style="background:linear-gradient(135deg,#eef2ff,#e0e7ff);border-radius:14px;padding:20px 28px;flex:1;min-width:180px;">
        <div style="font-size:11px;color:#6366f1;text-transform:uppercase;letter-spacing:0.5px;font-weight:700;">Programme Total</div>
        <div style="font-size:28px;font-weight:800;color:#4338ca;margin-top:4px;" id="ops-total-count">—</div>
        <div style="font-size:12px;color:#6366f1;margin-top:2px;">operations</div>
      </div>
      <div style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);border-radius:14px;padding:20px 28px;flex:1;min-width:180px;">
        <div style="font-size:11px;color:#059669;text-transform:uppercase;letter-spacing:0.5px;font-weight:700;">Total Revenue</div>
        <div style="font-size:28px;font-weight:800;color:#047857;margin-top:4px;" id="ops-total-revenue">—</div>
        <div style="font-size:12px;color:#059669;margin-top:2px;">Algerian Dinar (DA)</div>
      </div>
      <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:14px;padding:20px 28px;flex:1;min-width:180px;">
        <div style="font-size:11px;color:#2563eb;text-transform:uppercase;letter-spacing:0.5px;font-weight:700;">Total Paid</div>
        <div style="font-size:28px;font-weight:800;color:#1d4ed8;margin-top:4px;" id="ops-total-paid">—</div>
        <div style="font-size:12px;color:#2563eb;margin-top:2px;">Algerian Dinar (DA)</div>
      </div>
      <div style="background:linear-gradient(135deg,#fef2f2,#fee2e2);border-radius:14px;padding:20px 28px;flex:1;min-width:180px;">
        <div style="font-size:11px;color:#dc2626;text-transform:uppercase;letter-spacing:0.5px;font-weight:700;">Total Debt</div>
        <div style="font-size:28px;font-weight:800;color:#b91c1c;margin-top:4px;" id="ops-total-debt">—</div>
        <div style="font-size:12px;color:#dc2626;margin-top:2px;">Algerian Dinar (DA)</div>
      </div>
    </div>
    <table class="data-table">
      <thead>
        <tr><th>#</th><th>Doctor</th><th>Specialty</th><th>Ords</th><th>Apps</th><th>Revenue (DA)</th><th>Paid (DA)</th><th>Debt (DA)</th><th>Actions</th></tr>
      </thead>
      <tbody id="ops-doctor-body"></tbody>
    </table>
  </div>
</div>

<div id="doctor-availability-section" class="card" style="margin-top:24px; display:none;">
  <div class="card-header">
    <span class="card-title"><i class="fas fa-calendar-times" style="color:#ef4444;margin-right:8px;"></i>My Unavailability (Off Dates)</span>
    <button class="btn btn-primary btn-sm" onclick="openUnavailabilityModal()"><i class="fas fa-plus"></i> Add Off Date</button>
  </div>
  <div class="card-body">
    <table class="data-table">
      <thead><tr><th>Start Date</th><th>End Date</th><th>Reason</th><th>Actions</th></tr></thead>
      <tbody id="unavailability-body"></tbody>
    </table>
  </div>
</div>

<!-- Modal for Unavailability -->
<div id="unavailability-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:501;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:20px;padding:32px;max-width:400px;width:95%;box-shadow:0 24px 80px rgba(0,0,0,0.3);">
    <h3 style="font-size:18px;font-weight:700;color:#0f3460;margin-bottom:20px;">Add Unavailability</h3>
    <div class="form-group"><label class="form-label">Start Date *</label><input type="date" id="u-start" class="form-control" required></div>
    <div class="form-group"><label class="form-label">End Date</label><input type="date" id="u-end" class="form-control"></div>
    <div class="form-group"><label class="form-label">Reason</label><input type="text" id="u-reason" class="form-control"></div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <button class="btn btn-outline" onclick="closeUnavailabilityModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveUnavailability()"><i class="fas fa-save"></i> Save</button>
    </div>
  </div>
</div>

<!-- Modal for Financial Update -->
<div id="financial-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:501;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:20px;padding:32px;max-width:400px;width:95%;box-shadow:0 24px 80px rgba(0,0,0,0.3);">
    <h3 style="font-size:18px;font-weight:700;color:#0f3460;margin-bottom:20px;">Update Doctor Financials</h3>
    <div style="font-size:13px;color:#64748b;margin-bottom:16px;">Doctor: <strong id="fin-doctor-name"></strong></div>
    <input type="hidden" id="fin-doctor-id">
    <div class="form-group">
      <label class="form-label">Total Amount Paid (DA) *</label>
      <input type="number" id="fin-paid-amount" class="form-control" step="0.01" min="0" required>
      <div style="font-size:11px;color:#94a3b8;margin-top:6px;">This updates the total accumulated amount paid to the doctor. Debt will be automatically recalculated.</div>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;">
      <button class="btn btn-outline" onclick="document.getElementById('financial-modal').style.display='none'">Cancel</button>
      <button class="btn btn-primary" onclick="saveFinancials()"><i class="fas fa-save"></i> Save</button>
    </div>
  </div>
</div>

@push('scripts')
<script>
const token = localStorage.getItem('auth_token') || '';
const headers = {'Content-Type':'application/json','Accept':'application/json','Authorization':'Bearer '+token};

const colors = ['#0f3460','#e94560','#533483','#10b981','#f59e0b'];
function avatar(name, i) {
  return `<div class="avatar" style="background:${colors[i%colors.length]}">${name.charAt(0).toUpperCase()}</div>`;
}

async function loadDashboard() {
  try {
    const r = await fetch('/api/dashboard', {headers});
    const {data} = await r.json();
    document.getElementById('stat-patients').textContent = data.patients;
    document.getElementById('stat-doctors').textContent = data.doctors;
    document.getElementById('stat-records').textContent = data.records_today;
    document.getElementById('stat-appointments').textContent = data.appointments_today;
    document.getElementById('stat-ordonnances').textContent = data.total_ordonnances;

    // Operations + Revenue (admin only)
    const user = JSON.parse(localStorage.getItem('auth_user') || '{}');
    if (user.role === 'admin') {
      // Hide doctor-specific "today" cards to save space if needed, 
      // or just leave them. The user requested specific top cards.
      document.getElementById('card-records').style.display = 'none';
      document.getElementById('card-apps-today').style.display = 'none';
      
      // Show admin cards
      document.getElementById('stat-card-total-apps').style.display = '';
      document.getElementById('stat-card-rev-apps').style.display = '';
      document.getElementById('stat-card-rev-ords').style.display = '';
      document.getElementById('stat-card-rev').style.display = '';

      // Populate values
      document.getElementById('stat-total-apps').textContent = data.total_appointments;
      document.getElementById('stat-rev-apps').textContent = (data.total_appointment_revenue || 0).toLocaleString();
      document.getElementById('stat-rev-ords').textContent = (data.total_ordonnance_revenue || 0).toLocaleString();
      document.getElementById('stat-revenue').textContent = data.total_revenue.toLocaleString();
      document.getElementById('stat-contact-messages').textContent = data.contact_messages_unread;

      document.getElementById('operations-section').style.display = 'block';
      document.getElementById('ops-total-count').textContent = data.total_ordonnances + data.total_appointments;
      document.getElementById('ops-total-revenue').textContent = data.total_revenue.toLocaleString() + ' DA';
      document.getElementById('ops-total-paid').textContent = data.total_paid.toLocaleString() + ' DA';
      document.getElementById('ops-total-debt').textContent = data.total_debt.toLocaleString() + ' DA';

      const opsTb = document.getElementById('ops-doctor-body');
      const doctorOps = data.doctor_operations || [];
      const barColors = ['#6366f1','#e94560','#059669','#f59e0b','#533483','#3b82f6'];
      opsTb.innerHTML = doctorOps.map((d, i) => {
        return `<tr>
          <td style="color:#94a3b8;font-size:12px;">#${d.id}</td>
          <td style="font-weight:600;">${d.name}</td>
          <td><span class="badge badge-purple">${d.specialty}</span></td>
          <td style="font-weight:600;">${d.ordonnances}</td>
          <td style="font-weight:600;">${d.appointments}</td>
          <td style="font-weight:700;color:#047857;">${d.revenue.toLocaleString()}</td>
          <td style="font-weight:600;color:#3b82f6;">${(d.paid || 0).toLocaleString()}</td>
          <td style="font-weight:700;color:${d.debt > 0 ? '#e94560' : '#10b981'};">${d.debt.toLocaleString()}</td>
          <td>
            <button class="btn btn-sm btn-outline" style="color:#0f3460;border-color:#0f3460;" onclick="openFinancialModal(${d.id}, '${d.name.replace(/'/g,"\\'").replace(/"/g,'&quot;')}', ${d.paid || 0})"><i class="fas fa-wallet"></i> Edit Paid</button>
          </td>
        </tr>`;
      }).join('');
    }

    if (user.role === 'doctor' && data.my_stats) {
      document.getElementById('stat-card-my-paid').style.display = '';
      document.getElementById('stat-card-my-debt').style.display = '';
      document.getElementById('stat-my-paid').textContent = data.my_stats.paid.toLocaleString();
      document.getElementById('stat-my-debt').textContent = data.my_stats.debt.toLocaleString();
    }

    // Recent records
    const rb = document.getElementById('recent-records-body');
    rb.innerHTML = data.recent_records.length ? data.recent_records.map((r,i) => `
      <tr>
        <td style="display:flex;align-items:center;gap:10px;">${avatar(r.patient.name,i)}<span>${r.patient.name}</span></td>
        <td>${r.doctor.user.name}</td>
        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.diagnosis}</td>
        <td><span class="badge badge-info">${r.visit_date}</span></td>
      </tr>`).join('') :
      '<tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;">No records yet</td></tr>';

    // Upcoming appointments
    const ab = document.getElementById('upcoming-appt-body');
    ab.innerHTML = data.upcoming_appointments.length ? data.upcoming_appointments.map((a,i) => `
      <tr>
        <td style="display:flex;align-items:center;gap:10px;">${avatar(a.patient.name,i)}<span>${a.patient.name}</span></td>
        <td>${a.doctor.user.name}</td>
        <td>${new Date(a.scheduled_at).toLocaleString()}</td>
        <td><span class="badge badge-warning">${a.status}</span></td>
      </tr>`).join('') :
      '<tr><td colspan="4" style="text-align:center;padding:20px;color:#94a3b8;">No upcoming appointments</td></tr>';

    // Show availability section if doctor
    if (user.role === 'doctor') {
      document.getElementById('doctor-availability-section').style.display = 'block';
      loadUnavailabilities();
    }

  } catch(e) {
    console.error('Dashboard load error:', e);
  }
}

async function loadUnavailabilities() {
  const r = await fetch('/api/doctor-unavailabilities', {headers});
  const {data} = await r.json();
  const tb = document.getElementById('unavailability-body');
  tb.innerHTML = data.map(u => `
    <tr>
      <td>${u.start_date}</td>
      <td>${u.end_date || '—'}</td>
      <td>${u.reason || '—'}</td>
      <td><button class="btn btn-sm" style="color:#ef4444;background:none;border:none;" onclick="deleteUnavailability(${u.id})"><i class="fas fa-trash"></i></button></td>
    </tr>`).join('');
}

function openUnavailabilityModal() { document.getElementById('unavailability-modal').style.display = 'flex'; }
function closeUnavailabilityModal() { document.getElementById('unavailability-modal').style.display = 'none'; }

async function saveUnavailability() {
  const start = document.getElementById('u-start').value;
  const end   = document.getElementById('u-end').value;
  const reason = document.getElementById('u-reason').value;
  if (!start) return;

  const r = await fetch('/api/doctor-unavailabilities', {
    method: 'POST',
    headers,
    body: JSON.stringify({start_date: start, end_date: end, reason})
  });
  if (r.ok) { closeUnavailabilityModal(); loadUnavailabilities(); }
}

async function deleteUnavailability(id) {
  if (!confirm('Delete this entry?')) return;
  await fetch(`/api/doctor-unavailabilities/${id}`, {method: 'DELETE', headers});
  loadUnavailabilities();
}

function openFinancialModal(id, name, currentPaid) {
  document.getElementById('fin-doctor-id').value = id;
  document.getElementById('fin-doctor-name').textContent = name;
  document.getElementById('fin-paid-amount').value = currentPaid;
  document.getElementById('financial-modal').style.display = 'flex';
}

async function saveFinancials() {
  const id = document.getElementById('fin-doctor-id').value;
  const paid = document.getElementById('fin-paid-amount').value;
  if (paid === '') return;

  try {
    const r = await fetch(`/api/doctors/${id}`, {
      method: 'PATCH',
      headers,
      body: JSON.stringify({ paid_amount: parseFloat(paid) })
    });
    if (r.ok) {
      document.getElementById('financial-modal').style.display = 'none';
      loadDashboard();
    } else {
      alert('Failed to update financials.');
    }
  } catch(e) {
    console.error(e);
  }
}


// Update loadDashboard to show admin-only elements
const originalLoadDashboard = loadDashboard;
loadDashboard = async function() {
  await originalLoadDashboard();
  
  // Show admin-only elements for admin users
  const userJson = localStorage.getItem('auth_user');
  let user = null;
  if (userJson) {
    try { user = JSON.parse(userJson); } catch(e) {}
  }
  if (!user && window.serverUser) user = window.serverUser;

  if (user) {
    try {
      if (user.role === 'admin') {
        // Show admin-only elements
        document.querySelectorAll('.admin-card, .admin-only').forEach(el => {
          el.style.display = el.tagName === 'DIV' ? (el.classList.contains('admin-card') ? 'block' : 'flex') : 'block';
        });
      }
    } catch (e) {
      console.error('Error checking user role:', e);
    }
  }
};

loadDashboard();
</script>
@endpush
@endsection
