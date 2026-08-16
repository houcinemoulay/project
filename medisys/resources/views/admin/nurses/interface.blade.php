@extends('layouts.app')

@section('page-title', 'Nurse Management Interface')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-nurse"></i> Nurse Management Interface</h1>
    <div class="header-actions">
        <button onclick="openAddNurseModal()" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Nurse
        </button>
        <button onclick="refreshNurseData()" class="btn btn-secondary">
            <i class="fas fa-sync"></i> Refresh
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user-nurse"></i></div>
        <div>
            <div class="stat-value" id="total-nurses-count">-</div>
            <div class="stat-label">Total Nurses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
        <div>
            <div class="stat-value" id="active-nurses-count">-</div>
            <div class="stat-label">Active Nurses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-user-clock"></i></div>
        <div>
            <div class="stat-value" id="new-nurses-count">-</div>
            <div class="stat-label">New This Month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-building"></i></div>
        <div>
            <div class="stat-value" id="departments-count">-</div>
            <div class="stat-label">Departments</div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-search"></i> Search & Filters</h3>
    </div>
    <div class="card-body">
        <div class="search-filters">
            <div class="filter-group">
                <input type="text" id="search-nurses" placeholder="Search by name, email, or department..." class="form-control">
            </div>
            <div class="filter-group">
                <select id="department-filter" class="form-control">
                    <option value="">All Departments</option>
                    <option value="Emergency">Emergency</option>
                    <option value="ICU">ICU</option>
                    <option value="Surgery">Surgery</option>
                    <option value="Pediatrics">Pediatrics</option>
                    <option value="Cardiology">Cardiology</option>
                    <option value="Neurology">Neurology</option>
                </select>
            </div>
            <div class="filter-group">
                <select id="status-filter" class="form-control">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="filter-group">
                <button onclick="applyFilters()" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <button onclick="clearFilters()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Nurses Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-list"></i> Nurses List</h3>
        <div class="table-actions">
            <button onclick="exportNurses()" class="btn btn-outline">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table" id="nurses-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>License</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="nurses-tbody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="fas fa-spinner fa-spin"></i> Loading nurses data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination-wrapper" id="pagination-wrapper">
            <!-- Pagination will be inserted here -->
        </div>
    </div>
</div>

<!-- Add/Edit Nurse Modal -->
<div class="modal" id="nurse-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Add New Nurse</h3>
            <button class="close-btn" onclick="closeNurseModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="nurse-form">
                <input type="hidden" id="nurse-id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nurse-name">Full Name *</label>
                        <input type="text" id="nurse-name" name="name" class="form-control" required>
                        <span class="error-text" id="name-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="nurse-email">Email *</label>
                        <input type="email" id="nurse-email" name="email" class="form-control" required>
                        <span class="error-text" id="email-error"></span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nurse-username">Username *</label>
                        <input type="text" id="nurse-username" name="username" class="form-control" required>
                        <span class="error-text" id="username-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="nurse-phone">Phone</label>
                        <input type="tel" id="nurse-phone" name="phone" class="form-control">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nurse-department">Department</label>
                        <select id="nurse-department" name="department" class="form-control">
                            <option value="">Select Department</option>
                            <option value="Emergency">Emergency</option>
                            <option value="ICU">ICU</option>
                            <option value="Surgery">Surgery</option>
                            <option value="Pediatrics">Pediatrics</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Neurology">Neurology</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="nurse-license">License Number</label>
                        <input type="text" id="nurse-license" name="license_number" class="form-control">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nurse-password">Password *</label>
                        <input type="password" id="nurse-password" name="password" class="form-control">
                        <span class="error-text" id="password-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="nurse-password-confirm">Confirm Password *</label>
                        <input type="password" id="nurse-password-confirm" name="password_confirmation" class="form-control">
                        <span class="error-text" id="password-confirm-error"></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="nurse-address">Address</label>
                    <textarea id="nurse-address" name="address" class="form-control" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeNurseModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="saveNurse()" class="btn btn-primary">
                <i class="fas fa-save"></i> <span id="save-btn-text">Add Nurse</span>
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="delete-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Confirm Delete</h3>
            <button class="close-btn" onclick="closeDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this nurse account?</p>
            <p><strong id="delete-nurse-name"></strong></p>
            <p style="color: #e74c3c;">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="confirmDelete()" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-icon.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
.stat-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-icon.orange { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
}

.stat-label {
    font-size: 14px;
    color: #64748b;
    margin-top: 4px;
}

.search-filters {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    gap: 10px;
    align-items: end;
}

