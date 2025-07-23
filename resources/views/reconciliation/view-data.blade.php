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
                <span class="navbar-brand mb-0 h1">View Data</span>
                <small class="text-muted d-none">
                    <!-- Value Date: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }} -->
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

        <!-- Content -->
        <div class="p-4">
            <!-- Header dengan Back Button -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">View Data</h4>
                            <small class="text-muted">Showing data for {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</small>
                        </div>
                        <a href="{{ route('reconciliation') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Reconciliation
                        </a>
                    </div>
                </div>
            </div>


            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-3" id="dataTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center {{ $activeTab === 'cip' ? 'active' : '' }}"
                    href="{{ route('reconciliation.view.data', ['tab' => 'cip', 'date' => $date]) }}">
                        <i class="fas fa-database me-2 text-primary"></i>
                        <span class="text-primary fw-semibold">CIP Data</span>
                        <span class="badge bg-primary ms-2">{{ $totalCip }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center {{ $activeTab === 'ams' ? 'active' : '' }}"
                    href="{{ route('reconciliation.view.data', ['tab' => 'ams', 'date' => $date]) }}">
                        <i class="fas fa-database me-2 text-success"></i>
                        <span class="text-primary fw-semibold">AMS Data</span>
                        <span class="badge bg-success ms-2">{{ $totalAms }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center {{ $activeTab === 'bs' ? 'active' : '' }}"
                    href="{{ route('reconciliation.view.data', ['tab' => 'bs', 'date' => $date]) }}">
                        <i class="fas fa-database me-2 text-info"></i>
                        <span class="text-primary fw-semibold">BS Data</span>
                        <span class="badge bg-info ms-2">{{ $totalBs }}</span>
                    </a>
                </li>
            </ul>


            <!-- Tab Content -->
            <div class="tab-content mt-3">
                @if ($activeTab === 'cip')
                    @include('reconciliation.partials.tables.cip-table', ['cipData' => $cipData])
                @elseif ($activeTab === 'ams')
                    @include('reconciliation.partials.tables.ams-table', ['amsData' => $amsData])
                @elseif ($activeTab === 'bs')
                    @include('reconciliation.partials.tables.bs-table', ['bsData' => $bsData])
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
