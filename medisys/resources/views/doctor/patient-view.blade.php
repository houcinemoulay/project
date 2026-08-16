@extends('layouts.app')
@section('title', 'Patient Profile')
@section('page-title', 'Patient Profile — Doctor View')

@section('content')
<div id="loading-state" style="text-align:center;padding:60px;color:#94a3b8;">
  <i class="fas fa-spinner fa-spin" style="font-size:32px;margin-bottom:16px;display:block;"></i>
  Loading patient record...
</div>

<div id="profile-content" style="display:none;">

  {{-- Back button --}}
  <div style="margin-bottom:20px;">
    <a href="javascript:history.back()" class="btn btn-outline" style="font-size:13px;">
      <i class="fas fa-arrow-left"></i> Back
    </a>
    <span style="color:#94a3b8;font-size:13px;margin-left:12px;">Viewing as Doctor — your session remains active</span>
  </div>

  {{-- Patient Header Card --}}
  <div class="card" style="margin-bottom:24px;">
    <div style="padding:28px;">
      <div style="display:flex;align-items:flex-start;gap:24px;flex-wrap:wrap;">
        <div id="doc-pat-avatar" style="width:80px;height:80px;background:linear-gradient(135deg,#0f3460,#533483);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:700;color:white;flex-shrink:0;overflow:hidden;"></div>
        <div style="flex:1;">
          <div style="font-size:22px;font-weight:800;color:#0f3460;" id="doc-pat-name">—</div>
          <div style="display:flex;flex-wrap:wrap;gap:16px;color:#64748b;font-size:13.5px;margin-top:6px;">
            <span><i class="fas fa-calendar"></i> Age: <strong id="doc-pat-age">—</strong></span>
            <span><i class="fas fa-venus-mars"></i> <strong id="doc-pat-gender">—</strong></span>
            <span style="color:#e94560;"><i class="fas fa-tint"></i> Blood: <strong id="doc-pat-blood">—</strong></span>
            <span><i class="fas fa-wifi"></i> <strong id="doc-pat-nfc" style="color:#533483;">NFC Patient</strong></span>
          </div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;" id="doc-pat-badges"></div>
        </div>
        <div style="display:flex;gap:10px;">
          <button class="btn btn-primary" onclick="openPrescribeModal()">
            <i class="fas fa-prescription"></i> New Prescription
          </button>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-top:24px;background:#f8fafc;border-radius:14px;padding:20px;">
        <div><div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Phone</div><div style="font-weight:600;margin-top:4px;" id="doc-pat-phone">—</div></div>
        <div><div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Email</div><div style="font-weight:600;margin-top:4px;" id="doc-pat-email">—</div></div>
        <div><div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Address</div><div style="font-weight:600;margin-top:4px;" id="doc-pat-address">—</div></div>
        <div><div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Emergency Contact</div><div style="font-weight:600;margin-top:4px;" id="doc-pat-emergency">—</div></div>
        <div><div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Allergies</div><div style="font-weight:600;color:#e94560;margin-top:4px;" id="doc-pat-allergies">—</div></div>
        <div><div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Date of Birth</div><div style="font-weight:600;margin-top:4px;" id="doc-pat-dob">—</div></div>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px;" id="tab-buttons">
    <button class="btn btn-primary btn-sm active-tab" onclick="switchTab('records', this)"><i class="fas fa-file-medical"></i> Medical Records</button>
    <button class="btn btn-outline btn-sm" onclick="switchTab('prescriptions', this)"><i class="fas fa-prescription"></i> Prescriptions</button>
    <button class="btn btn-outline btn-sm" onclick="switchTab('appointments', this)"><i class="fas fa-calendar-check"></i> Appointments</button>
    <button class="btn btn-outline btn-sm" onclick="switchTab('labresults', this)"><i class="fas fa-flask"></i> Lab Results</button>
  </div>

  {{-- Medical Records Tab --}}
  <div id="tab-records" class="card">
    <div class="card-header">
      <span class="card-title"><i class="fas fa-file-medical" style="color:#3b82f6;margin-right:8px;"></i>Medical Records</span>
    </div>
    <div class="card-body">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Type</th><th>Prescription</th></tr></thead>
        <tbody id="records-body"><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
      </table>
    </div>
  </div>

  {{-- Prescriptions Tab --}}
  <div id="tab-prescriptions" class="card" style="display:none;">
    <div class="card-header">
      <span class="card-title"><i class="fas fa-prescription" style="color:#533483;margin-right:8px;"></i>Prescriptions</span>
    </div>
    <div class="card-body" style="padding:20px;" id="prescriptions-body">
      <div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
  </div>

  {{-- Appointments Tab --}}
  <div id="tab-appointments" class="card" style="display:none;">
    <div class="card-header">
      <span class="card-title"><i class="fas fa-calendar-check" style="color:#10b981;margin-right:8px;"></i>Appointments</span>
    </div>
    <div class="card-body">
      <table class="data-table">
        <thead><tr><th>Date & Time</th><th>Doctor</th><th>Status</th><th>Notes</th></tr></thead>
        <tbody id="appts-body"><tr><td colspan="4" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
      </table>
    </div>
  </div>

  {{-- Lab Results Tab --}}
  <div id="tab-labresults" class="card" style="display:none;">
    <div class="card-header">
      <span class="card-title"><i class="fas fa-flask" style="color:#f59e0b;margin-right:8px;"></i>Lab Results</span>
    </div>
    <div class="card-body" style="padding:20px;" id="labresults-body">
      <div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
  </div>

