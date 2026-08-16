<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SHIFA — Patient Portal</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --primary: #0f3460;
  --accent: #e94560;
  --bg-gradient: linear-gradient(135deg, #0f3460 0%, #16213e 60%, #533483 100%);
  --card-bg: rgba(255,255,255,0.97);
  --text-main: #0f3460;
  --text-muted: #64748b;
  --border: #e2e8f0;
}

body.dark {
  --bg-gradient: linear-gradient(135deg, #020617 0%, #0f172a 100%);
  --card-bg: #1e293b;
  --text-main: #f1f5f9;
  --text-muted: #94a3b8;
  --border: #334155;
}

[dir="rtl"] { font-family: 'Noto Kufi Arabic', 'Inter', sans-serif; }

* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background: var(--bg-gradient); min-height: 100vh; transition: background 0.3s; }
.portal-header { background: rgba(255,255,255,0.08); backdrop-filter: blur(10px); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.1); }
.brand { display: flex; align-items: center; gap: 12px; color: white; }
.brand-icon { width: 40px; height: 40px; background: #e94560; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.top-actions { display: flex; align-items: center; gap: 12px; }
.btn-circle { width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.btn-circle:hover { background: rgba(255,255,255,0.2); }
.btn-logout { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 10px; cursor: pointer; font-size: 13px; }
.btn-logout:hover { background: rgba(255,255,255,0.2); }
.portal-content { max-width: 960px; margin: 0 auto; padding: 36px 24px; }
.glass-card { background: var(--card-bg); border-radius: 24px; padding: 28px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); margin-bottom: 24px; transition: all 0.3s; }
.section-title { font-size: 15px; font-weight: 700; color: var(--text-main); margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.profile-header { display: flex; align-items: flex-start; gap: 24px; margin-bottom: 28px; flex-wrap: wrap; }
.avatar-wrap { position: relative; cursor: pointer; flex-shrink: 0; }
.avatar-img { width: 88px; height: 88px; border-radius: 22px; object-fit: cover; background: linear-gradient(135deg,#0f3460,#533483); display: flex; align-items: center; justify-content: center; font-size: 34px; font-weight: 700; color: white; overflow: hidden; }
.avatar-img img { width: 100%; height: 100%; object-fit: cover; }
.avatar-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); border-radius: 22px; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; }
.avatar-wrap:hover .avatar-overlay { opacity: 1; }
.avatar-overlay i { color: white; font-size: 20px; }
.profile-name { font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
.profile-meta { display: flex; flex-wrap: wrap; gap: 16px; color: var(--text-muted); font-size: 13.5px; margin-bottom: 10px; }
.profile-meta span { display: flex; align-items: center; gap: 6px; }
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
.badge-success { background: #f0fdf4; color: #166534; }
.badge-danger  { background: #fff1f2; color: #9f1239; }
.badge-info    { background: #eff6ff; color: #1e40af; }
.badge-warning { background: #fffbeb; color: #92400e; }
.badge-purple  { background: #faf5ff; color: #6b21a8; }
.badge-nfc { background: linear-gradient(135deg,#0f3460,#533483); color: white; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; background: rgba(0,0,0,0.03); border-radius: 16px; padding: 20px; }
body.dark .info-grid { background: rgba(255,255,255,0.03); }
.info-item label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }
.info-item div { font-size: 14px; font-weight: 600; color: var(--text-main); }
.tab-row { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.tab { padding: 9px 18px; border-radius: 10px; font-size: 13.5px; font-weight: 600; cursor: pointer; background: rgba(0,0,0,0.05); color: var(--text-muted); border: none; transition: all 0.2s; }
body.dark .tab { background: rgba(255,255,255,0.05); }
.tab.active { background: #0f3460; color: white; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { background: rgba(0,0,0,0.02); padding: 11px 14px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--border); }
[dir="rtl"] .data-table th { text-align: right; }
.data-table td { padding: 13px 14px; border-bottom: 1px solid var(--border); font-size: 13.5px; color: var(--text-main); }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: rgba(0,0,0,0.01); }
.btn-book { background: linear-gradient(135deg,#0f3460,#533483); color: white; border: none; padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }
.btn-book:hover { opacity: 0.9; }
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 500; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: var(--card-bg); border-radius: 24px; padding: 32px; max-width: 480px; width: 95%; box-shadow: 0 24px 80px rgba(0,0,0,0.5); max-height: 90vh; overflow-y: auto; color: var(--text-main); }
.modal h3 { font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 20px; }
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text-main); margin-bottom: 6px; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 13.5px; font-family: inherit; background: transparent; color: var(--text-main); }
.form-control:focus { outline: none; border-color: #0f3460; }
.btn-primary { background: #0f3460; color: white; border: none; padding: 11px 22px; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-outline { background: transparent; color: var(--text-muted); border: 1.5px solid var(--border); padding: 11px 22px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
.ord-card { border: 1.5px solid var(--border); border-radius: 16px; padding: 20px; margin-bottom: 16px; }
.ord-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
.med-chip { display: inline-flex; flex-direction: column; background: rgba(0,0,0,0.02); border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; margin: 4px; }
.med-name { font-weight: 700; font-size: 13px; color: var(--text-main); }
.med-info { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.btn-pdf-sm { display: inline-flex; align-items: center; gap: 6px; background: #fff1f2; color: #e94560; border: 1px solid #fecdd3; border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; }
input[type="file"] { display: none; }
</style>
</head>
<body>

<header class="portal-header">
  <div class="brand">
    <div class="brand-icon"><i class="fas fa-hospital-alt"></i></div>
    <div>
      <div style="color:white;font-size:18px;font-weight:800;">SHIFA</div>
      <div style="color:rgba(255,255,255,0.5);font-size:11px;" data-i18n="Patient Portal">Patient Portal</div>
    </div>
  </div>
  <div class="top-actions">
    <a href="/medical-chat" class="btn-circle" style="text-decoration:none; display:flex; align-items:center; justify-content:center;" title="AI Medical Assistant">
      <i class="fas fa-robot"></i>
    </a>
    <button class="btn-circle" onclick="toggleLang()" id="lang-btn">AR</button>
    <button class="btn-circle" onclick="toggleTheme()" id="theme-btn"><i class="fas fa-moon"></i></button>
    <button class="btn-logout" onclick="logoutPatient()"><i class="fas fa-sign-out-alt"></i> <span data-i18n="Logout">Logout</span></button>
  </div>
</header>

<div class="portal-content">
  <!-- Profile Card -->
  <div class="glass-card">
    <div class="profile-header">
      <div class="avatar-wrap" onclick="document.getElementById('photo-upload').click()" title="Change photo">
        <div class="avatar-img" id="p-avatar"><span id="p-initial">?</span></div>
        <div class="avatar-overlay"><i class="fas fa-camera"></i></div>
      </div>
      <input type="file" id="photo-upload" accept="image/*" onchange="uploadPhoto(this)">

      <div style="flex:1;">
        <div class="profile-name" id="p-name">Loading...</div>
        <div class="profile-meta">
          <span><i class="fas fa-calendar"></i><span id="p-age">—</span> <span data-i18n="yrs">yrs</span></span>
          <span><i class="fas fa-venus-mars"></i><span id="p-gender">—</span></span>
          <span style="color:#e94560;font-weight:600;"><i class="fas fa-tint"></i><span id="p-blood">—</span></span>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
          <span class="badge badge-nfc"><i class="fas fa-wifi"></i> <span data-i18n="NFC Authenticated">NFC Authenticated</span></span>
          <span class="badge badge-success" id="p-status-badge"><i class="fas fa-check-circle"></i> <span data-i18n="Active Patient">Active Patient</span></span>
        </div>
      </div>
    </div>

    <div class="info-grid">
      <div class="info-item"><label data-i18n="Phone">Phone</label><div id="p-phone">—</div></div>
      <div class="info-item"><label data-i18n="Email">Email</label><div id="p-email">—</div></div>
      <div class="info-item"><label data-i18n="Emergency Contact">Emergency Contact</label><div id="p-emergency">—</div></div>
      <div class="info-item"><label data-i18n="Allergies">Allergies</label><div id="p-allergies" style="color:#e94560;">—</div></div>
      <div class="info-item"><label data-i18n="Address">Address</label><div id="p-address">—</div></div>
      <div class="info-item"><label data-i18n="Date of Birth">Date of Birth</label><div id="p-dob">—</div></div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="glass-card" style="padding:24px;">
    <div class="tab-row">
      <button class="tab active" onclick="showTab('appointments')"><i class="fas fa-calendar-check"></i> <span data-i18n="My Appointments">My Appointments</span></button>
      <button class="tab" onclick="showTab('ordonnances')"><i class="fas fa-prescription"></i> <span data-i18n="My Prescriptions">My Prescriptions</span></button>
      <button class="tab" onclick="showTab('vitals')"><i class="fas fa-heartbeat"></i> <span data-i18n="Vitals">Vitals</span></button>
      <button class="tab" onclick="showTab('history')"><i class="fas fa-history"></i> <span data-i18n="Medical History">Medical History</span></button>
      <button class="tab" onclick="showTab('labresults')"><i class="fas fa-flask"></i> <span data-i18n="Lab Results">Lab Results</span></button>
      <button class="tab" onclick="showTab('recommendations')"><i class="fas fa-lightbulb"></i> <span data-i18n="Recommendations">Recommendations</span></button>
    </div>

    <!-- Appointments Tab -->
    <div id="tab-appointments" class="tab-content active">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div style="font-size:14px;font-weight:700;" data-i18n="Scheduled Appointments">Scheduled Appointments</div>
        <button class="btn-book" onclick="document.getElementById('book-modal').classList.add('open')"><i class="fas fa-plus"></i> <span data-i18n="Book Appointment">Book Appointment</span></button>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th data-i18n="Date & Time">Date & Time</th>
            <th data-i18n="Doctor">Doctor</th>
            <th data-i18n="Specialty">Specialty</th>
            <th data-i18n="Status">Status</th>
            <th data-i18n="Notes">Notes</th>
          </tr>
        </thead>
        <tbody id="app-body"><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
      </table>
    </div>

    <!-- Ordonnances Tab -->
    <div id="tab-ordonnances" class="tab-content">
      <div id="ord-container"><div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></div></div>
    </div>

    <!-- Vitals Tab -->
    <div id="tab-vitals" class="tab-content">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div style="font-size:14px;font-weight:700;" data-i18n="Vitals History">Vitals History</div>
        <a href="/vitals/create/{{ patientId }}" class="btn-book" style="text-decoration:none;"><i class="fas fa-plus"></i> <span data-i18n="Add Vitals">Add Vitals</span></a>
      </div>
      <div id="vitals-container">
        <div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></div>
      </div>
    </div>

    <!-- Medical History Tab -->
    <div id="tab-history" class="tab-content">
      <table class="data-table">
        <thead>
          <tr>
            <th data-i18n="Date">Date</th>
            <th data-i18n="Doctor">Doctor</th>
            <th data-i18n="Diagnosis">Diagnosis</th>
            <th data-i18n="Type">Type</th>
            <th data-i18n="Prescription">Prescription</th>
          </tr>
        </thead>
        <tbody id="history-body"><tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></td></tr></tbody>
      </table>
    </div>

    <!-- Lab Results Tab -->
    <div id="tab-labresults" class="tab-content">
      <div id="lab-results-container">
        <div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></div>
      </div>
    </div>

    <!-- Recommendations Tab -->
    <div id="tab-recommendations" class="tab-content">
      <div id="recommendations-container">
        <div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></div>
      </div>
    </div>
  </div>
</div>

<!-- Book Appointment Modal -->
<div id="book-modal" class="modal-overlay">
  <div class="modal">
    <h3 data-i18n="Book an Appointment">Book an Appointment</h3>
    <div class="form-group">
      <label class="form-label" data-i18n="Select Doctor *">Select Doctor *</label>
      <select id="book-doctor" class="form-control">
        <option value="" data-i18n="Loading doctors...">Loading doctors...</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" data-i18n="Date & Time *">Date & Time *</label>
      <input type="datetime-local" id="book-date" class="form-control">
    </div>
    <div class="form-group">
      <label class="form-label" data-i18n="Reason / Notes">Reason / Notes</label>
      <textarea id="book-notes" class="form-control" rows="2" placeholder="Brief reason..."></textarea>
    </div>
    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
      <button class="btn-outline" onclick="document.getElementById('book-modal').classList.remove('open')" data-i18n="Cancel">Cancel</button>
      <button class="btn-primary" onclick="bookAppointment()"><i class="fas fa-check"></i> <span data-i18n="Confirm">Confirm</span></button>
    </div>
    <div id="book-status" style="margin-top:12px;font-size:13px;"></div>
  </div>
</div>

<script>
const translations = {
  ar: {
    "Patient Portal": "بوابة المريض", "Logout": "خروج", "yrs": "سنة", "NFC Authenticated": "موثق بـ NFC",
    "Active Patient": "مريض نشط", "Phone": "الهاتف", "Email": "الإيميل", "Emergency Contact": "اتصال الطوارئ",
    "Allergies": "الحساسية", "Address": "العنوان", "Date of Birth": "تاريخ الميلاد", "My Appointments": "مواعيدي",
    "My Prescriptions": "وصفاتي", "Medical History": "السجل الطبي", "Lab Results": "نتائج المختبر",
    "Scheduled Appointments": "المواعيد المجدولة", "Book Appointment": "حجز موعد", "Date & Time": "التاريخ والوقت",
    "Doctor": "الطبيب", "Specialty": "التخصص", "Status": "الحالة", "Notes": "ملاحظات", "Date": "التاريخ",
    "Diagnosis": "التشخيص", "Type": "النوع", "Prescription": "الوصفة", "Book an Appointment": "حجز موعد جديد",
    "Select Doctor *": "اختر الطبيب *", "Date & Time *": "التاريخ والوقت *", "Reason / Notes": "السبب / ملاحظات",
    "Cancel": "إلغاء", "Confirm": "تأكيد", "Loading doctors...": "جاري تحميل الأطباء...", "None": "لا يوجد",
    "Appointment booked!": "تم حجز الموعد!", "Please select a doctor and date.": "يرجى اختيار الطبيب والتاريخ.",
    "No appointments scheduled": "لا توجد مواعيد مجدولة", "No prescriptions found": "لا توجد وصفات طبية",
    "No medical history": "لا يوجد سجل طبي", "No lab results uploaded yet": "لا توجد نتائج مختبر مرفوعة بعد",
    "pending": "pending", "confirmed": "confirmed", "completed": "completed", "cancelled": "cancelled",
    "Vitals": "Vital Signs", "Vitals History": "Vitals History", "Add Vitals": "Add Vitals"
  }
};

let currentLang = localStorage.getItem('patient_lang') || 'en';
let currentTheme = localStorage.getItem('patient_theme') || 'light';

function applyLang() {
  document.documentElement.dir = currentLang === 'ar' ? 'rtl' : 'ltr';
  document.getElementById('lang-btn').textContent = currentLang === 'ar' ? 'EN' : 'AR';
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (currentLang === 'ar' && translations.ar[key]) {
      el.textContent = translations.ar[key];
    } else {
      el.textContent = key;
    }
  });
  // Update placeholders
  const notes = document.getElementById('book-notes');
  if (notes) notes.placeholder = currentLang === 'ar' ? "سبب الزيارة..." : "Brief reason...";
}

function toggleLang() {
  currentLang = currentLang === 'en' ? 'ar' : 'en';
  localStorage.setItem('patient_lang', currentLang);
  applyLang();
  loadProfile(); // Refresh lists to apply status translations
}

function toggleTheme() {
  currentTheme = currentTheme === 'light' ? 'dark' : 'light';
  localStorage.setItem('patient_theme', currentTheme);
  applyTheme();
}

function applyTheme() {
  document.body.classList.toggle('dark', currentTheme === 'dark');
  const icon = document.querySelector('#theme-btn i');
  if (icon) icon.className = currentTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// Initial apply
applyLang();
applyTheme();

const token = sessionStorage.getItem('patient_token');
if (!token) { window.location.href = '/login'; }

const h = {'Content-Type':'application/json','Accept':'application/json','Authorization':'Bearer '+token};
let patientId = null;
let patientData = null;

function showTab(name) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  event.target.classList.add('active');
  if (name === 'labresults') loadLabResults();
  if (name === 'recommendations') loadRecommendations();
  if (name === 'vitals') loadVitals();
}

async function loadLabResults() {
  const container = document.getElementById('lab-results-container');
  try {
    const r = await fetch('/api/patient/lab-results', {headers: h});
    const {data} = await r.json();
    if (!data || !data.length) {
      container.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8;"><i class="fas fa-flask" style="font-size:32px;margin-bottom:12px;display:block;"></i>No lab results uploaded yet</div>';
      return;
    }
    container.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">` +
      data.map(res => `
        <div style="border:1.5px solid #e2e8f0;border-radius:14px;overflow:hidden;background:#f8fafc;">
          ${res.file_type === 'image'
            ? `<img src="/storage/${res.file_path}" style="width:100%;height:140px;object-fit:cover;">`
            : `<div style="height:140px;display:flex;align-items:center;justify-content:center;background:#fef3c7;"><i class="fas fa-file-pdf" style="font-size:48px;color:#d97706;"></i></div>`}
          <div style="padding:12px;">
            <div style="font-weight:700;font-size:13px;color:#0f3460;">${res.title || 'Lab Result'}</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">${res.laboratory?.name || 'Laboratory'} · ${new Date(res.created_at).toLocaleDateString()}</div>
            ${res.note ? `<div style="font-size:12px;color:#64748b;margin-top:4px;">${res.note}</div>` : ''}
            <a href="/storage/${res.file_path}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;margin-top:8px;color:#f59e0b;font-size:12px;font-weight:700;text-decoration:none;"><i class="fas fa-external-link-alt"></i> Open File</a>
          </div>
        </div>`).join('') + `</div>`;
  } catch(e) {
    container.innerHTML = '<div style="text-align:center;padding:24px;color:#e94560;">Error loading lab results.</div>';
  }
}

async function loadVitals() {
  const container = document.getElementById('vitals-container');
  try {
    const r = await fetch(`/vitals/${patientId}`, {headers: h});
    const html = await r.text();
    container.innerHTML = html;
  } catch(e) {
    container.innerHTML = '<div style="text-align:center;padding:24px;color:#e94560;">Error loading vitals.</div>';
  }
}

async function loadProfile() {
  try {
    const r = await fetch('/api/patient/profile', {headers: h});
    if (r.status === 401) { logoutPatient(); return; }
    const {data} = await r.json();
    if (!data) { logoutPatient(); return; }
    patientData = data;
    patientId = data.id;

    // Header info
    const av = document.getElementById('p-avatar');
    if (data.photo) {
      av.innerHTML = `<img src="/storage/${data.photo}" alt="${data.name}">`;
    } else {
      av.innerHTML = `<span id="p-initial">${data.name.charAt(0).toUpperCase()}</span>`;
    }
    document.getElementById('p-name').textContent = data.name;
    document.getElementById('p-age').textContent = ' ' + data.age;
    document.getElementById('p-gender').textContent = ' ' + (data.gender || '—');
    document.getElementById('p-blood').textContent = ' ' + (data.blood_type || '—');
    document.getElementById('p-phone').textContent = data.phone || '—';
    document.getElementById('p-email').textContent = data.email || '—';
    document.getElementById('p-emergency').textContent = data.emergency_contact || '—';
    document.getElementById('p-allergies').textContent = data.allergies || 'None';
    document.getElementById('p-address').textContent = data.address || '—';
    document.getElementById('p-dob').textContent = data.date_of_birth || '—';

    // Appointments
    renderAppointments(data.appointments || []);

    // Medical records
    renderHistory(data.medical_records || [], data.ordonnances || []);

    // Load ordonnances separately
    loadOrdonnances();
    loadDoctors();
    loadRecommendations();

  } catch(e) {
    console.error(e);
  }
}

function renderAppointments(apps) {
  const tb = document.getElementById('app-body');
  const emptyMsg = currentLang === 'ar' ? translations.ar["No appointments scheduled"] : "No appointments scheduled";
  if (!apps.length) { tb.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">${emptyMsg}</td></tr>`; return; }
  const statColor = {pending:'badge-info',confirmed:'badge-success',completed:'badge-purple',cancelled:'badge-danger'};
  tb.innerHTML = apps.map(a => {
    const statusText = currentLang === 'ar' ? (translations.ar[a.status] || a.status) : a.status;
    return `<tr>
      <td style="font-weight:600;">${new Date(a.scheduled_at).toLocaleString(currentLang === 'ar'?'ar-EG':'en-US')}</td>
      <td>${a.doctor?.user?.name || '—'}</td>
      <td><span style="color:var(--text-muted);font-size:12px;">${a.doctor?.specialty || '—'}</span></td>
      <td><span class="badge ${statColor[a.status]||'badge-info'}">${statusText}</span></td>
      <td style="color:var(--text-muted);">${a.notes || '—'}</td>
    </tr>`;
  }).join('');
}

async function loadOrdonnances() {
  try {
    const r = await fetch('/api/patient/ordonnances', {headers: h});
    const {data} = await r.json();
    const container = document.getElementById('ord-container');
    const emptyMsg = currentLang === 'ar' ? translations.ar["No prescriptions found"] : "No prescriptions found";
    if (!data || !data.length) {
      container.innerHTML = `<div style="text-align:center;padding:24px;color:#94a3b8;">${emptyMsg}</div>`;
      return;
    }
    container.innerHTML = data.map(o => `
      <div class="ord-card">
        <div class="ord-header">
          <div>
            <div style="font-size:15px;font-weight:700;color:var(--text-main);">${currentLang==='ar'?'وصفة طبية':'Prescription'} #${o.id}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">
              <i class="fas fa-user-md"></i> ${o.doctor?.user?.name || '—'} &nbsp;·&nbsp;
              <i class="fas fa-calendar"></i> ${o.issued_date} &nbsp;·&nbsp;
              ${currentLang==='ar'?'صالح حتى':'Valid until'}: ${o.valid_until || (currentLang==='ar'?'بدون حد':'No limit')}
            </div>
            <div style="margin-top:6px;">
              <span class="badge ${o.type==='laboratory'?'badge-purple':o.type==='nurse'?'badge-info':'badge-primary'}">
                <i class="fas ${o.type==='laboratory'?'fa-microscope':o.type==='nurse'?'fa-syringe':'fa-pills'}"></i> 
                ${o.type === 'laboratory' ? (currentLang==='ar'?'مختبر':'Laboratory') : o.type === 'nurse' ? (currentLang==='ar'?'ممرض':'Nurse') : (currentLang==='ar'?'صيدلية':'Pharmacy')}
              </span>
            </div>
          </div>
          <div style="display:flex;gap:8px;align-items:center;">
            <button class="btn-pdf-sm" style="background:${o.is_taken?'#10b981':'#f59e0b'};color:white;border:none;" onclick="toggleTaken(${o.id})">
              <i class="fas ${o.is_taken?'fa-check-circle':'fa-circle'}"></i> 
              ${currentLang==='ar'?(o.is_taken?'تم التناول':'قيد التناول'):(o.is_taken?'Taken':'Mark Taken')}
            </button>
            <span class="badge ${o.status==='active'?'badge-success':'badge-warning'}">${currentLang==='ar'?(o.status==='active'?'نشطة':'غير نشطة'):o.status}</span>
            <a href="/api/ordonnances/${o.id}/pdf?token=${token}" target="_blank" class="btn-pdf-sm"><i class="fas fa-print"></i> ${currentLang==='ar'?'طباعة':'Print'}</a>
          </div>
        </div>
        ${o.instructions ? `<div style="background:#fffbeb;color:#92400e;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:14px;"><i class="fas fa-info-circle"></i> ${o.instructions}</div>` : ''}
        ${o.explanation ? `
          <div style="background:#f0fdf4;color:#166534;padding:12px 16px;border-radius:12px;margin-bottom:14px;border-left:4px solid #22c55e;">
            <div style="display:flex;align-items:center;margin-bottom:8px;">
              <i class="fas fa-robot" style="color:#22c55e;margin-right:8px;"></i>
              <strong style="color:#166534;">${currentLang==='ar'?'AI Explanation':'AI Explanation'}</strong>
              ${!o.explanation_generated ? `<span style="margin-left:8px;font-size:11px;color:#f59e0b;">(${currentLang==='ar'?'Being generated':'Being generated'})</span>` : ''}
            </div>
            <div style="font-size:13px;line-height:1.6;color:#15803d;">${o.explanation}</div>
          </div>
        ` : o.explanation_generated === false ? `
          <div style="background:#fef2f2;color:#dc2626;padding:12px 16px;border-radius:12px;margin-bottom:14px;border-left:4px solid #ef4444;">
            <div style="display:flex;align-items:center;">
              <i class="fas fa-exclamation-triangle" style="color:#ef4444;margin-right:8px;"></i>
              <strong style="color:#dc2626;">${currentLang==='ar'?'AI Explanation Unavailable':'AI Explanation Unavailable'}</strong>
            </div>
            <div style="font-size:12px;color:#991b1b;margin-top:4px;">${currentLang==='ar'?'Unable to generate explanation at this time':'Unable to generate explanation at this time'}</div>
          </div>
        ` : `
          <div style="background:#f8fafc;color:#64748b;padding:12px 16px;border-radius:12px;margin-bottom:14px;border-left:4px solid #cbd5e1;">
            <div style="display:flex;align-items:center;">
              <i class="fas fa-clock" style="color:#94a3b8;margin-right:8px;"></i>
              <strong style="color:#64748b;">${currentLang==='ar'?'AI Explanation':'AI Explanation'}</strong>
            </div>
            <div style="font-size:12px;color:#94a3b8;margin-top:4px;">${currentLang==='ar'?'Generating explanation...':'Generating explanation...'}</div>
          </div>
        `}
        <div style="display:flex;flex-wrap:wrap;gap:6px;">
          ${(o.medications||[]).map(m => `
            <div class="med-chip">
              <div class="med-name"><i class="fas ${o.type==='laboratory'?'fa-flask':o.type==='nurse'?'fa-procedures':'fa-capsules'}" style="color:#533483;margin-right:4px;"></i>${m.name}</div>
              ${o.type === 'pharmacy' ? `<div class="med-info">${m.dosage} · ${m.frequency} · ${m.duration}</div>` : ''}
            </div>`).join('')}
        </div>
      </div>`).join('');
  } catch(e) {}
}

async function toggleTaken(id) {
  try {
    const r = await fetch(`/api/ordonnances/${id}/toggle-taken`, {
      method: 'PATCH',
      headers: h
    });
    const data = await r.json();
    if (data.success) {
      loadOrdonnances();
    }
  } catch(e) {}
}

function loadRecommendations() {
  const container = document.getElementById('recommendations-container');
  const emptyMsg = currentLang === 'ar' ? 'لا توجد توصيات' : 'No recommendations found';
  
  if (container) {
    container.innerHTML = `<div style="text-align:center;padding:24px;color:#94a3b8;"><i class="fas fa-spinner fa-spin"></i></div>`;
    
    fetch(`/api/patients/${patientId}/recommendations`, {
      method: 'GET',
      headers: h
    }).then(r => r.json())
    .then(data => {
      if (data.success && data.data && data.data.length > 0) {
        container.innerHTML = data.data.map(rec => `
          <div class="rec-card" style="border-left:4px solid ${rec.priority === 'urgent' ? '#dc2626' : rec.priority === 'high' ? '#f59e0b' : '#3b82f6'};">
            <div class="rec-header">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <div style="font-size:14px;font-weight:700;color:var(--text-main);">
                  ${currentLang === 'ar' ? 'التوصيات' : 'Recommendations'}
                </div>
                <span class="priority-badge ${rec.priority}">
                  ${rec.priority}
                </span>
              </div>
              <div style="font-size:12px;color:var(--text-muted);">
                ${new Date(rec.created_at).toLocaleDateString()}
              </div>
            </div>
            <div style="background:#fffbeb;color:#92400e;padding:12px;border-radius:8px;margin-bottom:12px;">
              <div style="display:flex;align-items:center;margin-bottom:8px;">
                <i class="fas fa-lightbulb" style="color:#f59e0b;margin-right:8px;"></i>
                <strong style="color:#92400e;">${currentLang === 'ar' ? 'اقتراح الأطباء' : 'Recommended Doctors'}</strong>
              </div>
              <div style="font-size:13px;line-height:1.6;">
                ${(rec.recommended_doctors || []).map(doc => `<span class="doctor-tag">${doc}</span>`).join(' ')}
              </div>
            </div>
            <div style="background:#f0fdf4;color:#166534;padding:12px;border-radius:8px;margin-bottom:12px;">
              <div style="display:flex;align-items:center;margin-bottom:8px;">
                <i class="fas fa-vial" style="color:#22c55e;margin-right:8px;"></i>
                <strong style="color:#166534;">${currentLang === 'ar' ? 'الاختبارات الموصى بها' : 'Recommended Tests'}</strong>
              </div>
              <div style="font-size:13px;line-height:1.6;">
                ${(rec.recommended_tests || []).map(test => `<span class="test-tag">${test}</span>`).join(' ')}
              </div>
            </div>
            <div style="background:#f8fafc;color:#64748b;padding:12px;border-radius:8px;margin-bottom:12px;">
              <div style="display:flex;align-items:center;margin-bottom:8px;">
                <i class="fas fa-brain" style="color:#94a3b8;margin-right:8px;"></i>
                <strong style="color:#64748b;">${currentLang === 'ar' ? 'السبب' : 'AI Reasoning'}</strong>
              </div>
              <div style="font-size:12px;color:#15803d;line-height:1.6;">
                ${rec.reasoning || 'No reasoning provided'}
              </div>
            </div>
          </div>
        `).join('');
      } else {
        container.innerHTML = `<div style="text-align:center;padding:24px;color:#94a3b8;">${emptyMsg}</div>`;
      }
    })
    .catch(e => {
      console.error('Recommendations load error:', e);
      container.innerHTML = `<div style="text-align:center;padding:24px;color:#e94560;">${currentLang === 'ar' ? 'خطأ في التحميل' : 'Error loading recommendations'}</div>`;
    });
  }
}

function renderHistory(records, ords) {
  const tb = document.getElementById('history-body');
  const emptyMsg = currentLang === 'ar' ? translations.ar["No medical history"] : "No medical history";
  if (!records.length) { tb.innerHTML = `<tr><td colspan="5" style="text-align:center;padding:24px;color:#94a3b8;">${emptyMsg}</td></tr>`; return; }
  const typeColor = {consultation:'badge-info',follow_up:'badge-purple',emergency:'badge-danger',routine:'badge-success'};
  tb.innerHTML = records.map(rec => {
    const ord = ords.find(o => o.medical_record_id === rec.id);
    const typeText = currentLang === 'ar' ? (translations.ar[rec.visit_type] || rec.visit_type) : rec.visit_type;
    return `<tr>
      <td>${rec.visit_date}</td>
      <td style="font-weight:600;">${rec.doctor?.user?.name || '—'}<div style="font-size:11px;color:var(--text-muted);">${rec.doctor?.specialty || ''}</div></td>
      <td>${rec.diagnosis}</td>
      <td><span class="badge ${typeColor[rec.visit_type]||'badge-info'}">${typeText}</span></td>
      <td>${ord ? `<a href="/api/ordonnances/${ord.id}/pdf?token=${token}" target="_blank" class="btn-pdf-sm"><i class="fas fa-print"></i> PDF</a>` : `<span style="color:#94a3b8;">${currentLang==='ar'?'لا يوجد':'None'}</span>`}</td>
    </tr>`;
  }).join('');
}

async function loadDoctors() {
  try {
    const r = await fetch('/api/public/doctors', {headers: h});
    const {data} = await r.json();
    const docs = data.data || data;
    const sel = document.getElementById('book-doctor');
    const loadMsg = currentLang === 'ar' ? "اختر الطبيب..." : "Select a doctor...";
    sel.innerHTML = `<option value="">${loadMsg}</option>`;
    docs.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.name} — ${d.specialty}</option>`);
  } catch(e) {}
}

async function bookAppointment() {
  const doctorId = document.getElementById('book-doctor').value;
  const date     = document.getElementById('book-date').value;
  const notes    = document.getElementById('book-notes').value;
  const status   = document.getElementById('book-status');

  const errorMsg = currentLang === 'ar' ? translations.ar["Please select a doctor and date."] : "Please select a doctor and date.";
  if (!doctorId || !date) { status.innerHTML = `<span style="color:#e94560;">${errorMsg}</span>`; return; }

  try {
    const r = await fetch('/api/patient/appointments/book', {
      method: 'POST',
      headers: h,
      body: JSON.stringify({doctor_id: doctorId, scheduled_at: date, reason: notes})
    });
    const data = await r.json();
    if (data.success) {
      const successMsg = currentLang === 'ar' ? translations.ar["Appointment booked!"] : "Appointment booked!";
      status.innerHTML = `<span style="color:#166534;background:#f0fdf4;padding:8px 14px;border-radius:10px;"><i class="fas fa-check-circle"></i> ${successMsg}</span>`;
      setTimeout(() => {
        document.getElementById('book-modal').classList.remove('open');
        status.innerHTML = '';
        loadProfile();
      }, 1500);
    } else {
      status.innerHTML = `<span style="color:#e94560;">${JSON.stringify(data.errors || data.message)}</span>`;
    }
  } catch(e) {
    status.innerHTML = `<span style="color:#e94560;">${currentLang==='ar'?'خطأ في الاتصال':'Connection error.'}</span>`;
  }
}

async function uploadPhoto(input) {
  if (!input.files[0]) return;
  const formData = new FormData();
  formData.append('photo', input.files[0]);
  formData.append('type', 'patient');
  formData.append('entity_id', patientId);

  try {
    const r = await fetch('/api/upload/photo', {
      method: 'POST',
      headers: {'Accept':'application/json','Authorization':'Bearer '+token},
      body: formData
    });
    const data = await r.json();
    if (data.success) {
      document.getElementById('p-avatar').innerHTML = `<img src="${data.url}?t=${Date.now()}" alt="Photo">`;
    }
  } catch(e) {}
}

// Set default datetime for booking
const nowStr = new Date(Date.now() + 3600000).toISOString().slice(0,16);
document.getElementById('book-date').value = nowStr;

// Periodic refresh for AI explanations
setInterval(() => {
  if (document.visibilityState === 'visible') {
    loadOrdonnances(); // Refresh prescriptions to check for updated explanations
  }
}, 30000); // Check every 30 seconds

// Init
loadProfile();

function logoutPatient() {
  sessionStorage.removeItem('patient_token');
  window.location.href = '/login';
}
</script>
</body>
</html>
