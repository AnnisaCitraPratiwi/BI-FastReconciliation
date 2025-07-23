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
                <span class="navbar-brand mb-0 h1">Reconciliation Results</span>
                <small class="text-muted d-none">
                    Value Date: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
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
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Reconciliation Results</h4>
                            <p class="text-muted mb-0">Value Date: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
                        </div>
                        <a href="{{ route('reconciliation') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Reconciliation
                        </a>
                    </div>
                </div>
            </div>
            <div class="alert alert-{{ $summary['total_anomalies'] > 0 ? 'warning' : 'success' }} mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-{{ $summary['total_anomalies'] > 0 ? 'exclamation-triangle' : 'check-circle' }} fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">{{ number_format($summary['total_anomalies']) }} Anomali Ditemukan</h5>
                        <p class="mb-0">
                            Total Data: 
                            CIP: <strong>{{ number_format($summary['data_counts']['cip_total']) }}</strong> | 
                            AMS: <strong>{{ number_format($summary['data_counts']['ams_total']) }}</strong> | 
                            BS: <strong>{{ number_format($summary['data_counts']['bs_total']) }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            {{-- TABEL TUNGGAL UNTUK SEMUA ANOMALI --}}
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Anomali</h5>
                    <div class="d-flex align-items-center">
                        <input type="text" class="form-control form-control-sm me-2" id="anomaliSearch" placeholder="Cari di tabel..." style="width: 250px;">
                        <button class="btn btn-sm btn-success me-2" onclick="exportAnomalies()">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="exportAnomaliesPdf()">
                            <i class="fas fa-file-pdf me-1"></i> Export PDF
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 65vh;">
                        <table class="table table-striped table-hover table-bordered mb-0">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center">No</th>
                                    <th rowspan="2" class="align-middle text-center">End-to-End ID</th>
                                    <th rowspan="2" class="align-middle text-center">Reference Number</th>
                                    <th rowspan="2" class="align-middle text-center">Trx Time</th>
                                    <th rowspan="2" class="align-middle text-center">Trx Source</th>
                                    <th rowspan="2" class="align-middle text-center">Source Account</th>
                                    <th rowspan="2" class="align-middle text-center">Destination Account</th>
                                    <th rowspan="2" class="align-middle text-center">Amount</th>
                                    <th rowspan="2" class="align-middle text-center">Status Data</th>
                                    <th colspan="3" class="align-middle text-center">Status</th>
                                    <th rowspan="2" class="align-middle text-center">Keterangan</th>
                                </tr>
                                <tr>
                                    <th class="text-center">AMS</th>
                                    <th class="text-center">Debit</th>
                                    <th class="text-center">Credit</th>
                                </tr>
                            </thead>
                            @php
                                $cipSuccess = $cipDanger = 0;
                                $amsSuccess = $amsDanger = 0;
                                $bsSuccess  = $bsDanger  = 0;
                                $totalNominal = 0;
                            @endphp
                            <tbody id="anomaliTableBody">
                                @forelse($anomalies as $item)
                                    @php
                                        $cip = $item['cip'];
                                        $ams = $item['ams'];
                                        $bs = $item['bs'];
                                        if ($cip) $cipSuccess++; else $cipDanger++;
                                        if ($ams) $amsSuccess++; else $amsDanger++;
                                        if ($bs)  $bsSuccess++;  else $bsDanger++;
                                        $nominal = ($cip->debit ?? 0) + ($cip->kredit ?? 0);
                                        if (!$nominal) $nominal = $ams->trx_amount ?? 0;
                                        if (!$nominal) $nominal = $bs->nilai_transaksi ?? 0;
                                        $totalNominal += $nominal;
                                        $trxStatusClass    = ($ams && strtolower($ams->trx_status ?? '') === 'success')   ? 'bg-success' : 'bg-danger';
                                        $debitStatusClass  = ($ams && strtolower($ams->debit_status ?? '') === 'success') ? 'bg-success' : 'bg-danger';
                                        $creditStatusClass = ($ams && strtolower($ams->credit_status ?? '') === 'success')? 'bg-success' : 'bg-danger';
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td><small>{{ $cip?->end_to_end_id ?? $ams?->bifast_reference_number ?? '-' }}</small></td>
                                        <td><small>{{ $ams?->reference_number ?? $bs?->retrieval_ref_number ?? '-' }}</small></td>

                                        <td>
                                            @if($ams && $ams->trx_date_time)
                                                {{ \Carbon\Carbon::parse($ams->trx_date_time)->format('H:i:s') }}
                                            @elseif($cip && $cip->transaction_date)
                                                {{ \Carbon\Carbon::parse($cip->transaction_date)->format('H:i:s') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td><small>{{ $ams->trx_source ?? '-' }}</small></td>
                                        <td><small>{{ $ams->source_account_number ?? ($cip->rekening_pengirim ?? '-') }}</small></td>
                                        <td><small>{{ $ams->destination_account_number ?? ($cip->rekening_penerima ?? '-') }}</small></td>
                                        <td class="fw-bold text-end">
                                            {{ number_format($nominal, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="me-2" title="CIP">
                                                <strong>CIP</strong>
                                                {!! $cip ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}
                                            </span>
                                            <span class="me-2" title="AMS">
                                                <strong>AMS</strong>
                                                {!! $ams ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}
                                            </span>
                                            <span title="BS">
                                                <strong>BS</strong>
                                                {!! $bs ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>' !!}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($ams)
                                                <span class="badge {{ $trxStatusClass }}">{{ ucfirst($ams->trx_status ?? 'unknown') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($ams)
                                                <span class="badge {{ $debitStatusClass }}">{{ ucfirst($ams->debit_status ?? 'unknown') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($ams)
                                                <span class="badge {{ $creditStatusClass }}">{{ ucfirst($ams->credit_status ?? 'unknown') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cip && $ams && !$bs)
                                                <span class="badge bg-warning text-dark">Tidak Ditemukan di BS</span>
                                            @elseif($cip && !$ams && !$bs)
                                                <span class="badge bg-primary">Hanya Ditemukan di CIP</span>
                                            @elseif(!$cip && $ams && $bs)
                                                <span class="badge bg-info text-dark">Tidak Ditemukan di CIP</span>
                                            @elseif(!$cip && $ams && !$bs)
                                                <span class="badge bg-purple">Hanya Ditemukan di AMS</span>
                                            @elseif(!$cip && !$ams && $bs)
                                                <span class="badge bg-secondary">Hanya Ditemukan di BS</span>
                                            @elseif(isset($item['type']) && $item['type'] === 'AMOUNT_MISMATCH')
                                                <span class="badge bg-danger">Beda Nominal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-5">
                                            <div class="text-success">
                                                <i class="fas fa-shield-alt fa-4x mb-3"></i>
                                                <h4 class="fw-bold">Luar Biasa!</h4>
                                                <p class="text-muted mb-0">Tidak ada anomali yang perlu ditindaklanjuti untuk tanggal ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-warning fw-bold text-center">
                                    <td colspan="7" class="text-end">TOTAL</td>
                                    <td class="text-end">{{ number_format($totalNominal, 0, ',', '.') }}</td>
                                    <td>
                                        <div>
                                            <span class="badge bg-success">CIP Success: {{ $cipSuccess }}</span>
                                            <span class="badge bg-danger">CIP Danger: {{ $cipDanger }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-success">AMS Success: {{ $amsSuccess }}</span>
                                            <span class="badge bg-danger">AMS Danger: {{ $amsDanger }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-success">BS Success: {{ $bsSuccess }}</span>
                                            <span class="badge bg-danger">BS Danger: {{ $bsDanger }}</span>
                                        </div>
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('anomaliSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toUpperCase();
            const tableBody = document.getElementById('anomaliTableBody');
            const rows = tableBody.getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j] && cells[j].textContent.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? "" : "none";
            }
        });
    }
});

function exportAnomalies() {
    const date = '{{ $date }}';
    const dateOnly = date.split(' ')[0];
    window.location.href = `/reconciliation/export-all-anomalies?date=${dateOnly}`;
}

function exportAnomaliesPdf() {
    const date = '{{ $date }}';
    const dateOnly = date.split(' ')[0];
    window.location.href = `/reconciliation/export-all-anomalies-pdf?date=${dateOnly}`;
}
</script>
{{-- Tambahkan sedikit CSS kustom jika perlu --}}
<style>
.bg-purple {
    background-color: #6f42c1;
    color: white;
} 
</style>
@endsection