</div>

{{-- Prescribe Modal --}}
<div id="prescribe-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:500;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:20px;padding:32px;max-width:560px;width:95%;box-shadow:0 24px 80px rgba(0,0,0,0.3);max-height:90vh;overflow-y:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
      <h3 style="font-size:18px;font-weight:700;color:#0f3460;"><i class="fas fa-prescription" style="color:#533483;margin-right:8px;"></i>New Prescription</h3>
      <button onclick="closePrescribeModal()" style="border:none;background:none;font-size:22px;cursor:pointer;color:#94a3b8;">&times;</button>
    </div>

    <div class="form-group">
      <label class="form-label">Patient</label>
      <input type="text" class="form-control" id="presc-patient-display" disabled style="background:#f8fafc;">
    </div>
    <div class="form-group">
      <label class="form-label">Prescription Type *</label>
      <select id="presc-type" class="form-control" onchange="document.getElementById('med-lines').innerHTML=''; addMedLine();">
        <option value="pharmacy" selected><i class="fas fa-pills"></i> 💊 Pharmacy</option>
        <option value="laboratory">🔬 Laboratory</option>
        <option value="nurse">💉 Nurse</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Issued Date *</label>
      <input type="date" id="presc-date" class="form-control">
    </div>
    <div class="form-group">
      <label class="form-label">Valid Until <span style="color:#94a3b8;font-weight:400;">(optional)</span></label>
      <input type="date" id="presc-until" class="form-control">
    </div>

    <div style="margin-bottom:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
        <label class="form-label" style="margin:0;">Medications * <span style="color:#94a3b8;font-size:11px;font-weight:400;">(only name is required; dosage, frequency & duration are optional)</span></label>
        <button type="button" class="btn btn-sm btn-outline" onclick="addMedLine()"><i class="fas fa-plus"></i> Add</button>
      </div>
      <div id="med-lines"></div>
    </div>

    <div class="form-group">
      <label class="form-label">Instructions</label>
      <textarea id="presc-instructions" class="form-control" rows="2" placeholder="Special instructions..."></textarea>
    </div>

    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
      <button class="btn btn-outline" onclick="closePrescribeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="savePrescription()"><i class="fas fa-save"></i> Save Prescription</button>
    </div>
    <div id="presc-status" style="margin-top:12px;font-size:13px;"></div>
  </div>
</div>

@push('scripts')
<script>
const token = localStorage.getItem('auth_token') || '';
const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
const h  = {'Accept':'application/json','Authorization':'Bearer '+token};
const hj = {'Content-Type':'application/json','Accept':'application/json','Authorization':'Bearer '+token};

// Get patient_id from URL
const params = new URLSearchParams(window.location.search);
const patientId = params.get('patient_id');

if (!token || !patientId) { window.location.href = '/dashboard'; }

let patientData = null;
let medicalRecords = [];

async function init() {
  try {
    // Load patient data using doctor's own token
    const [rPat, rRec, rOrd, rApp, rLab] = await Promise.all([
      fetch(`/api/patients/${patientId}`, {headers: h}),
      fetch(`/api/medical-records?patient_id=${patientId}&per_page=100`, {headers: h}),
      fetch(`/api/ordonnances?patient_id=${patientId}`, {headers: h}),
      fetch(`/api/appointments?patient_id=${patientId}`, {headers: h}),
      fetch(`/api/lab-results?patient_id=${patientId}`, {headers: h}),
    ]);

    const pat = await rPat.json();
    const rec = await rRec.json();
    const ord = await rOrd.json();
    const app = await rApp.json();
    const lab = await rLab.json();

    patientData = pat.data;
    medicalRecords = rec.data?.data || rec.data || [];

    renderHeader(patientData);
    renderRecords(medicalRecords, ord.data || []);
    renderPrescriptions(ord.data || []);
    renderAppointments(app.data || []);
    renderLabResults(lab.data || []);

    document.getElementById('loading-state').style.display = 'none';
    document.getElementById('profile-content').style.display = 'block';
  } catch(e) {
    document.getElementById('loading-state').innerHTML = '<div style="color:#e94560;"><i class="fas fa-exclamation-circle"></i> Error loading patient data.</div>';
  }
}

