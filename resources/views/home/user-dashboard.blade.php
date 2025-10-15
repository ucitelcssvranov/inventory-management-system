<!-- USER DASHBOARD -->
<!-- Moje úlohy a štatistiky -->
<div class="row mb-5">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stats-card stats-primary hover-shadow-lg">
            <div class="card-body text-center p-4">
                <div class="stats-icon bg-primary mx-auto">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="stats-number">{{ $stats['my_commissions_count'] ?? 0 }}</h3>
                <p class="stats-label">Moje komisie</p>
            </div>
            <div class="card-footer bg-transparent border-0 p-3">
                <a href="{{ route('inventory-commissions.index') }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-arrow-right me-1"></i>
                    Zobraziť komisie
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stats-card stats-warning hover-shadow-lg">
            <div class="card-body text-center p-4">
                <div class="stats-icon bg-warning mx-auto">
                    <i class="bi bi-clipboard-data"></i>
                </div>
                <h3 class="stats-number">{{ $stats['my_assigned_items'] ?? 0 }}</h3>
                <p class="stats-label">Pridelených položiek</p>
            </div>
            <div class="card-footer bg-transparent border-0 p-3">
                <a href="{{ route('inventory-tasks.index') }}" class="btn btn-warning btn-sm w-100 text-dark">
                    <i class="bi bi-arrow-right me-1"></i>
                    Moje úlohy
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card stats-card stats-success hover-shadow-lg">
            <div class="card-body text-center p-4">
                <div class="stats-icon bg-success mx-auto">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h3 class="stats-number">{{ $stats['my_completed_items'] ?? 0 }}</h3>
                <p class="stats-label">Dokončených</p>
            </div>
            <div class="card-footer bg-transparent border-0 p-3">
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: {{ $stats['my_assigned_items'] > 0 ? round(($stats['my_completed_items'] / $stats['my_assigned_items']) * 100, 1) : 0 }}%"
                         aria-valuenow="{{ $stats['my_assigned_items'] > 0 ? round(($stats['my_completed_items'] / $stats['my_assigned_items']) * 100, 1) : 0 }}" 
                         aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    {{ $stats['my_assigned_items'] > 0 ? round(($stats['my_completed_items'] / $stats['my_assigned_items']) * 100, 1) : 0 }}% dokončené
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Rýchle akcie pre používateľa -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="section-title">
            <i class="bi bi-list-task me-2"></i>
            Moje úlohy
        </h3>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <a href="{{ route('inventory-commissions.index') }}" class="action-card d-block">
            <div class="action-icon">
                <i class="bi bi-people"></i>
            </div>
            <h5 class="fw-bold mb-2">Moje komisie</h5>
            <p class="text-muted mb-0 small">Prehliadanie komisií, ktorých som členom</p>
        </a>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <a href="{{ route('inventory_plans.index') }}" class="action-card d-block">
            <div class="action-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <h5 class="fw-bold mb-2">Inventarizačné plány</h5>
            <p class="text-muted mb-0 small">Plány pridelené mojim komisiám</p>
        </a>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-4">
        <a href="{{ route('inventory_reports.index') }}" class="action-card d-block">
            <div class="action-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <h5 class="fw-bold mb-2">Moje správy</h5>
            <p class="text-muted mb-0 small">Správy z mojej inventarizačnej činnosti</p>
        </a>
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