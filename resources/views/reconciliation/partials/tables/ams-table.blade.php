<div class="card">
    <form method="GET" action="{{ route('reconciliation.view.data') }}" class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">AMS Data</h6>
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="tab" value="ams">
        <input type="text" name="search" id="searchInputAms" placeholder="Search..." class="form-control form-control-sm" style="width: 200px;" value="{{ request('search') }}">
    </form>

    <script>
        document.getElementById('searchInputAms').addEventListener('input', function () {
            this.form.submit();
        });
    </script>

    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>No</th>
                        <th>Reference Number</th>
                        <th>BI-Fast Reference</th>
                        <th>Amount</th>
                        <th>Trx Source</th>
                        <th>Source Account</th>
                        <th>Destination Account</th>
                        <th>Trx Status</th> <!-- new column -->
                        <th>Debit Status</th>
                        <th>Credit Status</th>
                        <th>Trx Datetime</th>
                    </tr>
                </thead>
                <tbody id="amsTableBody">
                    @forelse($amsData as $index => $data)
                        <tr>
                            <td>{{ $amsData->firstItem() + $index }}</td>
                            <td>{{ $data->reference_number }}</td>
                            <td>{{ $data->bifast_reference_number ?? '-' }}</td>
                            <td>{{ number_format($data->trx_amount, 2) }}</td>
                            <td>{{ $data->trx_source ?? '-' }}</td>
                            <td>{{ $data->source_account_number }}</td>
                            <td>{{ $data->destination_account_number }}</td>
                            <td>
                                <span class="badge bg-{{ strtolower($data->trx_status) === 'success' ? 'success' : 'danger' }}">
                                    {{ ucfirst($data->trx_status ?? 'unknown') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ strtolower($data->debit_status) === 'success' ? 'success' : 'danger' }}">
                                    {{ ucfirst($data->debit_status ?? 'unknown') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ strtolower($data->credit_status) === 'success' ? 'success' : 'danger' }}">
                                    {{ ucfirst($data->credit_status ?? 'unknown') }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($data->trx_date_time)->format('H:i:s') }}</td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No AMS data found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white">
        <div class="d-flex flex-column gap-2 text-center">
            <nav aria-label="Page navigation">
                {{ $amsData->appends(request()->query())->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
            </nav>
        </div>
    </div>
</div>
