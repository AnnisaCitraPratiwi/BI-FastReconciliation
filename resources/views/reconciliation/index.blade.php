@extends('layouts.dashboard')
@push('head')
    <link rel="icon" href="{{ asset('assets/images/logo-bl1.ico') }}" type="image/x-icon">
@endpush
@section('title', 'BI Fast Reconciliation')

@section('content')
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar bg-light p-3" style="width: 250px;">
        <div class="mb-4">
            <img src="{{ asset('assets/images/logo-bifast.png') }}" alt="Bank Lampung" class="me-2" style="height: 60px;">
        </div>
        
        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link active" href="{{ route('reconciliation') }}">
                    <i class="fas fa-calculator me-2"></i>Reconciliation
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="{{ route('reconciliation.history') }}">
                    <i class="fas fa-history me-2"></i>History
                </a>
            </li> 
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="flex-grow-1">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4">
            <div class="d-flex flex-column">
                <span class="navbar-brand mb-0 h1">Reconciliation</span>
                <small class="text-muted">
                    Welcome, {{ Auth::user()->name }}!
                </small>
            </div>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                    <img src="{{ Auth::user()->profile_photo ? asset('storage/profile_photos/' . Auth::user()->profile_photo) : asset('assets/images/profile-default.jpg') }}"
                        class="rounded-circle me-2" alt="User" style="width: 32px; height: 32px; object-fit: cover;">
                        <span>{{ Auth::user()->name }}</span>
                        <span class="badge bg-info ms-2">Administrator</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 280px;">
                        <li class="dropdown-header">
                            <div class="d-flex align-items-center">
                            <img src="{{ Auth::user()->profile_photo ? asset('storage/profile_photos/' . Auth::user()->profile_photo) : asset('assets/images/profile-default.jpg') }}"
                                class="rounded-circle me-2" alt="User" style="width: 48px; height: 48px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold">{{ Auth::user()->name }}</div>
                                    <small class="text-muted">{{ Auth::user()->email }}</small>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <div class="dropdown-item-text">
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Role:</small><br>
                                        <span class="badge bg-info">Administrator</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Status:</small><br>
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Last Login:</small><br>
                                    <small>
                                        @if(Auth::user()->last_login_at)
                                            @php
                                                $lastLogin = \Carbon\Carbon::parse(Auth::user()->last_login_at)->setTimezone('Asia/Jakarta');
                                            @endphp
                                            {{ $lastLogin->diffForHumans() }}
                                            <br><small class="text-muted">({{ $lastLogin->format('d M Y, H:i') }})</small>
                                        @else
                                            First time login
                                        @endif
                                    </small>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Member Since:</small><br>
                                    <small>{{ Auth::user()->created_at->format('d M Y') }}</small>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#profileModal">
                                <i class="fas fa-user me-2"></i>View Profile
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Reconciliation Content -->
        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <h4 class="mb-4">Import Excel</h4>
      <!-- Separate Upload Forms for CIP, AMS, BS -->
      <div class="row mb-4">
    <!-- CIP Upload Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-file-excel me-2"></i>Upload CIP File
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('upload.cip') }}" enctype="multipart/form-data" id="cipForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select CIP Excel File</label>
                        
                        <!-- Drag & Drop Zone -->
                        <div class="drag-drop-zone" id="cipDropZone">
                            <div class="drag-drop-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Drag & drop file di sini atau</p>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('cipFileInput').click()">
                                    Pilih File
                                </button>
                            </div>
                            <input type="file" class="d-none" name="cip_file[]" id="cipFileInput" accept=".xlsx,.xls" multiple required>
                        </div>
                        
                        <!-- File Info -->
                        <div id="cipFileInfo" class="mt-2 d-none">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                <span id="cipFileName"></span>
                            </small>
                        </div>
                        
                        <small class="text-muted">
                            CIP data only - Business Message ID, Debit, Credit<br>
                            <small class="text-info">Transaction date akan diambil dari Transaction ID</small>
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-2"></i>Upload CIP
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- AMS Upload Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i class="fas fa-file-excel me-2"></i>Upload AMS File
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('upload.ams') }}" enctype="multipart/form-data" id="amsForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select AMS Excel File</label>
                        
                        <!-- Drag & Drop Zone -->
                        <div class="drag-drop-zone" id="amsDropZone">
                            <div class="drag-drop-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Drag & drop file di sini atau</p>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="document.getElementById('amsFileInput').click()">
                                    Pilih File
                                </button>
                            </div>
                            <input type="file" class="d-none" name="ams_file[]" id="amsFileInput" accept=".xlsx,.xls" multiple required>
                        </div>
                        
                        <!-- File Info -->
                        <div id="amsFileInfo" class="mt-2 d-none">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                <span id="amsFileName"></span>
                            </small>
                        </div>
                        
                        <small class="text-muted">
                            AMS data only<br>
                            <small class="text-info">Transaction date akan diambil dari filename</small>
                        </small>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-upload me-2"></i>Upload AMS
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- BS Upload Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-file-excel me-2"></i>Upload BS File
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('upload.bs') }}" enctype="multipart/form-data" id="bsForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select BS Excel File</label>
                        
                        <!-- Drag & Drop Zone -->
                        <div class="drag-drop-zone" id="bsDropZone">
                            <div class="drag-drop-content">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Drag & drop file di sini atau</p>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="document.getElementById('bsFileInput').click()">
                                    Pilih File
                                </button>
                            </div>
                            <input type="file" class="d-none" name="bs_file[]" id="bsFileInput" accept=".xlsx,.xls" multiple required>
                        </div>
                        
                        <!-- File Info -->
                        <div id="bsFileInfo" class="mt-2 d-none">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                <span id="bsFileName"></span>
                            </small>
                        </div>
                        
                        <small class="text-muted">
                            BS data only<br>
                            <small class="text-info">Transaction date akan diambil dari data tgl_transaksi</small>
                        </small>
                    </div>
                    <button type="submit" class="btn btn-info w-100">
                        <i class="fas fa-upload me-2"></i>Upload BS
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Date Selection dan Actions -->
<div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <label class="me-2">Value Date :</label>
                                        <select class="form-select me-2" id="reconciliationDate" style="width: 200px;">
                                            <option value="">Choose the Date</option>
                                            @foreach($filteredDates as $date)
                                                <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</option>
                                            @endforeach
                                        </select>
                                    </div> 

                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex">
                                        <button class="btn btn-success me-2" onclick="viewData()">View Data</button>
                                        <button class="btn btn-primary" onclick="processDetailedReconciliation()">Detailed Recon</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


