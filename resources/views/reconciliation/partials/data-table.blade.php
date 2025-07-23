{{-- Tab Navigation --}}
<ul class="nav nav-tabs" id="data-table" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="cip-tab" data-bs-toggle="tab" data-bs-target="#cip" type="button" role="tab">
            <i class="fas fa-database me-2 text-primary"></i>
            <span class="text-primary fw semibold">CIP Data</span> 
            <span class="badge bg-primary ms-1">{{ $cipData->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ams-tab" data-bs-toggle="tab" data-bs-target="#ams" type="button" role="tab">
            <i class="fas fa-database me-2 text-success"></i>
            <span class="text-primary fw semibold">AMS Data</span> 
            <span class="badge bg-success ms-1">{{ $amsData->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="bs-tab" data-bs-toggle="tab" data-bs-target="#bs" type="button" role="tab">
            <i class="fas fa-database me-2 text-info"></i>
            <span class="text-primary fw semibold">BS Data</span>  
            <span class="badge bg-info ms-1">{{ $bsData->count() }}</span>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content mt-3" id="dataTabContent">

    {{-- CIP Data --}}
    <div class="tab-pane fade show active" id="cip" role="tabpanel">
        @include('reconciliation.partials.tables.cip', ['cipData' => $cipData])
    </div>

    {{-- AMS Data --}}
    <div class="tab-pane fade" id="ams" role="tabpanel">
        @include('reconciliation.partials.tables.ams', ['amsData' => $amsData])
    </div>

    {{-- BS Data --}}
    <div class="tab-pane fade" id="bs" role="tabpanel">
        @include('reconciliation.partials.tables.bs', ['bsData' => $bsData])
    </div>
</div>

<script>
// Re-activate tab events after dynamic HTML load (AJAX)
document.querySelectorAll('#dataTab button').forEach(function (triggerEl) {
    const tabTrigger = new bootstrap.Tab(triggerEl)
    triggerEl.addEventListener('click', function (event) {
        event.preventDefault()
        tabTrigger.show()
    })
})
</script>
