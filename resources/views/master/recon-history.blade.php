{{-- resources/views/master/recon-history.blade.php --}}
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
                <a class="nav-link" href="{{ route('master.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="fas fa-users me-2"></i>Manage Users
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link active" href="{{ route('master.recon-history') }}">
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
                        <span class="badge bg-primary ms-2">Master</span>
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
                                        <span class="badge bg-primary ms-2">Master</span>
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
                                <div class="col-sm-8"><span class="badge bg-primary">Master</span></div>
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

        <!-- Filter Bar Sederhana -->
<div class="container-fluid px-4 mt-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('master.recon-history') }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label mb-1">Processed By</label>
                    <select name="admin_id" class="form-select">
                        <option value="">All Admins</option>
                        @foreach($adminList as $admin)
                            <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label mb-1">Reconciliation Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-12 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    @if(request()->hasAny(['admin_id', 'date', 'status']))
                        <a href="{{ route('master.recon-history') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>


        <!-- History Content -->
        <div class="p-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Reconciliation History
                    </h5>
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
                                        <th>Result</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reconciliationHistories as $index => $history)
                                    <tr>
                                        <td>{{ $reconciliationHistories->firstItem() + $index }}</td>
                                        <td>
                                            <strong>{{ $history->reconciliation_date->format('d/m/Y') }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $history->user && $history->user->profile_photo ? asset('storage/profile_photos/' . $history->user->profile_photo) : asset('assets/images/profile-default.jpg') }}"
                                                     class="rounded-circle me-2" alt="User" style="width: 32px; height: 32px; object-fit: cover;">
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
                                                <small class="text-muted">{{ $history->created_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($history->excel_file_path && $history->pdf_file_path)
                                                <div class="btn-group btn-group-sm">
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
                                        <td>
                                            <form method="POST" action="{{ route('master.destroy-history', $history->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash-alt"></i> Delete
                                                </button>
                                            </form>
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
        <!-- End History Content -->
    </div>
</div>
@endsection