.table-actions {
    display: flex;
    gap: 10px;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.pagination {
    display: flex;
    gap: 5px;
    list-style: none;
    padding: 0;
}

.pagination li {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    cursor: pointer;
}

.pagination li.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination li:hover:not(.active) {
    background: #f8fafc;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    margin: 50px auto;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #64748b;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.error-text {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 5px;
    display: none;
}

@media (max-width: 768px) {
    .search-filters {
        grid-template-columns: 1fr;
    }
    
    .filter-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-content {
        margin: 10px;
        max-width: none;
    }
}
</style>

<script>
let currentPage = 1;
let searchTerm = '';
let departmentFilter = '';
let statusFilter = '';
let deleteNurseId = null;

// Load initial data
document.addEventListener('DOMContentLoaded', function() {
    loadNurseData();
    loadStatistics();
});

// Load nurse data
async function loadNurseData(page = 1) {
    try {
        const params = new URLSearchParams({
            page: page,
            search: searchTerm,
            department: departmentFilter,
            status: statusFilter
        });
        
        const response = await fetch(`/api/admin/nurses?${params}`);
        const data = await response.json();
        
        if (data.success) {
            displayNurses(data.data);
            updatePagination(data.pagination);
        }
    } catch (error) {
        console.error('Error loading nurses:', error);
        document.getElementById('nurses-tbody').innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle"></i> Error loading nurse data
                </td>
            </tr>
        `;
    }
}

// Display nurses in table
function displayNurses(nurses) {
    const tbody = document.getElementById('nurses-tbody');
    
    if (nurses.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="fas fa-users"></i> No nurses found
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = nurses.map(nurse => `
        <tr>
            <td>${nurse.id}</td>
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                        ${nurse.name.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <div style="font-weight: 600;">${nurse.name}</div>
                        <div style="font-size: 12px; color: #64748b;">${nurse.code || 'NURSE' + nurse.id}</div>
                    </div>
                </div>
            </td>
            <td>${nurse.email}</td>
            <td>${nurse.phone || '-'}</td>
            <td>
                <span class="badge badge-info">${nurse.department || 'Not assigned'}</span>
            </td>
            <td>${nurse.license_number || '-'}</td>
            <td>
                <span class="badge badge-success">Active</span>
            </td>
            <td>
                <div class="action-buttons">
                    <button onclick="editNurse(${nurse.id})" class="btn btn-sm btn-outline" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteNurse(${nurse.id}, '${nurse.name}')" class="btn btn-sm btn-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Load statistics
async function loadStatistics() {
    try {
        const response = await fetch('/api/admin/nurses/statistics');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-nurses-count').textContent = data.total;
            document.getElementById('active-nurses-count').textContent = data.active;
            document.getElementById('new-nurses-count').textContent = data.new_this_month;
            document.getElementById('departments-count').textContent = data.departments;
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}

// Modal functions
function openAddNurseModal() {
    document.getElementById('modal-title').textContent = 'Add New Nurse';
    document.getElementById('save-btn-text').textContent = 'Add Nurse';
    document.getElementById('nurse-form').reset();
    document.getElementById('nurse-id').value = '';
    clearErrors();
    document.getElementById('nurse-modal').style.display = 'block';
}

function editNurse(id) {
    // Load nurse data and open modal for editing
    fetch(`/api/admin/nurses/${id}`)
        .then(response => {
            if (!response.ok) throw new Error(`Request failed with status ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Nurse could not be loaded');
            const nurse = data.data;
            document.getElementById('modal-title').textContent = 'Edit Nurse';
            document.getElementById('save-btn-text').textContent = 'Update Nurse';
            document.getElementById('nurse-id').value = nurse.id;
            document.getElementById('nurse-name').value = nurse.name;
            document.getElementById('nurse-email').value = nurse.email;
            document.getElementById('nurse-username').value = nurse.username;
            document.getElementById('nurse-phone').value = nurse.phone || '';
            document.getElementById('nurse-department').value = nurse.department || '';
            document.getElementById('nurse-license').value = nurse.license_number || '';
            document.getElementById('nurse-address').value = nurse.address || '';
            clearErrors();
            document.getElementById('nurse-modal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error loading nurse:', error);
            showNotification('Could not load the nurse details', 'error');
        });
}

function closeNurseModal() {
    document.getElementById('nurse-modal').style.display = 'none';
    clearErrors();
}

function closeDeleteModal() {
    document.getElementById('delete-modal').style.display = 'none';
    deleteNurseId = null;
}

// Save nurse
async function saveNurse() {
    clearErrors();
    
    const formData = new FormData(document.getElementById('nurse-form'));
    const nurseId = document.getElementById('nurse-id').value;
    const isEdit = nurseId !== '';
    
    // Validate required fields
    let hasError = false;
    const requiredFields = ['name', 'email', 'username'];
    
    if (!isEdit) {
        requiredFields.push('password');
    }
    
    for (const field of requiredFields) {
        const input = document.getElementById(`nurse-${field}`);
        if (!input.value.trim()) {
            document.getElementById(`${field}-error`).textContent = 'This field is required';
            document.getElementById(`${field}-error`).style.display = 'block';
            hasError = true;
        }
    }
    
    // Validate email
    const email = document.getElementById('nurse-email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
        document.getElementById('email-error').textContent = 'Please enter a valid email';
        document.getElementById('email-error').style.display = 'block';
        hasError = true;
    }
    
    // Validate password confirmation
    if (!isEdit) {
        const password = document.getElementById('nurse-password').value;
        const passwordConfirm = document.getElementById('nurse-password-confirm').value;
        
        if (password && password.length < 8) {
            document.getElementById('password-error').textContent = 'Password must be at least 8 characters';
            document.getElementById('password-error').style.display = 'block';
            hasError = true;
        }
        
        if (password !== passwordConfirm) {
            document.getElementById('password-confirm-error').textContent = 'Passwords do not match';
            document.getElementById('password-confirm-error').style.display = 'block';
            hasError = true;
        }
    }
    
    if (hasError) return;
    
    try {
        const url = isEdit ? `/api/admin/nurses/${nurseId}` : '/api/admin/nurses';
        const method = isEdit ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeNurseModal();
            loadNurseData(currentPage);
            loadStatistics();
            showNotification(isEdit ? 'Nurse updated successfully' : 'Nurse added successfully', 'success');
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorElement = document.getElementById(`${field}-error`);
                    if (errorElement) {
                        errorElement.textContent = data.errors[field][0];
                        errorElement.style.display = 'block';
                    }
                });
            }
            showNotification(data.message || 'Error saving nurse', 'error');
        }
    } catch (error) {
        console.error('Error saving nurse:', error);
        showNotification('Error saving nurse', 'error');
    }
}

// Delete functions
function deleteNurse(id, name) {
    deleteNurseId = id;
    document.getElementById('delete-nurse-name').textContent = name;
    document.getElementById('delete-modal').style.display = 'block';
}

async function confirmDelete() {
    if (!deleteNurseId) return;
    
    try {
        const response = await fetch(`/api/admin/nurses/${deleteNurseId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeDeleteModal();
            loadNurseData(currentPage);
            loadStatistics();
            showNotification('Nurse deleted successfully', 'success');
        } else {
            showNotification(data.message || 'Error deleting nurse', 'error');
        }
    } catch (error) {
        console.error('Error deleting nurse:', error);
        showNotification('Error deleting nurse', 'error');
    }
}

// Filter functions
function applyFilters() {
    searchTerm = document.getElementById('search-nurses').value;
    departmentFilter = document.getElementById('department-filter').value;
    statusFilter = document.getElementById('status-filter').value;
    currentPage = 1;
    loadNurseData(currentPage);
}

function clearFilters() {
    document.getElementById('search-nurses').value = '';
    document.getElementById('department-filter').value = '';
    document.getElementById('status-filter').value = '';
    searchTerm = '';
    departmentFilter = '';
    statusFilter = '';
    currentPage = 1;
    loadNurseData(currentPage);
}

// Utility functions
function refreshNurseData() {
    loadNurseData(currentPage);
    loadStatistics();
}

function clearErrors() {
    document.querySelectorAll('.error-text').forEach(element => {
        element.style.display = 'none';
        element.textContent = '';
    });
}

function showNotification(message, type) {
    // Simple notification - you can enhance this with a proper notification system
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 2000; padding: 15px; border-radius: 8px; color: white;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'}"></i> ${message}
    `;
    
    if (type === 'success') {
        notification.style.background = '#10b981';
    } else {
        notification.style.background = '#e74c3c';
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function updatePagination(pagination) {
    const wrapper = document.getElementById('pagination-wrapper');
    
    if (!pagination || pagination.last_page <= 1) {
        wrapper.innerHTML = '';
        return;
    }
    
    let html = '<ul class="pagination">';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<li onclick="loadNurseData(${pagination.current_page - 1})">Previous</li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        const active = i === pagination.current_page ? 'active' : '';
        html += `<li class="${active}" onclick="loadNurseData(${i})">${i}</li>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `<li onclick="loadNurseData(${pagination.current_page + 1})">Next</li>`;
    }
    
    html += '</ul>';
    wrapper.innerHTML = html;
}

function exportNurses() {
    // Export functionality - you can implement this with CSV or Excel export
    showNotification('Export feature coming soon', 'info');
}
</script>
@endsection