function renderHeader(p) {
  const av = document.getElementById('doc-pat-avatar');
  if (p.photo) {
    av.innerHTML = `<img src="/storage/${p.photo}" style="width:100%;height:100%;object-fit:cover;">`;
  } else {
    av.textContent = p.name.charAt(0).toUpperCase();
  }
  document.getElementById('doc-pat-name').textContent = p.name;
  document.getElementById('doc-pat-age').textContent = p.age;
  document.getElementById('doc-pat-gender').textContent = p.gender;
  document.getElementById('doc-pat-blood').textContent = p.blood_type || '—';
  document.getElementById('doc-pat-phone').textContent = p.phone || '—';
  document.getElementById('doc-pat-email').textContent = p.email || '—';
  document.getElementById('doc-pat-address').textContent = p.address || '—';
  document.getElementById('doc-pat-emergency').textContent = p.emergency_contact || '—';
  document.getElementById('doc-pat-allergies').textContent = p.allergies || 'None';
  document.getElementById('doc-pat-dob').textContent = p.date_of_birth || '—';

  const badges = document.getElementById('doc-pat-badges');
  badges.innerHTML = `
    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Active Patient</span>
    ${p.nfc_uid ? `<span class="nfc-badge"><i class="fas fa-wifi"></i> ${p.nfc_uid}</span>` : ''}
    ${p.allergies ? `<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Allergies noted</span>` : ''}
  `;
}

