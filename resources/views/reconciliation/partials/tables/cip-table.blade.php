<div class="card">
    <form method="GET" action="{{ route('reconciliation.view.data') }}" class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">CIP Data</h6>
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="tab" value="cip">
        <input type="text" name="search" id="searchInputCip" placeholder="Search..." class="form-control form-control-sm" style="width: 200px;" value="{{ request('search') }}">
    </form>
    <script>
    document.getElementById('searchInputCip').addEventListener('input', function() {
        this.form.submit(); 
    });
    </script>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>No</th>
                        <th>End to End ID</th>
                        <th>Debit</th>
                        <th>Kredit</th>
                        <th>Date</th>
                    </tr>
                </thead>
                @php
                    use Carbon\Carbon;
                    $formattedDate = Carbon::parse($date)->format('d/m/Y');
                @endphp

                <tbody id="cipPartialTableBody">
                    @forelse ($cipData as $index => $item)
                        <tr>
                            <td>{{ $cipData->firstItem() + $index }}</td>
                            <td>{{ $item->end_to_end_id }}</td>
                            <td>{{ number_format($item->debit, 2) }}</td>
                            <td>{{ number_format($item->kredit, 2) }}</td>
                            <td>{{ $formattedDate }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No CIP data found
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
                {{ $cipData->appends(request()->query())->onEachSide(1)->links('vendor.pagination.bootstrap-5') }}
            </nav>
        </div>
    </div>
</div>

<script>
document.getElementById('cipSearchPartial').addEventListener('keyup', function () {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#cipPartialTableBody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>
