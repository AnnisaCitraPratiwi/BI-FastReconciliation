{{-- resources/views/reconciliation/history.blade.php --}}
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
                <a class="nav-link" href="{{ route('reconciliation') }}">
                    <i class="fas fa-calculator me-2"></i>Reconciliation
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link active" href="{{ route('reconciliation.history') }}">
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
                <span class="navbar-brand mb-0 h1">Reconciliation History</span>
                <small class="text-muted">View all reconciliation results</small>
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


        <!-- History Content -->
        <div class="p-4">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Reconciliation History
                    </h5>
                </div>
                <div style="max-width:320px;" class="ms-auto">
                    <form class="d-flex align-items-center gap-2">
                        <label for="dateTableSearch" class="form-label mb-0 me-2 fw-semibold">
                            <!-- <i class="fas fa-calendar-alt text-white me-1"></i> -->
                        </label>
                        <input
                            type="date"
                            class="form-control form-control-sm"
                            id="dateTableSearch"
                            style="background-color:#f8f9fa; min-width:150px;"
                        >
                    </form>
                </div>
            </div>

                <div class="card-body p-0">
                    @if($reconciliationHistories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Reconciliation Date</th>
                                        <th>Processed By</th>
                                        <th>Total Anomalies</th>
                                        <th>Status</th>
                                        <th>Processed Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    @foreach($reconciliationHistories as $index => $history)
                                    <tr>
                                        <td>{{ $reconciliationHistories->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $history->reconciliation_date->format('d/m/Y') }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle me-2"></i>
                                                <span>{{ $history->user->name ?? 'Administrator' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ number_format($history->total_anomalies) }}
                                            </span>
                                        </td> 
                                        <td>

                                            @if($history->excel_file_path && $history->pdf_file_path)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-danger">Failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                {{ $history->created_at->format('d/m/Y') }}<br>
                                                <small class="text-muted">{{ $history->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($history->excel_file_path && $history->pdf_file_path)
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('reconciliation.history.view', $history->id) }}" class="btn btn-outline-info" title="View Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('reconciliation.history.download', ['id' => $history->id, 'type' => 'excel']) }}" 
                                                       class="btn btn-outline-success" title="Download Excel">
                                                        <i class="fas fa-file-excel"></i>
                                                    </a>
                                                    <a href="{{ route('reconciliation.history.download', ['id' => $history->id, 'type' => 'pdf']) }}" 
                                                       class="btn btn-outline-danger" title="Download PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($reconciliationHistories->hasPages())
                        <div class="d-flex justify-content-center p-3">
                            {{ $reconciliationHistories->links() }}
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Reconciliation History Found</h5>
                            <p class="text-muted">Start your first reconciliation to see the history here.</p>
                            <a href="{{ route('reconciliation') }}" class="btn btn-primary">
                                <i class="fas fa-calculator me-2"></i>Start Reconciliation
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('dateTableSearch');
    if (!dateInput) return;
    dateInput.addEventListener('change', function() {
        const filter = this.value;
        const tableBody = document.getElementById('historyTableBody');
        const rows = tableBody ? tableBody.getElementsByTagName('tr') : [];
        for (let i = 0; i < rows.length; i++) {
            // Ambil kolom "Reconciliation Date" (td kedua)
            let cells = rows[i].getElementsByTagName('td');
            if (cells && cells[1]) {
                // Ubah ke format yyyy-mm-dd agar mudah dibandingkan
                let dateText = cells[1].innerText.trim();
                let parts = dateText.split('/');
                let formatted = parts.length === 3 ? `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}` : '';
                rows[i].style.display = (!filter || formatted === filter) ? '' : 'none';
            }
        }
    });
});
</script>

@endsection