function renderRecords(records, ords) {
  const tb = document.getElementById('records-body');
  if (!records.length) { tb.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">No medical records</td></tr>'; return; }
  const typeColor = {consultation:'badge-info',follow_up:'badge-purple',emergency:'badge-danger',routine:'badge-success'};
  tb.innerHTML = records.map(r => {
    const ord = ords.find(o => o.medical_record_id === r.id);
    return `<tr>
      <td>${r.visit_date}</td>
      <td style="font-weight:600;">${r.doctor?.user?.name || '—'}<div style="font-size:11px;color:#94a3b8;">${r.doctor?.specialty || ''}</div></td>
      <td>${r.diagnosis}</td>
      <td><span class="badge ${typeColor[r.visit_type]||'badge-info'}">${r.visit_type}</span></td>
      <td>${ord ? `<a href="/api/ordonnances/${ord.id}/pdf?token=${token}" target="_blank" class="btn btn-sm" style="background:#fff1f2;color:#e94560;border:none;"><i class="fas fa-print"></i> Print</a>` : '<span style="color:#94a3b8;">—</span>'}</td>
    </tr>`;
  }).join('');
}

function renderPrescriptions(ords) {
  const container = document.getElementById('prescriptions-body');
  if (!ords.length) { container.innerHTML = '<div style="text-align:center;padding:24px;color:#94a3b8;">No prescriptions</div>'; return; }
  container.innerHTML = ords.map(o => `
    <div style="border:1.5px solid #e2e8f0;border-radius:14px;padding:20px;margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
        <div>
          <div style="font-size:15px;font-weight:700;color:#0f3460;">Prescription #${o.id}</div>
          <div style="font-size:12px;color:#94a3b8;margin-top:4px;">
            <i class="fas fa-user-md"></i> ${o.doctor?.user?.name || '—'} &nbsp;·&nbsp;
            <i class="fas fa-calendar"></i> ${o.issued_date} &nbsp;·&nbsp;
            Valid: ${o.valid_until || 'No limit'}
          </div>
          <div style="margin-top:6px;">
            <span class="badge ${o.type==='laboratory'?'badge-purple':o.type==='nurse'?'badge-info':'badge-primary'}" style="font-size:11px;">
              <i class="fas ${o.type==='laboratory'?'fa-microscope':o.type==='nurse'?'fa-syringe':'fa-pills'}"></i> 
              ${o.type === 'laboratory' ? 'Laboratory' : o.type === 'nurse' ? 'Nurse' : 'Pharmacy'}
            </span>
          </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          ${o.is_taken ? '<span class="badge badge-success" title="Marked as taken by patient"><i class="fas fa-check-circle"></i> Taken</span>' : '<span class="badge badge-outline" style="border:1px solid #94a3b8;color:#94a3b8;"><i class="fas fa-circle"></i> Not Taken</span>'}
          <span class="badge ${o.status==='active'?'badge-success':o.status==='dispensed'?'badge-purple':'badge-warning'}">${o.status==='dispensed'?'Delivered ✓':o.status}</span>
          <a href="/api/ordonnances/${o.id}/pdf?token=${token}" target="_blank" class="btn btn-sm" style="background:#e94560;color:white;border:none;"><i class="fas fa-print"></i> Print</a>
          <button class="btn btn-sm" style="background:#fff1f2;color:#ef4444;border:1px solid #fecdd3;" onclick="deleteOrdonnance(${o.id})"><i class="fas fa-trash"></i></button>
        </div>
      </div>
      ${o.instructions ? `<div style="background:#fffbeb;color:#92400e;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:12px;"><i class="fas fa-info-circle"></i> ${o.instructions}</div>` : ''}
      <div style="display:flex;flex-wrap:wrap;gap:8px;">
        ${(o.medications||[]).map(m => `
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;">
            <div style="font-weight:700;font-size:13px;color:#0f3460;"><i class="fas ${o.type==='laboratory'?'fa-flask':o.type==='nurse'?'fa-procedures':'fa-capsules'}" style="color:#533483;margin-right:4px;"></i>${m.name}</div>
            ${o.type === 'pharmacy' ? `<div style="font-size:12px;color:#64748b;margin-top:2px;">${m.dosage} · ${m.frequency} · ${m.duration}</div>` : ''}
          </div>`).join('')}
      </div>
    </div>`).join('');
}

function renderAppointments(apps) {
  const tb = document.getElementById('appts-body');
  if (!apps.length) { tb.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:24px;color:#94a3b8;">No appointments</td></tr>'; return; }
  const sc = {pending:'badge-info',confirmed:'badge-success',completed:'badge-purple',cancelled:'badge-danger'};
  tb.innerHTML = apps.map(a => `<tr>
    <td style="font-weight:600;">${new Date(a.scheduled_at).toLocaleString()}</td>
    <td>${a.doctor?.user?.name || '—'}<div style="font-size:11px;color:#94a3b8;">${a.doctor?.specialty || ''}</div></td>
    <td><span class="badge ${sc[a.status]||'badge-info'}">${a.status}</span></td>
    <td style="color:#64748b;">${a.notes || '—'}</td>
  </tr>`).join('');
}

function renderLabResults(results) {
  const container = document.getElementById('labresults-body');
  if (!results.length) { container.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8;"><i class="fas fa-flask" style="font-size:32px;display:block;margin-bottom:12px;"></i>No lab results uploaded</div>'; return; }
  container.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">` +
    results.map(res => `
      <div style="border:1.5px solid #e2e8f0;border-radius:14px;overflow:hidden;background:#f8fafc;">
        ${res.file_type === 'image'
          ? `<img src="/storage/${res.file_path}" style="width:100%;height:140px;object-fit:cover;">`
          : `<div style="height:140px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,#fef3c7,#fffbeb);"><i class="fas fa-file-pdf" style="font-size:40px;color:#d97706;"></i><span style="font-size:11px;color:#92400e;margin-top:8px;font-weight:600;">PDF Document</span></div>`}
        <div style="padding:12px;">
          <div style="font-weight:700;font-size:13px;color:#0f3460;">${res.title || 'Lab Result'}</div>
          <div style="font-size:11px;color:#94a3b8;margin-top:3px;">${res.laboratory?.name || 'Laboratory'} · ${new Date(res.created_at).toLocaleDateString()}</div>
          ${res.note ? `<div style="font-size:11.5px;color:#64748b;margin-top:4px;border-left:3px solid #f59e0b;padding-left:8px;">${res.note}</div>` : ''}
          <a href="/storage/${res.file_path}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;color:#f59e0b;font-size:12px;font-weight:700;text-decoration:none;"><i class="fas fa-external-link-alt"></i> Open</a>
        </div>
      </div>`).join('') + `</div>`;
}

// Tab switching
function switchTab(name, btn) {
  ['records','prescriptions','appointments','labresults'].forEach(t => {
    document.getElementById('tab-'+t).style.display = t === name ? 'block' : 'none';
  });
  document.querySelectorAll('#tab-buttons .btn').forEach(b => {
    b.className = 'btn btn-outline btn-sm';
  });
  btn.className = 'btn btn-primary btn-sm';
}

async function deleteOrdonnance(id) {
  if (!confirm('Are you sure you want to delete this prescription?')) return;
  try {
    const r = await fetch('/api/ordonnances/' + id, {method: 'DELETE', headers: h});
    if (!r.ok) throw new Error(`Request failed with status ${r.status}`);
    init(); // refresh
  } catch(e) {
    console.error('Delete prescription error:', e);
    alert('Failed to delete prescription');
  }
}

// Prescribe modal
function openPrescribeModal() {
  document.getElementById('presc-patient-display').value = patientData?.name || '';
  document.getElementById('presc-date').value = new Date().toISOString().split('T')[0];

  // Init one med line
  document.getElementById('med-lines').innerHTML = '';
  addMedLine();

  document.getElementById('prescribe-modal').style.display = 'flex';
  document.getElementById('presc-status').innerHTML = '';
}

function closePrescribeModal() {
  document.getElementById('prescribe-modal').style.display = 'none';
}

function addMedLine() {
  const type = document.getElementById('presc-type').value;
  const div = document.createElement('div');
  
  if (type === 'laboratory' || type === 'nurse') {
    div.style.cssText = 'display:grid;grid-template-columns:1fr auto;gap:8px;margin-bottom:10px;';
    div.innerHTML = `
      <input class="form-control med-name" placeholder="${type==='laboratory'?'Analysis Name *':'Procedure Name *'}" style="font-size:13px;">
      <input type="hidden" class="med-dosage" value="">
      <input type="hidden" class="med-freq" value="">
      <input type="hidden" class="med-dur" value="">
      <button type="button" onclick="this.parentElement.remove()" style="background:#fff1f2;color:#e94560;border:1px solid #fecdd3;border-radius:8px;padding:0 10px;cursor:pointer;font-size:14px;">×</button>
    `;
  } else {
    div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:8px;margin-bottom:10px;';
    div.innerHTML = `
      <input class="form-control med-name" placeholder="Medicine name *" style="font-size:13px;">
      <input class="form-control med-dosage" placeholder="Dosage" style="font-size:13px;">
      <input class="form-control med-freq" placeholder="Frequency" style="font-size:13px;">
      <input class="form-control med-dur" placeholder="Duration" style="font-size:13px;">
      <button type="button" onclick="this.parentElement.remove()" style="background:#fff1f2;color:#e94560;border:1px solid #fecdd3;border-radius:8px;padding:0 10px;cursor:pointer;font-size:14px;">×</button>
    `;
  }
  document.getElementById('med-lines').appendChild(div);
}

async function savePrescription() {
  const medications = Array.from(document.querySelectorAll('#med-lines > div')).map(div => ({
    name:      div.querySelector('.med-name').value.trim(),
    dosage:    div.querySelector('.med-dosage').value.trim(),
    frequency: div.querySelector('.med-freq').value.trim(),
    duration:  div.querySelector('.med-dur').value.trim(),
  })).filter(m => m.name);

  if (!medications.length) { document.getElementById('presc-status').innerHTML = '<span style="color:#e94560;">Add at least one medication.</span>'; return; }

  const recordId = null;
  const payload = {
    patient_id:        patientId,
    medications,
    issued_date:       document.getElementById('presc-date').value,
    valid_until:       document.getElementById('presc-until').value || null,
    instructions:      document.getElementById('presc-instructions').value,
    type:              document.getElementById('presc-type').value,
    medical_record_id: null,
  };

  const status = document.getElementById('presc-status');
  status.innerHTML = '<span style="color:#64748b;"><i class="fas fa-spinner fa-spin"></i> Saving...</span>';

  try {
    const r = await fetch('/api/ordonnances', {method:'POST', headers: hj, body: JSON.stringify(payload)});
    const data = await r.json();
    if (data.success) {
      status.innerHTML = '<span style="background:#f0fdf4;color:#166534;padding:8px 14px;border-radius:10px;"><i class="fas fa-check-circle"></i> Prescription saved!</span>';
      setTimeout(() => {
        closePrescribeModal();
        init(); // Refresh all data
      }, 1500);
    } else {
      status.innerHTML = `<span style="color:#9f1239;">${JSON.stringify(data.errors || data.message)}</span>`;
    }
  } catch(e) {
    status.innerHTML = '<span style="color:#9f1239;">Connection error.</span>';
  }
}

// Keyboard shortcut: Esc closes modal
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePrescribeModal(); });

// Init
init();
</script>
@endpush
@endsection
