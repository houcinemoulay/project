<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SHIFA — Laboratory Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f3460 0%,#16213e 55%,#f59e0b 100%);min-height:100vh}
.portal-header{background:rgba(255,255,255,0.08);backdrop-filter:blur(10px);padding:16px 32px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.1)}
.brand{display:flex;align-items:center;gap:12px;color:white}
.brand-icon{width:40px;height:40px;background:#f59e0b;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#1e293b}
.btn-logout{background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);padding:8px 16px;border-radius:10px;cursor:pointer;font-size:13px}
.btn-logout:hover{background:rgba(255,255,255,0.2)}
.portal-content{max-width:1100px;margin:0 auto;padding:40px 24px}
.tabs{display:flex;gap:12px;margin-bottom:28px;flex-wrap:wrap}
.tab{padding:10px 20px;border-radius:12px;font-size:13.5px;font-weight:600;cursor:pointer;background:rgba(255,255,255,0.1);color:white;border:none;transition:all 0.2s;display:flex;align-items:center;gap:8px}
.tab.active{background:#f59e0b;color:#1e293b}
.panel{display:none}
.panel.active{display:block}
.glass-card{background:rgba(255,255,255,0.97);border-radius:24px;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,0.3);margin-bottom:24px}
.nfc-scanner{background:rgba(255,255,255,0.08);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.15);border-radius:24px;padding:40px;text-align:center;margin-bottom:32px}
.nfc-icon{width:80px;height:80px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:22px;display:flex;align-items:center;justify-content:center;font-size:32px;color:white;margin:0 auto 20px;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(245,158,11,0.4)}50%{box-shadow:0 0 0 14px rgba(245,158,11,0)}}
.nfc-title{color:white;font-size:20px;font-weight:700;margin-bottom:8px}
.nfc-sub{color:rgba(255,255,255,0.6);font-size:13.5px;margin-bottom:24px}
.nfc-row{display:flex;gap:12px;max-width:480px;margin:0 auto}
.nfc-input{flex:1;background:rgba(255,255,255,0.12);border:1.5px solid rgba(255,255,255,0.2);border-radius:12px;padding:13px 18px;font-size:14px;color:white;font-family:inherit}
.nfc-input::placeholder{color:rgba(255,255,255,0.4)}
.nfc-input:focus{outline:none;border-color:#f59e0b}
.btn-scan{background:#f59e0b;color:#1e293b;border:none;border-radius:12px;padding:13px 22px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;transition:all 0.2s}
.btn-scan:hover{background:#d97706}
.patient-bar{display:flex;align-items:center;gap:20px;background:#f8fafc;border-radius:16px;padding:20px;margin-bottom:24px}
.p-avatar{width:64px;height:64px;background:linear-gradient(135deg,#0f3460,#533483);border-radius:18px;display:flex;align-items:center;justify-content:center;color:white;font-size:26px;font-weight:700;overflow:hidden;flex-shrink:0}
.p-avatar img{width:100%;height:100%;object-fit:cover}
.upload-area{border:2px dashed #e2e8f0;border-radius:16px;padding:28px;text-align:center;cursor:pointer;transition:all 0.2s;background:#fafafa}
.upload-area:hover{border-color:#f59e0b;background:#fffbeb}
.upload-icon{font-size:32px;color:#f59e0b;margin-bottom:12px}
.form-label{display:block;font-size:12.5px;font-weight:600;color:#334155;margin-bottom:6px}
.form-control{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-family:inherit;background:white}
.form-control:focus{outline:none;border-color:#f59e0b}
.form-group{margin-bottom:16px}
.btn-upload{background:#f59e0b;color:#1e293b;border:none;padding:11px 22px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px}
.btn-upload:hover{background:#d97706}
.result-thumb{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-top:16px}
.result-item{border:1.5px solid #e2e8f0;border-radius:14px;overflow:hidden;position:relative;background:#f8fafc}
.result-item img{width:100%;height:140px;object-fit:cover;display:block}
.result-item-info{padding:10px 12px}
.result-item-title{font-size:13px;font-weight:600;color:#0f3460;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.result-item-meta{font-size:11px;color:#94a3b8;margin-top:3px}
.btn-del{position:absolute;top:8px;right:8px;background:rgba(233,69,96,0.9);color:white;border:none;border-radius:8px;width:28px;height:28px;cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center}
.patients-table{width:100%;border-collapse:collapse}
.patients-table th{background:#f8fafc;padding:11px 14px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#94a3b8;font-weight:600;border-bottom:1px solid #e2e8f0}
.patients-table td{padding:13px 14px;border-bottom:1px solid #f1f5f9;font-size:13.5px}
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600}
.badge-success{background:#f0fdf4;color:#166534}
.badge-nfc{background:linear-gradient(135deg,#0f3460,#533483);color:white}
/* Profile */
.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
.info-box{background:#f8fafc;border-radius:16px;padding:20px}
.info-box label{font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px}
.info-box div{font-size:14px;font-weight:600;color:#1e293b}
</style>
</head>
<body>
<header class="portal-header">
  <div class="brand">
    <div class="brand-icon"><i class="fas fa-flask"></i></div>
    <div>
      <div style="color:white;font-size:18px;font-weight:800;">SHIFA</div>
      <div style="color:rgba(255,255,255,0.5);font-size:11px;">Laboratory Portal</div>
    </div>
  </div>
  <div style="display:flex;align-items:center;gap:16px;">
    <div style="color:white;font-size:13px;opacity:0.8;" id="lab-name-hdr"></div>
    <button class="btn-logout" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</button>
  </div>
</header>

<div class="portal-content">
  <div class="tabs">
    <button class="tab active" onclick="showTab('nfc',this)"><i class="fas fa-wifi"></i> NFC Scan & Upload</button>
    <button class="tab" onclick="showTab('patients',this)"><i class="fas fa-users"></i> All Patients</button>
    <button class="tab" onclick="showTab('history',this)"><i class="fas fa-history"></i> Upload History</button>
    <button class="tab" onclick="showTab('profile',this)"><i class="fas fa-flask"></i> Lab Profile</button>
  </div>

  <!-- NFC Scan & Upload Tab -->
  <div id="tab-nfc" class="panel active">
    <div class="nfc-scanner">
      <div class="nfc-icon"><i class="fas fa-wifi"></i></div>
      <div class="nfc-title">Scan Patient NFC Card</div>
      <div class="nfc-sub">Tap or enter the patient NFC UID to load their record and add lab results</div>
      <div class="nfc-row">
        <input type="text" id="lab-nfc-uid" class="nfc-input" placeholder="NFC UID (e.g. NFC001ABC)...">
        <button class="btn-scan" onclick="scanNfc()"><i class="fas fa-search"></i> Scan</button>
      </div>
      <div id="lab-err" style="color:#fca5a5;margin-top:14px;font-size:13px;"></div>
    </div>

    <div id="lab-result-panel" style="display:none;">
      <div class="glass-card">
        <div class="patient-bar">
          <div class="p-avatar" id="lab-pat-av">?</div>
          <div>
            <div style="font-size:18px;font-weight:700;color:#0f3460;" id="lab-pat-nm">—</div>
            <div style="color:#64748b;font-size:13px;" id="lab-pat-mt">—</div>
            <span class="badge badge-nfc" style="margin-top:6px;"><i class="fas fa-wifi"></i> NFC Verified</span>
          </div>
        </div>

        <!-- Laboratory Prescriptions -->
        <div style="margin-top:20px;margin-bottom:28px;">
          <h4 style="font-size:15px;font-weight:700;color:#0f3460;margin-bottom:14px;"><i class="fas fa-file-medical" style="color:#533483;margin-right:8px;"></i>Laboratory Prescriptions (Analyses)</h4>
          <div id="lab-prescriptions-container">
            <div style="text-align:center;color:#94a3b8;padding:20px;">Loading prescriptions...</div>
          </div>
        </div>

        <!-- Upload Form -->
        <h4 style="font-size:15px;font-weight:700;color:#0f3460;margin-bottom:14px;"><i class="fas fa-upload" style="color:#f59e0b;margin-right:8px;"></i>Add Lab Result</h4>
        <div class="upload-area" id="drop-zone" onclick="document.getElementById('result-file').click()" ondragover="event.preventDefault()" ondrop="handleDrop(event)">
          <input type="file" id="result-file" accept="image/*,.pdf" style="display:none" onchange="handleFile(this.files[0])">
          <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
          <div style="font-size:15px;font-weight:600;color:#0f3460;margin-bottom:4px;">Drop file here or click to browse</div>
          <div style="font-size:12.5px;color:#94a3b8;">Supports: JPEG, PNG, PDF (max 10 MB)</div>
          <div id="file-preview" style="margin-top:12px;"></div>
        </div>
        <div class="form-group" style="margin-top:16px;">
          <label class="form-label">Result Title</label>
          <input type="text" id="result-title" class="form-control" placeholder="e.g. Blood Count Analysis">
        </div>
        <div class="form-group">
          <label class="form-label">Notes / Description</label>
          <textarea id="result-note" class="form-control" rows="2" placeholder="Any important observations..."></textarea>
        </div>
        <button class="btn-upload" onclick="uploadResult()"><i class="fas fa-upload"></i> Upload Result</button>
        <div id="upload-status" style="margin-top:12px;font-size:13px;"></div>

        <!-- Previous Results -->
        <div style="margin-top:28px;border-top:1px solid #e2e8f0;padding-top:20px;">
          <h4 style="font-size:15px;font-weight:700;color:#0f3460;margin-bottom:16px;"><i class="fas fa-history" style="color:#3b82f6;margin-right:8px;"></i>Uploaded Results for This Patient</h4>
          <div class="result-thumb" id="prev-results">
            <div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:20px;">No results yet</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- All Patients Tab -->
  <div id="tab-patients" class="panel">
    <div class="glass-card" style="padding:0;overflow:hidden;">
      <div style="padding:18px 22px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:15px;font-weight:700;"><i class="fas fa-users" style="color:#f59e0b;margin-right:8px;"></i>Patients</span>
        <input type="text" id="pt-search" placeholder="Search..." style="padding:8px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;" oninput="loadPatients(this.value)">
      </div>
      <table class="patients-table">
        <thead><tr><th>Patient</th><th>Age</th><th>Blood</th><th>Phone</th><th>NFC</th><th>Actions</th></tr></thead>
        <tbody id="pt-body"><tr><td colspan="6" style="text-align:center;padding:28px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
      </table>
    </div>
  </div>

  <!-- History Tab -->
  <div id="tab-history" class="panel">
    <div class="glass-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h3 style="font-size:18px;font-weight:700;color:#0f3460;"><i class="fas fa-history" style="color:#3b82f6;margin-right:8px;"></i>All Uploaded Lab Results</h3>
        <span id="hist-count" style="background:#eff6ff;color:#1e40af;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;"></span>
      </div>
      <div style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;">
        <input type="text" id="hist-search" placeholder="Search by patient name or title..." style="flex:1;min-width:200px;padding:9px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;" oninput="filterHistory(this.value)">
        <select id="hist-type" style="padding:9px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;" onchange="filterHistory()">
          <option value="">All types</option>
          <option value="image">Images</option>
          <option value="pdf">PDFs</option>
        </select>
      </div>
      <div id="hist-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;">
        <div style="grid-column:1/-1;text-align:center;padding:32px;color:#94a3b8;"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>
      </div>
    </div>
  </div>

  <!-- Lab Profile Tab -->
  <div id="tab-profile" class="panel">
    <div class="glass-card">
      <h3 style="font-size:18px;font-weight:700;color:#0f3460;margin-bottom:24px;"><i class="fas fa-flask" style="color:#f59e0b;margin-right:8px;"></i>Laboratory Profile</h3>
      <div style="background:#eff6ff;border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
        <i class="fas fa-envelope" style="color:#3b82f6;font-size:18px;"></i>
        <div>
          <div style="font-size:12px;color:#64748b;">Login Email</div>
          <div style="font-weight:700;color:#0f3460;" id="lb-login-email">—</div>
        </div>
      </div>
      <div class="info-grid">
        <div class="info-box"><label>Lab Name</label><div id="lb-name">—</div></div>
        <div class="info-box"><label>Contact Email</label><div id="lb-email">—</div></div>
        <div class="info-box"><label>Username</label><div id="lb-username">—</div></div>
        <div class="info-box"><label>Specialization</label><div id="lb-spec">—</div></div>
        <div class="info-box"><label>Phone</label><div id="lb-phone">—</div></div>
        <div class="info-box"><label>Address</label><div id="lb-addr">—</div></div>
        <div class="info-box"><label>License No.</label><div id="lb-lic">—</div></div>
        <div class="info-box"><label>Status</label><div id="lb-status">—</div></div>
      </div>
    </div>
  </div>
</div>

<script>
const token = localStorage.getItem('auth_token') || '';
const user  = JSON.parse(localStorage.getItem('auth_user') || '{}');
const h     = {'Accept':'application/json','Authorization':'Bearer '+token};
const hj    = {'Content-Type':'application/json','Accept':'application/json','Authorization':'Bearer '+token};

if (!token || user.role !== 'lab') { window.location.href = '/login'; }
document.getElementById('lab-name-hdr').textContent = user.name || '';

let currentPatientId = null;
let selectedFile = null;

document.getElementById('lab-nfc-uid').addEventListener('keydown', e => { if(e.key==='Enter') scanNfc(); });

function showTab(name, btn) {
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tab-'+name).classList.add('active');
  if (name === 'patients') loadPatients();
  if (name === 'profile')  loadProfile();
  if (name === 'history')  loadHistory();
}

let allHistory = [];

async function loadHistory() {
  const grid = document.getElementById('hist-grid');
  grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:32px;color:#94a3b8;"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>';
  try {
    const r = await fetch('/api/lab-results/history', {headers: h});
    const {data} = await r.json();
    allHistory = data || [];
    document.getElementById('hist-count').textContent = allHistory.length + ' result(s)';
    renderHistory(allHistory);
  } catch(e) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:24px;color:#e94560;">Error loading history.</div>';
  }
}

function filterHistory(q) {
  const search = (document.getElementById('hist-search').value || '').toLowerCase();
  const type   = document.getElementById('hist-type').value;
  const filtered = allHistory.filter(r =>
    (!search || (r.patient?.name||'').toLowerCase().includes(search) || (r.title||'').toLowerCase().includes(search)) &&
    (!type   || r.file_type === type)
  );
  renderHistory(filtered);
}

function renderHistory(items) {
  const grid = document.getElementById('hist-grid');
  if (!items.length) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:32px;color:#94a3b8;"><i class="fas fa-folder-open" style="font-size:32px;margin-bottom:12px;display:block;"></i>No results found</div>';
    return;
  }
  grid.innerHTML = items.map(res => `
    <div style="border:1.5px solid #e2e8f0;border-radius:16px;overflow:hidden;background:#fff;box-shadow:0 2px 12px rgba(15,52,96,0.06);transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 28px rgba(15,52,96,0.13)'" onmouseout="this.style.boxShadow='0 2px 12px rgba(15,52,96,0.06)'">
      ${res.file_type === 'image'
        ? `<img src="/storage/${res.file_path}" style="width:100%;height:150px;object-fit:cover;display:block;">`
        : `<div style="height:150px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,#fef3c7,#fffbeb);"><i class="fas fa-file-pdf" style="font-size:48px;color:#d97706;margin-bottom:8px;"></i><span style="font-size:12px;color:#92400e;font-weight:600;">PDF Document</span></div>`}
      <div style="padding:14px;">
        <div style="font-weight:700;font-size:13.5px;color:#0f3460;margin-bottom:4px;">${res.title || 'Lab Result'}</div>
        <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
          <div style="width:28px;height:28px;background:linear-gradient(135deg,#0f3460,#533483);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">${(res.patient?.name||'?').charAt(0)}</div>
          <span style="font-size:12.5px;font-weight:600;color:#334155;">${res.patient?.name || '—'}</span>
        </div>
        ${res.note ? `<div style="font-size:11.5px;color:#64748b;margin-bottom:8px;border-left:3px solid #f59e0b;padding-left:8px;">${res.note}</div>` : ''}
        <div style="font-size:11px;color:#94a3b8;margin-bottom:10px;"><i class="fas fa-clock"></i> ${new Date(res.created_at).toLocaleString()}</div>
        <div style="display:flex;gap:8px;">
          <a href="/storage/${res.file_path}" target="_blank" style="flex:1;text-align:center;background:#f59e0b;color:#1e293b;border:none;border-radius:8px;padding:7px;font-size:12px;font-weight:700;text-decoration:none;"><i class="fas fa-external-link-alt"></i> Open</a>
          <button onclick="delHistResult(${res.id})" style="background:#fff1f2;color:#e94560;border:1px solid #fecdd3;border-radius:8px;padding:7px 12px;font-size:12px;font-weight:700;cursor:pointer;"><i class="fas fa-trash"></i></button>
        </div>
      </div>
    </div>`).join('');
}

async function delHistResult(id) {
  if (!confirm('Delete this lab result permanently?')) return;
  await fetch(`/api/lab-results/${id}`, {method:'DELETE', headers: h});
  allHistory = allHistory.filter(r => r.id !== id);
  document.getElementById('hist-count').textContent = allHistory.length + ' result(s)';
  renderHistory(allHistory);
}

async function scanNfc() {
  const uid = document.getElementById('lab-nfc-uid').value.trim();
  if (!uid) return;
  document.getElementById('lab-err').textContent = '';
  document.getElementById('lab-result-panel').style.display = 'none';
  currentPatientId = null;

  try {
    const r = await fetch('/api/nfc-lookup', {
      method:'POST', headers: hj, body: JSON.stringify({nfc_uid: uid})
    });
    const data = await r.json();
    if (!data.success) { document.getElementById('lab-err').textContent = data.message; return; }

    const p = data.patient;
    currentPatientId = p.id;
    const av = document.getElementById('lab-pat-av');
    av.innerHTML = p.photo ? `<img src="/storage/${p.photo}" alt="${p.name}">` : p.name.charAt(0).toUpperCase();
    document.getElementById('lab-pat-nm').textContent = p.name;
    document.getElementById('lab-pat-mt').textContent = `${p.gender} · Age: ${p.age} · Blood: ${p.blood_type || 'N/A'}`;
    document.getElementById('lab-result-panel').style.display = 'block';
    loadPrevResults(p.id);
    loadPatientPrescriptions(p.id);
  } catch(e) {
    document.getElementById('lab-err').textContent = 'Connection error.';
  }
}

async function loadPrevResults(patientId) {
  const r = await fetch(`/api/lab-results?patient_id=${patientId}`, {headers: h});
  const {data} = await r.json();
  const container = document.getElementById('prev-results');
  if (!data || !data.length) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#94a3b8;padding:20px;">No results uploaded yet</div>';
    return;
  }
  container.innerHTML = data.map(res => `
    <div class="result-item">
      ${res.file_type === 'image'
        ? `<img src="/storage/${res.file_path}" alt="${res.title||'Result'}">`
        : `<div style="height:140px;display:flex;align-items:center;justify-content:center;background:#fef3c7;"><i class="fas fa-file-pdf" style="font-size:48px;color:#d97706;"></i></div>`}
      <button class="btn-del" onclick="delResult(${res.id})" title="Delete"><i class="fas fa-times"></i></button>
      <div class="result-item-info">
        <div class="result-item-title">${res.title || 'Lab Result'}</div>
        <div class="result-item-meta">${new Date(res.created_at).toLocaleDateString()}</div>
        ${res.note ? `<div style="font-size:11px;color:#64748b;margin-top:4px;">${res.note}</div>` : ''}
        <a href="/storage/${res.file_path}" target="_blank" style="font-size:12px;color:#f59e0b;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px;margin-top:6px;"><i class="fas fa-external-link-alt"></i> Open</a>
      </div>
    </div>`).join('');
}

async function loadPatientPrescriptions(patientId) {
  const container = document.getElementById('lab-prescriptions-container');
  try {
    const r = await fetch(`/api/ordonnances?patient_id=${patientId}`, {headers: h});
    const {data} = await r.json();
    if (!data || !data.length) {
      container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:20px;background:#f8fafc;border-radius:12px;border:1px dashed #e2e8f0;">No pending analyses (prescriptions) for this patient.</div>';
      return;
    }
    container.innerHTML = data.map(o => `
      <div style="border:1.5px solid #e2e8f0;border-left:4px solid #533483;border-radius:12px;padding:16px;margin-bottom:12px;background:#f8fafc;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
          <div>
            <div style="font-weight:700;color:#0f3460;font-size:14px;">Prescription #${o.id}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;"><i class="fas fa-user-md"></i> Dr. ${o.doctor?.user?.name || '—'} · <i class="fas fa-calendar-alt"></i> ${o.issued_date}</div>
          </div>
          <div style="display:flex;gap:6px;">
            <button onclick="togglePrescriptionTaken(${o.id})" class="btn btn-sm" style="background:${o.is_taken?'#10b981':'#f59e0b'};color:white;border:none;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;cursor:pointer;">
              <i class="fas ${o.is_taken?'fa-check-circle':'fa-circle'}"></i> ${o.is_taken ? 'Taken' : 'Mark Taken'}
            </button>
            <a href="/api/ordonnances/${o.id}/pdf?token=${token}" target="_blank" style="background:#fff1f2;color:#e94560;border:1px solid #fecdd3;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;text-decoration:none;"><i class="fas fa-print"></i> Print</a>
          </div>
        </div>
        ${o.instructions ? `<div style="background:#fffbeb;color:#92400e;padding:8px 12px;border-radius:8px;font-size:12px;margin-bottom:10px;"><i class="fas fa-info-circle"></i> <strong>Notice:</strong> ${o.instructions}</div>` : ''}
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
          ${(o.medications||[]).map(m => `
            <div style="background:white;border:1px solid #e2e8f0;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;color:#334155;">
              <i class="fas fa-flask" style="color:#3b82f6;margin-right:4px;"></i> ${m.name}
            </div>
          `).join('')}
        </div>
      </div>
    `).join('');
  } catch (e) {
    console.error('Prescriptions load error:', e);
    container.innerHTML = '<div style="text-align:center;color:#ef4444;padding:20px;">Error loading prescriptions.</div>';
  }
}

async function togglePrescriptionTaken(id) {
  try {
    const r = await fetch(`/api/ordonnances/${id}/toggle-taken`, {method:'PATCH', headers: h});
    if (!r.ok) throw new Error(`Request failed with status ${r.status}`);
    loadPatientPrescriptions(currentPatientId);
  } catch (e) {
    console.error('Toggle prescription error:', e);
    alert('Could not update the prescription status. Please try again.');
  }
}

async function delResult(id) {
  if (!confirm('Delete this result?')) return;
  await fetch(`/api/lab-results/${id}`, {method:'DELETE', headers: h});
  loadPrevResults(currentPatientId);
}

function handleFile(file) {
  if (!file) return;
  selectedFile = file;
  const preview = document.getElementById('file-preview');
  if (file.type.startsWith('image/')) {
    preview.innerHTML = `<img src="${URL.createObjectURL(file)}" style="max-height:100px;border-radius:10px;margin-top:8px;">`;
  } else {
    preview.innerHTML = `<div style="background:#fef3c7;padding:10px;border-radius:10px;color:#92400e;font-size:13px;margin-top:8px;"><i class="fas fa-file-pdf"></i> ${file.name}</div>`;
  }
}

function handleDrop(e) {
  e.preventDefault();
  const file = e.dataTransfer.files[0];
  if (file) handleFile(file);
}

async function uploadResult() {
  if (!selectedFile || !currentPatientId) { alert('Please scan a patient and select a file first.'); return; }
  const status = document.getElementById('upload-status');
  status.innerHTML = '<span style="color:#64748b;"><i class="fas fa-spinner fa-spin"></i> Uploading...</span>';

  const fd = new FormData();
  fd.append('file', selectedFile);
  fd.append('patient_id', currentPatientId);
  fd.append('title', document.getElementById('result-title').value);
  fd.append('note', document.getElementById('result-note').value);

  try {
    const r = await fetch('/api/lab-results', {
      method:'POST', headers: {'Accept':'application/json','Authorization':'Bearer '+token}, body: fd
    });
    const data = await r.json();
    if (data.success) {
      status.innerHTML = '<span style="background:#f0fdf4;color:#166534;padding:8px 14px;border-radius:10px;"><i class="fas fa-check-circle"></i> Uploaded!</span>';
      selectedFile = null;
      document.getElementById('result-file').value = '';
      document.getElementById('file-preview').innerHTML = '';
      document.getElementById('result-title').value = '';
      document.getElementById('result-note').value = '';
      loadPrevResults(currentPatientId);
    } else {
      status.innerHTML = `<span style="color:#9f1239;">${JSON.stringify(data.errors || data.message)}</span>`;
    }
  } catch(e) {
    status.innerHTML = '<span style="color:#9f1239;">Connection error.</span>';
  }
}

function selectFromList(id, name, uid) {
  document.getElementById('lab-nfc-uid').value = uid || '';
  currentPatientId = id;
  const av = document.getElementById('lab-pat-av');
  av.textContent = name.charAt(0).toUpperCase();
  document.getElementById('lab-pat-nm').textContent = name;
  document.getElementById('lab-pat-mt').textContent = 'Selected from patient list';
  document.getElementById('lab-result-panel').style.display = 'block';
  loadPrevResults(id);
  showTab('nfc', document.querySelector('.tab'));
}

async function loadPatients(search = '') {
  const url = search ? `/api/patients?search=${encodeURIComponent(search)}` : '/api/patients';
  const r = await fetch(url, {headers: h});
  const {data} = await r.json();
  const rows = data.data || data;
  const tb = document.getElementById('pt-body');
  if (!rows.length) { tb.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:#94a3b8;">No patients</td></tr>'; return; }
  tb.innerHTML = rows.map(p => `<tr>
    <td><div style="display:flex;align-items:center;gap:10px;">
      ${p.photo ? `<img src="/storage/${p.photo}" style="width:36px;height:36px;border-radius:10px;object-fit:cover;">` : `<div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0f3460,#533483);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;">${p.name.charAt(0)}</div>`}
      <span style="font-weight:600;">${p.name}</span>
    </div></td>
    <td>${p.age}</td>
    <td><span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:8px;font-size:12px;">${p.blood_type||'—'}</span></td>
    <td>${p.phone||'—'}</td>
    <td>${p.nfc_uid ? `<span class="badge badge-nfc" style="font-size:10px;"><i class="fas fa-wifi"></i> ${p.nfc_uid}</span>` : '<span style="color:#94a3b8;">—</span>'}</td>
    <td><button onclick="selectFromList(${p.id},'${p.name.replace(/'/g,"\\'")}','${p.nfc_uid||''}')" style="background:#f59e0b;color:#1e293b;border:none;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;"><i class="fas fa-upload"></i> Add Result</button></td>
  </tr>`).join('');
}

async function loadProfile() {
  const r = await fetch('/api/my-profile', {headers: h});
  const {data} = await r.json();
  const lb = data.laboratory || {};
  document.getElementById('lb-login-email').textContent = data.email || '—';
  document.getElementById('lb-name').textContent = lb.name || data.name || '—';
  document.getElementById('lb-email').textContent = lb.email || data.email || '—';
  document.getElementById('lb-username').textContent = data.username || '—';
  document.getElementById('lb-spec').textContent = lb.specialization || '—';
  document.getElementById('lb-phone').textContent = lb.phone || '—';
  document.getElementById('lb-addr').textContent = lb.address || '—';
  document.getElementById('lb-lic').textContent = lb.license_number || '—';
  document.getElementById('lb-status').innerHTML = lb.is_active !== false ? '<span class="badge badge-success">Active</span>' : '<span style="color:#9f1239;">Inactive</span>';
}

function logout() {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('auth_user');
  window.location.href = '/login';
}
</script>
</body>
</html>
