@extends('layouts.app')
@section('page-title', 'Appointments')
@section('content')
<div class="card">
  <div class="card-header">
    <span class="card-title"><i class="fas fa-calendar-check" style="color:#0f3460;margin-right:8px;"></i>Appointments</span>
    <button class="btn btn-primary btn-sm" onclick="openModal()"><i class="fas fa-plus"></i> New Appointment</button>
  </div>
  <div class="card-body">
    <table class="data-table">
      <thead><tr><th>#</th><th>Patient</th><th>Doctor</th><th>Date & Time</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead>
      <tbody id="app-body">
        <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<div id="app-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:500;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:20px;padding:32px;max-width:520px;width:95%;box-shadow:0 24px 80px rgba(0,0,0,0.3);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
      <h3 style="font-size:18px;font-weight:700;color:#0f3460;">New Appointment</h3>
      <button onclick="closeModal()" style="border:none;background:none;font-size:20px;cursor:pointer;color:#94a3b8;">&times;</button>
    </div>
    <form id="app-form">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Patient *</label>
          <select id="a-patient" class="form-control" required><option value="">Select patient...</option></select>
        </div>
        <div class="form-group" id="doctor-group">
          <label class="form-label">Doctor *</label>
          <select id="a-doctor" class="form-control" required><option value="">Select doctor...</option></select>
        </div>
        <div class="form-group">
          <label class="form-label">Date and Time *</label>
          <input type="datetime-local" id="a-date" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select id="a-status" class="form-control">
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea id="a-notes" class="form-control" rows="2"></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
const token = localStorage.getItem('auth_token') || '';
const h = {'Content-Type':'application/json','Accept':'application/json','Authorization':'Bearer '+token};
let userRole = '';
try { userRole = JSON.parse(localStorage.getItem('auth_user')).role; } catch(e){ console.error('Invalid cached auth_user:', e); }

async function loadAppointments() {
  const tb = document.getElementById('app-body');
  let data;
  try {
    const r = await fetch('/api/appointments', {headers: h});
    if (!r.ok) throw new Error(`Request failed with status ${r.status}`);
    ({data} = await r.json());
  } catch(e) {
    console.error('Appointments load error:', e);
    tb.innerHTML='<tr><td colspan="7" style="text-align:center;padding:24px;color:#ef4444;">Error loading appointments</td></tr>';
    return;
  }
  if (!data || !data.length) { tb.innerHTML='<tr><td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;">No appointments found</td></tr>'; return; }
  
  tb.innerHTML = data.map(a => {
    let statColor = 'badge-info';
    if(a.status==='confirmed') statColor='badge-success';
    if(a.status==='cancelled') statColor='badge-danger';
    if(a.status==='completed') statColor='badge-purple';
    return `<tr>
      <td style="color:#94a3b8;font-size:12px;">#${a.id}</td>
      <td style="font-weight:600;">${a.patient.name}</td>
      <td>${a.doctor.user.name} <span style="font-size:11px;color:#94a3b8;display:block">${a.doctor.specialty}</span></td>
      <td>${new Date(a.scheduled_at).toLocaleString()}</td>
      <td><span class="badge ${statColor}">${a.status.charAt(0).toUpperCase() + a.status.slice(1)}</span></td>
      <td style="max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${a.notes||''}">${a.notes||'—'}</td>
      <td>
        <button class="btn btn-sm" style="background:#fff1f2;color:#e94560;border:none;" onclick="deleteApp(${a.id})"><i class="fas fa-trash"></i></button>
      </td>
    </tr>`;
  }).join('');
}

async function loadSelects() {
  const rp = await fetch('/api/patients', {headers: h});
  if (!rp.ok) throw new Error(`Patients request failed with status ${rp.status}`);
  const dp = await rp.json();
  const selP = document.getElementById('a-patient');
  (dp.data?.data || dp.data || []).forEach(p => selP.innerHTML += `<option value="${p.id}">${p.name}</option>`);

  if (userRole === 'admin') {
    const rd = await fetch('/api/doctors', {headers: h});
    if (!rd.ok) throw new Error(`Doctors request failed with status ${rd.status}`);
    const dd = await rd.json();
    const selD = document.getElementById('a-doctor');
    (dd.data?.data || dd.data || []).forEach(d => selD.innerHTML += `<option value="${d.id}">${d.name}</option>`);
  } else {
    document.getElementById('doctor-group').style.display = 'none';
    document.getElementById('a-doctor').removeAttribute('required');
  }
}

function openModal() {
  document.getElementById('app-modal').style.display = 'flex';
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  document.getElementById('a-date').value = now.toISOString().slice(0,16);
}
function closeModal() { document.getElementById('app-modal').style.display = 'none'; }

document.getElementById('app-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const body = {
    patient_id: document.getElementById('a-patient').value,
    scheduled_at: document.getElementById('a-date').value,
    status: document.getElementById('a-status').value,
    notes: document.getElementById('a-notes').value,
  };
  if (userRole === 'admin') {
    body.doctor_id = document.getElementById('a-doctor').value;
  }
  try {
    const r = await fetch('/api/appointments', {method:'POST', headers: h, body: JSON.stringify(body)});
    const data = await r.json();
    if (!r.ok || !data.success) {
      alert(JSON.stringify(data.errors || data.message || `Request failed with status ${r.status}`));
      return;
    }
    closeModal();
    loadAppointments();
  } catch(e) {
    console.error('Create appointment error:', e);
    alert('Could not save the appointment. Please try again.');
  }
});

async function deleteApp(id) {
  if (!confirm('Cancel/Delete this appointment?')) return;
  try {
    const r = await fetch(`/api/appointments/${id}`, {method:'DELETE',headers:h});
    if (!r.ok) throw new Error(`Request failed with status ${r.status}`);
    loadAppointments();
  } catch(e) {
    console.error('Delete appointment error:', e);
    alert('Could not delete the appointment. Please try again.');
  }
}

loadAppointments();
loadSelects().catch(e => {
  console.error('Appointment form options load error:', e);
  alert('Could not load patients or doctors for the appointment form.');
});
</script>
@endpush
@endsection