<!-- Upload History (Simplified) -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Upload History
                </h6> 
            </div>
            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                @forelse($uploadHistories ?? [] as $history)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-{{ $history->file_type === 'cip' ? 'primary' : ($history->file_type === 'ams' ? 'success' : 'info') }} me-2">
                                    {{ strtoupper($history->file_type) }}
                                </span>
                                <h6 class="mb-0">{{ $history->filename }}</h6>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>{{ $history->user->name ?? 'Unknown' }}
                            </small>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block">{{ $history->created_at->format('d/m/Y H:i') }}</small>
                            <small class="text-success">{{ $history->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    <span>Belum ada file yang diupload</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats (Optional) -->
@if(isset($quickStats))
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-database fa-2x text-primary mb-2"></i>
                <h5 class="card-title">{{ $quickStats['cip_records'] ?? 0 }}</h5>
                <p class="card-text text-muted">CIP Records Today</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-database fa-2x text-success mb-2"></i>
                <h5 class="card-title">{{ $quickStats['ams_records'] ?? 0 }}</h5>
                <p class="card-text text-muted">AMS Records Today</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-database fa-2x text-info mb-2"></i>
                <h5 class="card-title">{{ $quickStats['bs_records'] ?? 0 }}</h5>
                <p class="card-text text-muted">BS Records Today</p>
            </div>
        </div>
    </div>
</div>
@endif
            <!-- Clear Data Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Data Management</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Clear All Data:</strong> Delete all reconciliation data</p>
                                    <button class="btn btn-danger" onclick="clearAllData()">
                                        <i class="fas fa-trash me-2"></i>Clear All Data
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Clear by Date:</strong> Delete data by date</p>
                                    <div class="d-flex">
                                        <input type="date" class="form-control me-2" id="clearDate" style="width: 200px;">
                                        <button class="btn btn-warning" onclick="clearDataByDate()">
                                            <i class="fas fa-calendar-times me-2"></i>Clear Date
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="profilePhotoForm" method="POST" action="{{ route('profile.update-photo') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">User Profile</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">  
                                <img id="profilePreview" src="{{ Auth::user()->profile_photo ? asset('storage/profile_photos/' . Auth::user()->profile_photo) : asset('assets/images/profile-default.jpg') }}" class="rounded-circle" alt="User" style="width: 100px; height: 100px; object-fit: cover;">
                                <div>
                                    <label class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-camera"></i> Change Photo
                                        <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/*" class="d-none">
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">Max: 2MB | JPG/PNG</small>
                            </div> 
                            <div class="row">
                                <div class="col-sm-4"><strong>Name:</strong></div>
                                <div class="col-sm-8">{{ Auth::user()->name }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Email:</strong></div>
                                <div class="col-sm-8">{{ Auth::user()->email }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Role:</strong></div>
                                <div class="col-sm-8"><span class="badge bg-info">Administrator</span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Status:</strong></div>
                                <div class="col-sm-8"><span class="badge bg-success">Active</span></div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong>Member Since:</strong></div>
                                <div class="col-sm-8">{{ Auth::user()->created_at->format('d F Y') }}</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Photo</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        document.getElementById('profilePhotoInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        </script>
<style>
.drag-drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    cursor: pointer;
}

.drag-drop-zone:hover {
    border-color: #007bff;
    background-color: #e3f2fd;
}

.drag-drop-zone.drag-over {
    border-color: #28a745;
    background-color: #d4edda;
    transform: scale(1.02);
}

.drag-drop-zone.has-file {
    border-color: #28a745;
    background-color: #d4edda;
}
</style>

<script>
function viewData() {
    const date = document.getElementById('reconciliationDate').value;
    if (!date) {
        alert('Silakan pilih tanggal terlebih dahulu.');
        return;
    }

    const form = document.createElement('form');
    form.method = 'GET'; // ✅ Ganti dari POST ke GET
    form.action = '{{ route("reconciliation.view.data") }}';

    const dateInput = document.createElement('input');
    dateInput.type = 'hidden';
    dateInput.name = 'date';
    dateInput.value = date;

    form.appendChild(dateInput);
    document.body.appendChild(form);
    form.submit();
}


function processDetailedReconciliation() {
    const date = document.getElementById('reconciliationDate').value;
    if (!date) {
        alert('Please select a date first.');
        return;
    }
    
    if (!confirm('Are you sure you want to process detailed reconciliation for ' + date + '?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reconciliation.process.detailed") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const dateInput = document.createElement('input');
    dateInput.type = 'hidden';
    dateInput.name = 'date';
    dateInput.value = date;
    
    form.appendChild(csrfToken);
    form.appendChild(dateInput);
    document.body.appendChild(form);
    form.submit();
}

function clearAllData() {
    if (!confirm('Are you sure you want to delete ALL reconciliation data?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reconciliation.clear.all") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    form.appendChild(csrfToken);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}

function clearDataByDate() {
    const date = document.getElementById('clearDate').value;
    if (!date) {
        alert('Please select a date first.');
        return;
    }
    
    if (!confirm(`Successfully deleted data for ${date}?`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reconciliation.clear.date") }}';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    const dateInput = document.createElement('input');
    dateInput.type = 'hidden';
    dateInput.name = 'date';
    dateInput.value = date;
    
    form.appendChild(csrfToken);
    form.appendChild(methodInput);
    form.appendChild(dateInput);
    document.body.appendChild(form);
    form.submit();
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDragDrop('cip', 'cipDropZone', 'cipFileInput', 'cipFileInfo', 'cipFileName');
    initializeDragDrop('ams', 'amsDropZone', 'amsFileInput', 'amsFileInfo', 'amsFileName');
    initializeDragDrop('bs', 'bsDropZone', 'bsFileInput', 'bsFileInfo', 'bsFileName');
});

function initializeDragDrop(type, dropZoneId, fileInputId, fileInfoId, fileNameId) {
    const dropZone = document.getElementById(dropZoneId);
    const fileInput = document.getElementById(fileInputId);
    const fileInfo = document.getElementById(fileInfoId);
    const fileName = document.getElementById(fileNameId);

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
    });

    dropZone.addEventListener('drop', handleDrop, false);
    dropZone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFileSelect);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            // Handle multiple files
            const validFiles = [];
            for (let i = 0; i < files.length; i++) {
                if (validateFile(files[i])) {
                    validFiles.push(files[i]);
                }
            }
            
            if (validFiles.length > 0) {
                const dt = new DataTransfer();
                validFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
                showMultipleFileInfo(validFiles);
            }
        }
    }

    function handleFileSelect(e) {
        const files = Array.from(e.target.files);
        if (files.length > 0) {
            const validFiles = files.filter(validateFile);
            if (validFiles.length > 0) {
                showMultipleFileInfo(validFiles);
            }
        }
    }

    function validateFile(file) {
        const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        const maxSize = 51200 * 1024; // 50MB

        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
            alert('File harus berformat Excel (.xlsx atau .xls): ' + file.name);
            return false;
        }

        if (file.size > maxSize) {
            alert('Ukuran file maksimal 50MB: ' + file.name);
            return false;
        }

        return true;
    }

    function showMultipleFileInfo(files) {
        const count = files.length;
        const fileNames = files.map(f => f.name).join(', ');
        
        fileName.textContent = `${count} file(s): ${fileNames}`;
        fileInfo.classList.remove('d-none');
        dropZone.classList.add('has-file');
        
        dropZone.querySelector('.drag-drop-content').innerHTML = `
            <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
            <p class="mb-2 text-success">${count} file(s) siap untuk diupload</p>
            <small class="text-muted">${count > 1 ? 'Multiple files selected' : files[0].name}</small>
        `;
    }
}
</script>

@endsection
