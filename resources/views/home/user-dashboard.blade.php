<!-- USER DASHBOARD -->
<!-- Moje úlohy a štatistiky -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm hover-shadow-lg bg-primary text-white">
            <div class="card-body text-center">
                <i class="bi bi-people display-4 mb-3"></i>
                <h3 class="mb-1">{{ $stats['my_commissions_count'] ?? 0 }}</h3>
                <small class="opacity-75">Moje komisie</small>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('inventory-commissions.index') }}" class="btn btn-light btn-sm w-100">
                    Zobraziť komisie
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm hover-shadow-lg bg-warning text-dark">
            <div class="card-body text-center">
                <i class="bi bi-clipboard-data display-4 mb-3"></i>
                <h3 class="mb-1">{{ $stats['my_assigned_items'] ?? 0 }}</h3>
                <small class="opacity-75">Pridelených položiek</small>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('inventory_plans.index') }}" class="btn btn-light btn-sm w-100">
                    Moje úlohy
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm hover-shadow-lg bg-info text-white">
            <div class="card-body text-center">
                <i class="bi bi-check-circle display-4 mb-3"></i>
                <h3 class="mb-1">{{ $stats['my_completed_items'] ?? 0 }}</h3>
                <small class="opacity-75">Dokončených</small>
            </div>
            <div class="card-footer bg-transparent border-0">
                <span class="badge bg-light text-info w-100">
                    {{ $stats['my_assigned_items'] > 0 ? round(($stats['my_completed_items'] / $stats['my_assigned_items']) * 100, 1) : 0 }}% hotové
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Moje aktívne komisie -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3"><i class="bi bi-person-check me-2"></i>Moje aktívne komisie</h4>
    </div>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if(isset($stats['my_recent_commissions']) && $stats['my_recent_commissions']->count() > 0)
                    <div class="row">
                        @foreach($stats['my_recent_commissions'] as $commission)
                            <div class="col-lg-4 mb-3">
                                <div class="card border border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $commission->name }}</h6>
                                        <p class="card-text small text-muted">
                                            {{ Str::limit($commission->description ?? 'Bez popisu', 50) }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                @if($commission->chairman_id == auth()->id())
                                                    <i class="bi bi-star-fill text-warning"></i> Predseda
                                                @else
                                                    <i class="bi bi-person"></i> Člen
                                                @endif
                                            </small>
                                            <a href="{{ route('inventory-commissions.show', $commission) }}" class="btn btn-primary btn-sm">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-exclamation-triangle text-muted display-4"></i>
                        <p class="text-muted mt-2">Momentálne nie sте členom žiadnej komisie.</p>
                        <p class="small text-muted">Kontaktujte administrátora pre pridanie do komisie.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Rýchle akcie pre používateľa -->
<div class="row">
    <div class="col-12">
        <h4 class="mb-3"><i class="bi bi-lightning me-2"></i>Rýchle akcie</h4>
    </div>
    @if($stats['my_assigned_items'] > 0)
        <div class="col-lg-6 mb-3">
            <a href="{{ route('inventory_plans.index') }}" 
               class="btn btn-outline-warning w-100 py-3">
                <i class="bi bi-clipboard-check me-2"></i>Moje pridelené úlohy
            </a>
        </div>
    @endif
    @if($stats['my_commissions_count'] > 0)
        <div class="col-lg-6 mb-3">
            <a href="{{ route('inventory-commissions.index') }}" 
               class="btn btn-outline-success w-100 py-3">
                <i class="bi bi-people me-2"></i>Moje komisie
            </a>
        </div>
    @endif
</div>