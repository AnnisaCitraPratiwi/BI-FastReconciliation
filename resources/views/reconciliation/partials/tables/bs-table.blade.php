<div class="card">
    <form method="GET" action="{{ route('reconciliation.view.data') }}" class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">BS Data</h6>
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="tab" value="bs">
        <input type="text" name="search" id="searchInputBs" placeholder="Search..." class="form-control form-control-sm" style="width: 200px;" value="{{ request('search') }}">
    </form>
    <script>
    document.getElementById('searchInputBs').addEventListener('input', function() {
        this.form.submit();
    });
    </script>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>No</th>
                        <th>Retrieval Ref Number</th>
                        <th>Transaction Date</th>
                        <th>Transaction Value</th>
                    </tr>
                </thead>
                <tbody id="bsPartialTableBody">
                @forelse($bsData as $index => $data)
                <tr>
                    <td>{{ $bsData->firstItem() + $index }}</td>
                    <td>{{ $data->retrieval_ref_number }}</td>
                    <td>{{ $data->tgl_transaksi->format('d/m/Y') }}</td>
                    <td>{{ number_format($data->nilai_transaksi, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    No BS data found
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
                {{ $bsData->appends(request()->query())->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
            </nav>
        </div>
    </div>
</div> 