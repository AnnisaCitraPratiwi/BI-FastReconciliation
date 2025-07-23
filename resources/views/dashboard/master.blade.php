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
                <a class="nav-link active" href="{{ route('master.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="fas fa-users me-2"></i>Manage Users
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="{{ route('master.recon-history') }}">
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
                <span class="navbar-brand mb-0 h1">Dashboard</span>
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

        <!-- Dashboard Content -->
        <div class="p-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white" >
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4>{{ \App\Models\User::count() }}</h4>
                                                    <p class="mb-0">Total Users</p>
                                                </div>
                                                <i class="fas fa-users fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4>{{ \App\Models\User::where('role', 2)->count() }}</h4>
                                                    <p class="mb-0">Administrator Users</p>
                                                </div>
                                                <i class="fas fa-user-shield fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h4>{{ \App\Models\User::where('is_approved', 0)->count() }}</h4>
                                                    <p class="mb-0">Pending Approvals</p>
                                                </div>
                                                <i class="fas fa-clock fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Divider -->
        <div class="d-flex align-items-center my-2">
            <hr class="flex-grow-1 border-secondary-subtle">
            <span class="mx-3 text-muted fw-semibold" style="letter-spacing:0.5px;">Users Activities</span>
            <hr class="flex-grow-1 border-secondary-subtle">
        </div>

        <!-- Recent Login Activity -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4 shadow border-0">
                    <div class="card-header bg-info text-white d-flex align-items-center">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        <span>Recent Login Activity</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            @forelse($recentLogins as $login)
                                <li class="list-group-item d-flex align-items-center">
                                    <img src="{{ $login->profile_photo ? asset('storage/profile_photos/' . $login->profile_photo) : asset('assets/images/profile-default.jpg') }}"
                                        class="rounded-circle me-3" alt="User" style="width:38px; height:38px; object-fit:cover;">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold" style="font-size: 1.05rem;">
                                            {{ $login->name }}
                                        </div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($login->last_login_at)->diffForHumans() }}
                                            @if(!empty($login->ip_address))
                                                <span class="ms-2"><i class="fas fa-network-wired"></i> {{ $login->ip_address }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">No recent logins.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Cards -->
        <div class="row">
            <!-- Recent AMS Uploads -->
            <div class="col-md-4">
                <div class="card mb-4 shadow border-0">
                    <div class="card-header bg-success text-white d-flex align-items-center">
                        <i class="fas fa-file-excel me-2"></i>
                        <span>Recent AMS Uploads</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            @forelse($recentAmsUploads as $upload)
                                <li class="list-group-item d-flex align-items-center">
                                    <img src="{{ $upload->profile_photo ? asset('storage/profile_photos/' . $upload->profile_photo) : asset('assets/images/profile-default.jpg') }}"
                                        class="rounded-circle me-3" style="width:38px; height:38px; object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $upload->uploader_name }}</div>
                                        <div class="small text-muted">{{ $upload->filename }}</div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($upload->created_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">No recent AMS uploads.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Recent BS Uploads -->
            <div class="col-md-4">
                <div class="card mb-4 shadow border-0">
                    <div class="card-header bg-warning text-white d-flex align-items-center">
                        <i class="fas fa-file-excel me-2"></i>
                        <span>Recent BS Uploads</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            @forelse($recentBsUploads as $upload)
                                <li class="list-group-item d-flex align-items-center">
                                    <img src="{{ $upload->profile_photo ? asset('storage/profile_photos/' . $upload->profile_photo) : asset('assets/images/profile-default.jpg') }}"
                                        class="rounded-circle me-3" style="width:38px; height:38px; object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $upload->uploader_name }}</div>
                                        <div class="small text-muted">{{ $upload->filename }}</div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($upload->created_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">No recent BS uploads.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Recent CIP Uploads -->
            <div class="col-md-4">
                <div class="card mb-4 shadow border-0">
                    <div class="card-header bg-info text-white d-flex align-items-center">
                        <i class="fas fa-file-excel me-2"></i>
                        <span>Recent CIP Uploads</span>
                    </div>
                    <div class="card-body p-0" style="max-height: 320px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            @forelse($recentCipUploads as $upload)
                                <li class="list-group-item d-flex align-items-center">
                                    <img src="{{ $upload->profile_photo ? asset('storage/profile_photos/' . $upload->profile_photo) : asset('assets/images/profile-default.jpg') }}"
                                        class="rounded-circle me-3" style="width:38px; height:38px; object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $upload->uploader_name }}</div>
                                        <div class="small text-muted">{{ $upload->filename }}</div>
                                        <div class="small text-muted">
                                            <i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($upload->created_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">No recent CIP uploads.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Dashboard Content -->
    </div>
</div>

<!-- Tambahan style agar card recent activity lebih menarik -->
<style>
    .card .list-group-item {
        border: none;
        border-bottom: 1px solid #f1f1f1;
        transition: background 0.15s;
    }
    .card .list-group-item:last-child {
        border-bottom: none;
    }
    .card .list-group-item:hover {
        background: #f7fafc;
        cursor: pointer;
    }
    .card-header {
        font-size: 1.08rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
</style>
@endsection
