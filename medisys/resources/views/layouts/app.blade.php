<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'SHIFA') — Hospital Management</title>
<meta name="description" content="SHIFA — Complete Hospital & Clinic Management System">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Kufi+Arabic:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --primary: #0f3460;
  --primary-light: #16213e;
  --accent: #e94560;
  --accent2: #533483;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;
  --bg: #f0f4f8;
  --card: #ffffff;
  --sidebar-w: 260px;
  --text: #1e293b;
  --text-muted: #64748b;
  --border: #e2e8f0;
  --shadow: 0 4px 24px rgba(15,52,96,0.08);
}

body.dark {
  --bg: #0b0e14;
  --card: #151921;
  --text: #e2e8f0;
  --text-muted: #94a3b8;
  --border: #262c36;
  --shadow: 0 4px 24px rgba(0,0,0,0.4);
  --primary-light: #0d1117;
}

[dir="rtl"] { font-family: 'Noto Kufi Arabic', 'Inter', sans-serif; }
[dir="rtl"] .sidebar { left: auto; right: 0; box-shadow: -4px 0 20px rgba(0,0,0,0.2); }
[dir="rtl"] .main { margin-left: 0; margin-right: var(--sidebar-w); }
[dir="rtl"] .topbar-btn .badge-dot { right: auto; left: 6px; }
[dir="rtl"] .stat-card { text-align: right; }
[dir="rtl"] table.data-table th, [dir="rtl"] table.data-table td { text-align: right; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

/* ── Sidebar ─────────────────────────────── */
.sidebar {
  width: var(--sidebar-w); background: var(--primary-light);
  display: flex; flex-direction: column;
  position: fixed; top: 0; left: 0; height: 100vh;
  z-index: 100; box-shadow: 4px 0 20px rgba(0,0,0,0.2);
}
.sidebar-brand {
  padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,0.07);
  display: flex; align-items: center; gap: 12px;
}
.brand-icon {
  width: 42px; height: 42px; background: var(--accent);
  border-radius: 12px; display: flex; align-items: center; justify-content: center;
  font-size: 18px; color: white; flex-shrink: 0;
}
.brand-text { color: white; font-weight: 700; font-size: 18px; line-height: 1.1; }
.brand-text span { display: block; font-size: 10px; font-weight: 400; opacity: 0.5; letter-spacing: 1px; text-transform: uppercase; }
.sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; }
.nav-section-label {
  color: rgba(255,255,255,0.3); font-size: 10px; text-transform: uppercase;
  letter-spacing: 1.5px; padding: 12px 20px 6px; font-weight: 600;
}
.nav-item { margin: 2px 10px; }
.nav-link {
  display: flex; align-items: center; gap: 12px; padding: 11px 14px;
  color: rgba(255,255,255,0.6); text-decoration: none; border-radius: 10px;
  font-size: 13.5px; font-weight: 500; transition: all 0.2s;
}
.nav-link:hover { background: rgba(255,255,255,0.08); color: white; }
.nav-link.active { background: var(--accent); color: white; box-shadow: 0 4px 12px rgba(233,69,96,0.4); }
.nav-link .icon { width: 18px; text-align: center; font-size: 14px; }
.sidebar-footer {
  padding: 16px; border-top: 1px solid rgba(255,255,255,0.07);
}
.user-card {
  display: flex; align-items: center; gap: 10px;
  background: rgba(255,255,255,0.06); border-radius: 10px; padding: 10px 12px;
}
.user-avatar {
  width: 36px; height: 36px; background: var(--accent2);
  border-radius: 10px; display: flex; align-items: center; justify-content: center;
  font-size: 14px; color: white; font-weight: 600;
}
.user-info { flex: 1; min-width: 0; }
.user-name { color: white; font-size: 12.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { color: rgba(255,255,255,0.4); font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.5px; }
.logout-btn { color: rgba(255,255,255,0.3); cursor: pointer; font-size: 14px; transition: color 0.2s; }
.logout-btn:hover { color: var(--accent); }

/* ── Main layout ─────────────────────────── */
.main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
.topbar {
  background: white; padding: 0 28px; height: 64px;
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid var(--border); box-shadow: var(--shadow); position: sticky; top: 0; z-index: 50;
}
.topbar-left { display: flex; align-items: center; gap: 16px; }
.page-title { font-size: 18px; font-weight: 700; color: var(--text); }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-btn {
  width: 38px; height: 38px; border: 1px solid var(--border); border-radius: 10px;
  background: white; cursor: pointer; display: flex; align-items: center; justify-content: center;
  color: var(--text-muted); font-size: 14px; transition: all 0.2s; position: relative;
}
.topbar-btn:hover { background: var(--bg); color: var(--primary); }
.badge-dot {
  position: absolute; top: 6px; right: 6px; width: 8px; height: 8px;
  background: var(--accent); border-radius: 50%; border: 2px solid white;
}
.page-content { padding: 28px; flex: 1; }

/* ── Cards & Stats ───────────────────────── */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 28px; }
.stat-card {
  background: var(--card); border-radius: 16px; padding: 22px;
  box-shadow: var(--shadow); border: 1px solid var(--border);
  display: flex; align-items: center; gap: 16px;
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(15,52,96,0.12); }
.stat-icon {
  width: 54px; height: 54px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0;
}
.stat-icon.blue   { background: #eff6ff; color: var(--info); }
.stat-icon.red    { background: #fff1f2; color: var(--accent); }
.stat-icon.green  { background: #f0fdf4; color: var(--success); }
.stat-icon.purple { background: #faf5ff; color: var(--accent2); }
.stat-icon.orange { background: #fffbeb; color: var(--warning); }
.stat-value { font-size: 28px; font-weight: 700; line-height: 1; }
.stat-label { font-size: 12.5px; color: var(--text-muted); margin-top: 4px; }

/* ── Tables ──────────────────────────────── */
.card { background: white; border-radius: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); overflow: hidden; }
.card-header {
  padding: 18px 22px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.card-title { font-size: 15px; font-weight: 700; }
.card-body { padding: 0; }
table.data-table { width: 100%; border-collapse: collapse; }
table.data-table th {
  background: #f8fafc; padding: 12px 16px; text-align: left;
  font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;
  color: var(--text-muted); font-weight: 600; border-bottom: 1px solid var(--border);
}
table.data-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13.5px; }
table.data-table tr:last-child td { border-bottom: none; }
table.data-table tr:hover td { background: #fafbff; }

/* ── Badges ──────────────────────────────── */
.badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
.badge-success { background: #f0fdf4; color: #166534; }
.badge-danger  { background: #fff1f2; color: #9f1239; }
.badge-warning { background: #fffbeb; color: #92400e; }
.badge-info    { background: #eff6ff; color: #1e40af; }
.badge-purple  { background: #faf5ff; color: #6b21a8; }

/* ── Buttons ─────────────────────────────── */
.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 18px; border-radius: 10px; font-size: 13px; font-weight: 600;
  cursor: pointer; border: none; text-decoration: none; transition: all 0.2s;
}
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: #0c2d54; box-shadow: 0 4px 12px rgba(15,52,96,0.3); }
.btn-accent { background: var(--accent); color: white; }
.btn-accent:hover { background: #c73852; box-shadow: 0 4px 12px rgba(233,69,96,0.3); }
.btn-outline { background: transparent; color: var(--text); border: 1px solid var(--border); }
.btn-outline:hover { background: var(--bg); }
.btn-sm { padding: 5px 12px; font-size: 12px; }

/* ── Forms ───────────────────────────────── */
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 12.5px; font-weight: 600; color: var(--text); margin-bottom: 6px; }
.form-control {
  width: 100%; padding: 10px 14px; border: 1.5px solid var(--border); border-radius: 10px;
  font-size: 13.5px; font-family: inherit; color: var(--text); background: white; transition: border-color 0.2s;
}
.form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15,52,96,0.08); }
.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }

/* ── Avatar ──────────────────────────────── */
.avatar {
  width: 34px; height: 34px; border-radius: 10px;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 700; color: white; flex-shrink: 0;
}

/* ── NFC badge ───────────────────────────── */
.nfc-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: linear-gradient(135deg, #0f3460, #533483);
  color: white; border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 600;
}

/* ── Scrollbar ───────────────────────────── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
</style>
@stack('styles')
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fas fa-hospital-alt"></i></div>
    <div class="brand-text">SHIFA<span>Hospital Management</span></div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">{{ __('Main') }}</div>
    <div class="nav-item">
      <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" id="nav-dashboard">
        <span class="icon"><i class="fas fa-th-large"></i></span> {{ __('Dashboard') }}
      </a>
    </div>

    <div class="nav-section-label">{{ __('Clinical') }}</div>
    <div class="nav-item">
      <a href="/patients" class="nav-link {{ request()->is('patients*') ? 'active' : '' }}" id="nav-patients">
        <span class="icon"><i class="fas fa-user-injured"></i></span> {{ __('Patients') }}
      </a>
    </div>
    <div class="nav-item">
      <a href="/medical-records" class="nav-link {{ request()->is('medical-records*') ? 'active' : '' }}" id="nav-records">
        <span class="icon"><i class="fas fa-file-medical"></i></span> {{ __('Medical Records') }}
      </a>
    </div>
    <div class="nav-item">
      <a href="/ordonnances" class="nav-link {{ request()->is('ordonnances*') ? 'active' : '' }}" id="nav-ordonnances">
        <span class="icon"><i class="fas fa-prescription"></i></span> {{ __('Prescriptions') }}
      </a>
    </div>
    <div class="nav-item">
      <a href="/appointments" class="nav-link {{ request()->is('appointments*') ? 'active' : '' }}" id="nav-appointments">
        <span class="icon"><i class="fas fa-calendar-check"></i></span> {{ __('Appointments') }}
      </a>
    </div>

    <div class="nav-section-label">{{ __('Administration') }}</div>
    <div class="nav-item">
      <a href="/doctors" class="nav-link {{ request()->is('doctors*') ? 'active' : '' }}" id="nav-doctors">
        <span class="icon"><i class="fas fa-user-md"></i></span> {{ __('Doctors') }}
      </a>
    </div>
    <div class="nav-item">
      <a href="/pharmacies" class="nav-link {{ request()->is('pharmacies*') ? 'active' : '' }}" id="nav-pharmacies">
        <span class="icon"><i class="fas fa-pills"></i></span> {{ __('Pharmacies') }}
      </a>
    </div>
    <div class="nav-item">
      <a href="/laboratories" class="nav-link {{ request()->is('laboratories*') ? 'active' : '' }}" id="nav-laboratories">
        <span class="icon"><i class="fas fa-flask"></i></span> {{ __('Laboratories') }}
      </a>
    </div>

    <div class="nav-section-label">{{ __('Support') }}</div>
    <div class="nav-item">
      <a href="/contact" class="nav-link {{ request()->is('contact') ? 'active' : '' }}" id="nav-contact">
        <span class="icon"><i class="fas fa-envelope"></i></span> {{ __('Contact Us') }}
      </a>
    </div>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar" id="sidebar-avatar">-</div>
      <div class="user-info">
        <div class="user-name" id="sidebar-name">Loading...</div>
        <div class="user-role" id="sidebar-role">-</div>
      </div>
      <a href="#" onclick="logout(); return false;" class="logout-btn" title="{{ __('Logout') }}"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </div>
</aside>

<!-- Main -->
<div class="main">
  <header class="topbar" style="background: var(--card);">
    <div class="topbar-left">
      <span class="page-title">@yield('page-title', __('Dashboard'))</span>
    </div>
    <div class="topbar-right">
      <!-- Language Switcher -->
      <div style="display:flex;gap:4px;margin-right:8px;">
        <a href="{{ route('lang.switch', 'en') }}" class="topbar-btn" title="English" style="{{ app()->getLocale() === 'en' ? 'border-color:var(--primary);color:var(--primary);' : '' }}">EN</a>
        <a href="{{ route('lang.switch', 'ar') }}" class="topbar-btn" title="العربية" style="{{ app()->getLocale() === 'ar' ? 'border-color:var(--primary);color:var(--primary);' : '' }}">AR</a>
      </div>

      <!-- Theme Toggle -->
      <button class="topbar-btn" id="theme-toggle" onclick="toggleTheme()" title="{{ __('Toggle Theme') }}">
        <i class="fas fa-moon"></i>
      </button>

      <button class="topbar-btn" id="nfc-scan-btn" title="{{ __('Scan NFC Card') }}">
        <i class="fas fa-wifi"></i>
        <span class="badge-dot"></span>
      </button>
      <button class="topbar-btn" title="{{ __('Notifications') }}">
        <i class="fas fa-bell"></i>
      </button>
    </div>
  </header>

  <main class="page-content">
    @yield('content')
  </main>
</div>

<!-- NFC Scanner Modal -->
<div id="nfc-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:white;border-radius:20px;padding:40px;text-align:center;max-width:400px;width:90%;box-shadow:0 24px 80px rgba(0,0,0,0.3);">
    <div style="width:80px;height:80px;background:linear-gradient(135deg,#0f3460,#533483);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:32px;color:white;margin:0 auto 20px;">
      <i class="fas fa-wifi"></i>
    </div>
    <h3 style="font-size:20px;font-weight:700;margin-bottom:8px;">NFC Card Scanner</h3>
    <p style="color:#64748b;font-size:13.5px;margin-bottom:24px;">Tap or enter the NFC UID to authenticate a patient</p>
    <input id="nfc-uid-input" type="text" class="form-control" placeholder="NFC UID (e.g. NFC001ABC)" style="margin-bottom:16px;">
    <div style="display:flex;gap:10px;justify-content:center;">
      <button class="btn btn-primary" onclick="nfcLogin()"><i class="fas fa-check"></i> Authenticate</button>
      <button class="btn btn-outline" onclick="closeNFC()"><i class="fas fa-times"></i> Cancel</button>
    </div>
    <div id="nfc-result" style="margin-top:16px;"></div>
  </div>
</div>

<script>
// Authentication check and sidebar population
document.addEventListener('DOMContentLoaded', () => {
  const userJson = localStorage.getItem('auth_user');
  
  // Theme init
  if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark');
    const icon = document.querySelector('#theme-toggle i');
    if (icon) icon.className = 'fas fa-sun';
  }

  if (userJson) {
    try {
      const user = JSON.parse(userJson);
      document.getElementById('sidebar-name').textContent = user.name;
      document.getElementById('sidebar-role').textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
      
      const av = document.getElementById('sidebar-avatar');
      if (user.doctor && user.doctor.photo) {
        av.innerHTML = `<img src="${user.doctor.photo}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">`;
      } else {
        av.textContent = user.name.charAt(0).toUpperCase();
      }
      
      // Hide admin-only links for doctors
      if (user.role === 'doctor') {
        document.querySelectorAll('.sidebar-nav .nav-section-label:last-of-type, #nav-doctors, #nav-pharmacies, #nav-laboratories')
          .forEach(el => el.style.display = 'none');
      }
    } catch(e) {}
  }
});

function logout() {
  localStorage.removeItem('auth_token');
  localStorage.removeItem('auth_user');
  window.location.href = '/login';
}

function toggleTheme() {
  const isDark = document.body.classList.toggle('dark');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');
  const icon = document.querySelector('#theme-toggle i');
  if (icon) icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
}

document.getElementById('nfc-scan-btn').addEventListener('click', function() {
  document.getElementById('nfc-modal').style.display = 'flex';
  document.getElementById('nfc-uid-input').focus();
});

function closeNFC() {
  document.getElementById('nfc-modal').style.display = 'none';
  document.getElementById('nfc-result').innerHTML = '';
  document.getElementById('nfc-uid-input').value = '';
}

async function nfcLogin() {
  const uid = document.getElementById('nfc-uid-input').value.trim();
  if (!uid) return;
  const res = document.getElementById('nfc-result');
  res.innerHTML = '<span style="color:#64748b"><i class="fas fa-spinner fa-spin"></i> Authenticating...</span>';
  try {
    const resp = await fetch('/api/nfc-login', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json'},
      body: JSON.stringify({nfc_uid: uid})
    });
    const data = await resp.json();
    if (data.success) {
      sessionStorage.setItem('patient_token', data.token);
      sessionStorage.setItem('patient_name', data.patient.name);
      res.innerHTML = `<div style="background:#f0fdf4;color:#166534;border-radius:10px;padding:14px;font-weight:600;">
        <i class="fas fa-check-circle"></i> Patient identified: <strong>${data.patient.name}</strong><br>
        <a href="/doctor-patient-view?patient_id=${data.patient.id}" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:#0f3460;color:white;padding:9px 18px;border-radius:10px;font-size:13px;text-decoration:none;font-weight:700;">
          <i class="fas fa-id-card"></i> Open Full Patient Profile
        </a>
      </div>`;
    } else {
      res.innerHTML = `<div style="background:#fff1f2;color:#9f1239;border-radius:10px;padding:12px;">
        <i class="fas fa-exclamation-circle"></i> ${data.message}
      </div>`;
    }
  } catch(e) {
    res.innerHTML = '<div style="color:red;">Connection error. Is the server running?</div>';
  }
}

// Allow pressing Enter in NFC input
document.getElementById('nfc-uid-input').addEventListener('keydown', e => { if (e.key === 'Enter') nfcLogin(); });
</script>
@stack('scripts')
</body>
</html>
