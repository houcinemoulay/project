@extends('layouts.app')
@section('page-title', 'Nurse Dashboard')

@section('content')
<!-- Alerts Section -->
<div id="nurse-alerts-section" style="margin-bottom:20px;">
  <div id="nurse-alerts-content"></div>
</div>

<div class="stats-grid" id="stats-grid">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-user-injured"></i></div>
    <div><div class="stat-value">{{ $totalPatients }}</div><div class="stat-label">Total Patients</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-heartbeat"></i></div>
    <div><div class="stat-value">{{ $recentVitals->count() }}</div><div class="stat-label">Vitals Today</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-notes-medical"></i></div>
    <div><div class="stat-value">{{ $recentNotes->count() }}</div><div class="stat-label">Notes Today</div></div>
  </div>
</div>

<div class="dashboard-grid">
  <!-- Patients Section -->
  <div class="dashboard-card">
    <div class="card-header">
      <h3><i class="fas fa-users"></i> Recent Patients</h3>
      <a href="{{ route('nurse.patients') }}" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-content">
      <div class="quick-actions">
        <a href="{{ route('nurse.patients') }}" class="action-btn">
          <i class="fas fa-list"></i>
          <span>List Patients</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Recent Vitals Section -->
  <div class="dashboard-card">
    <div class="card-header">
      <h3><i class="fas fa-heartbeat"></i> Recent Vitals</h3>
    </div>
    <div class="card-content">
      @if($recentVitals->count() > 0)
        <div class="recent-items">
          @foreach($recentVitals as $vital)
            <div class="recent-item">
              <div class="item-info">
                <strong>{{ $vital->patient->name ?? 'Unknown Patient' }}</strong>
                <span class="item-meta">BP: {{ $vital->blood_pressure_systolic }}/{{ $vital->blood_pressure_diastolic }} | HR: {{ $vital->heart_rate }}</span>
              </div>
              <div class="item-time">{{ $vital->created_at->diffForHumans() }}</div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <i class="fas fa-heartbeat"></i>
          <p>No vitals recorded today</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Recent Notes Section -->
  <div class="dashboard-card">
    <div class="card-header">
      <h3><i class="fas fa-notes-medical"></i> Recent Notes</h3>
    </div>
    <div class="card-content">
      @if($recentNotes->count() > 0)
        <div class="recent-items">
          @foreach($recentNotes as $note)
            <div class="recent-item">
              <div class="item-info">
                <strong>{{ $note->patient->name ?? 'Unknown Patient' }}</strong>
                <span class="item-meta">{{ Str::limit($note->note, 50) }}</span>
              </div>
              <div class="item-time">{{ $note->created_at->diffForHumans() }}</div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-state">
          <i class="fas fa-notes-medical"></i>
          <p>No nurse notes today</p>
        </div>
      @endif
    </div>
  </div>
</div>

<style>
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.dashboard-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.card-header h3 {
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.card-content {
  padding: 20px;
}

.quick-actions {
  display: flex;
  gap: 10px;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  text-decoration: none;
  color: #374151;
  transition: all 0.3s ease;
  flex: 1;
}

.action-btn:hover {
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
}

.action-btn i {
  font-size: 24px;
  margin-bottom: 8px;
}

.recent-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.recent-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border-bottom: 1px solid #f3f4f6;
}

.recent-item:last-child {
  border-bottom: none;
}

.item-info {
  flex: 1;
}

.item-info strong {
  display: block;
  color: #374151;
}

.item-meta {
  font-size: 12px;
  color: #6b7280;
}

.item-time {
  font-size: 12px;
  color: #9ca3af;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 10px;
  opacity: 0.5;
}

/* Alert Styles */
.alert-banner {
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 12px;
  animation: slideIn 0.3s ease;
}
.alert-banner.critical {
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border-left: 4px solid #ef4444;
}
.alert-banner.warning {
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  border-left: 4px solid #f59e0b;
}
.alert-banner .alert-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.alert-banner.critical .alert-icon { background: #fecaca; color: #dc2626; }
.alert-banner.warning .alert-icon { background: #fde68a; color: #d97706; }
.alert-banner .alert-content { flex: 1; }
.alert-banner .alert-title {
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 2px;
}
.alert-banner.critical .alert-title { color: #991b1b; }
.alert-banner.warning .alert-title { color: #92400e; }
.alert-banner .alert-text {
  font-size: 13px;
  color: #6b7280;
}
.alert-banner .alert-time {
  font-size: 11px;
  color: #9ca3af;
}
.alert-banner .alert-dismiss {
  background: none;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  font-size: 16px;
  padding: 4px;
}
.alert-banner .alert-dismiss:hover { color: #374151; }

.alerts-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}
.alerts-header h3 {
  margin: 0;
  font-size: 16px;
  color: #374151;
  display: flex;
  align-items: center;
  gap: 8px;
}
.alerts-badge {
  background: #ef4444;
  color: white;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 700;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
(async function loadAlerts() {
  const container = document.getElementById('nurse-alerts-content');
  try {
    const r = await fetch('/api/alerts/recent', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    if (!r.ok) throw new Error(`Request failed with status ${r.status}`);
    const data = await r.json();
    if (data.alerts && data.alerts.length > 0) {
      let html = '<div class="alerts-header"><h3><i class="fas fa-bell" style="color:#ef4444;"></i> Patient Alerts</h3><span class="alerts-badge">' + data.unread_count + ' new</span></div>';
      data.alerts.forEach(alert => {
        const severity = alert.severity;
        html += '<div class="alert-banner ' + severity + '" data-alert-id="' + alert.id + '">';
        html += '<div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>';
        html += '<div class="alert-content">';
        html += '<div class="alert-title">' + alert.type_label + ' — ' + alert.patient_name + '</div>';
        html += '<div class="alert-text">' + alert.message + '</div>';
        html += '</div>';
        html += '<div class="alert-time">' + alert.created_at + '</div>';
        html += '<button class="alert-dismiss" onclick="dismissAlert(' + alert.id + ', this)"><i class="fas fa-times"></i></button>';
        html += '</div>';
      });
      container.innerHTML = html;
      document.getElementById('nurse-alerts-section').style.display = 'block';
    } else {
      document.getElementById('nurse-alerts-section').style.display = 'none';
    }
  } catch(e) {
    console.error('Error loading alerts:', e);
    const section = document.getElementById('nurse-alerts-section');
    if (container && section) {
      container.innerHTML = '<div style="padding:14px;color:#ef4444;font-size:13px;"><i class="fas fa-exclamation-triangle"></i> Could not load patient alerts</div>';
      section.style.display = 'block';
    }
  }
})();

async function dismissAlert(id, btn) {
  try {
    const r = await fetch('/api/alerts/' + id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' } });
    if (!r.ok) throw new Error(`Request failed with status ${r.status}`);
    const banner = btn.closest('.alert-banner');
    banner.style.opacity = '0';
    banner.style.transform = 'translateX(20px)';
    setTimeout(() => banner.remove(), 300);
  } catch(e) {
    console.error('Error dismissing alert:', e);
    alert('Could not dismiss the alert. Please try again.');
  }
}
</script>
@endsection
