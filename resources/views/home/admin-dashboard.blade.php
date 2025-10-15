<!-- ADMIN DASHBOARD -->
<!-- Základné štatistiky -->
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card stats-primary hover-shadow-lg">
            <div class="card-body text-center p-4">
                <div class="stats-icon bg-primary mx-auto">
                    <i class="bi bi-archive"></i>
                </div>
                <h3 class="stats-number">{{ $stats['assets_count'] ?? 0 }}</h3>
                <p class="stats-label">Položiek majetku</p>
            </div>
            <div class="card-footer bg-transparent border-0 p-3">
                <a href="{{ route('assets.index') }}" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-arrow-right me-1"></i>
                    Spravovať majetok
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card stats-success hover-shadow-lg">
            <div class="card-body text-center p-4">
                <div class="stats-icon bg-success mx-auto">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <h3 class="stats-number">{{ $stats['locations_count'] ?? 0 }}</h3>
                <p class="stats-label">Lokácií</p>
            </div>
            <div class="card-footer bg-transparent border-0 p-3">
                <a href="{{ route('locations.index') }}" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-arrow-right me-1"></i>
                    Spravovať lokácie
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card stats-warning hover-shadow-lg">
            <div class="card-body text-center p-4">
                <div class="stats-icon bg-warning mx-auto">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="stats-number">{{ $stats['inventory_commission_count'] ?? 0 }}</h3>
                <p class="stats-label">Komisií</p>
            </div>
            <div class="card-footer bg-transparent border-0 p-3">
                <a href="{{ route('inventory-commissions.index') }}" class="btn btn-warning btn-sm w-100 text-dark">
                    <i class="bi bi-arrow-right me-1"></i>
                    Spravovať komisie
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card stats-card stats-info hover-shadow-lg">
            <div class="card-body text-center p-4">
                <div class="stats-icon bg-info mx-auto">
                    <i class="bi bi-clipboard-check"></i>
                </div>
                <h3 class="stats-number">{{ $stats['active_inventory_plans'] ?? 0 }}</h3>
                <p class="stats-label">Aktívnych plánov</p>
            </div>
            <div class="card-footer bg-transparent border-0 p-3">
                <a href="{{ route('inventory_plans.index') }}" class="btn btn-info btn-sm w-100">
                    <i class="bi bi-arrow-right me-1"></i>
                    Inventarizačné plány
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Rýchle akcie pre administrátora -->
<div class="row mb-5">
    <div class="col-12">
        <h3 class="section-title">
            <i class="bi bi-lightning me-2"></i>
            Rýchle akcie
        </h3>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('assets.create') }}" class="action-card d-block">
            <div class="action-icon">
                <i class="bi bi-plus-circle"></i>
            </div>
            <h5 class="fw-bold mb-2">Pridať majetok</h5>
            <p class="text-muted mb-0 small">Registrácia novej položky majetku do systému</p>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('inventory-commissions.create') }}" class="action-card d-block">
            <div class="action-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <h5 class="fw-bold mb-2">Vytvoriť komisiu</h5>
            <p class="text-muted mb-0 small">Založenie novej inventarizačnej komisie</p>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('inventory_plans.create') }}" class="action-card d-block">
            <div class="action-icon">
                <i class="bi bi-journal-plus"></i>
            </div>
            <h5 class="fw-bold mb-2">Nový plán</h5>
            <p class="text-muted mb-0 small">Vytvorenie inventarizačného plánu</p>
        </a>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <a href="{{ route('inventory_reports.index') }}" class="action-card d-block">
            <div class="action-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <h5 class="fw-bold mb-2">Správy</h5>
            <p class="text-muted mb-0 small">Prehliadanie a export správ</p>
        </a>
    </div>
</div>

<!-- Najnovší obsah -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="recent-items-card">
            <div class="recent-items-header">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    Najnovší majetok
                </h5>
            </div>
            <div class="recent-items-body">
                @if(isset($stats['recent_assets']) && $stats['recent_assets']->count() > 0)
                    @foreach($stats['recent_assets'] as $asset)
                        <div class="recent-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">{{ $asset->name }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-hash me-1"></i>{{ $asset->inventory_number }}
                                        @if($asset->location)
                                            <span class="ms-2">
                                                <i class="bi bi-geo-alt me-1"></i>{{ $asset->location->name }}
                                            </span>
                                        @endif
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">{{ $asset->created_at->format('d.m.Y') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="text-center mt-3">
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>
                            Zobraziť všetok majetok
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox display-4 text-muted"></i>
                        <p class="text-muted mt-2">Zatiaľ nie je evidovaný žiadny majetok</p>
                        <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus me-1"></i>
                            Pridať prvú položku
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="recent-items-card">
            <div class="recent-items-header">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-activity me-2 text-success"></i>
                    Posledná aktivita
                </h5>
            </div>
            <div class="recent-items-body">
                @if(isset($stats['recent_inventory_counts']) && $stats['recent_inventory_counts']->count() > 0)
                    @foreach($stats['recent_inventory_counts'] as $count)
                        <div class="recent-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">
                                        @if($count->asset)
                                            {{ $count->asset->name }}
                                        @else
                                            Neznámy majetok
                                        @endif
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i>
                                        @if($count->user)
                                            {{ $count->user->name }}
                                        @else
                                            Neznámy užívateľ
                                        @endif
                                        @if($count->status)
                                            <span class="ms-2 badge badge-sm 
                                                @if($count->status == 'found') bg-success
                                                @elseif($count->status == 'not_found') bg-danger
                                                @elseif($count->status == 'damaged') bg-warning
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst($count->status) }}
                                            </span>
                                        @endif
                                    </small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">{{ $count->created_at->format('d.m.Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="text-center mt-3">
                        <a href="{{ route('inventory_reports.index') }}" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-arrow-right me-1"></i>
                            Zobraziť všetky správy
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard-data display-4 text-muted"></i>
                        <p class="text-muted mt-2">Zatiaľ nebola zaznamenaná žiadna inventarizačná aktivita</p>
                        <a href="{{ route('inventory_plans.create') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-plus me-1"></i>
                            Spustiť inventarizáciu
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent items section -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-archive me-2"></i>Najnovší majetok</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['recent_assets']) && $stats['recent_assets']->count() > 0)
                    @foreach($stats['recent_assets'] as $asset)
                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                            <div class="flex-grow-1">
                                <strong>{{ $asset->inventory_number }}</strong><br>
                                <small class="text-muted">{{ Str::limit($asset->name, 40) }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">{{ $asset->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">Žiadny najnovší majetok</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Najnovšie plány</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['recent_inventory_plans']) && $stats['recent_inventory_plans']->count() > 0)
                    @foreach($stats['recent_inventory_plans'] as $plan)
                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                            <div class="flex-grow-1">
                                <strong>{{ $plan->name }}</strong><br>
                                <small class="text-muted">
                                    <span class="badge bg-{{ $plan->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ $plan->status }}
                                    </span>
                                </small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">{{ $plan->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">Žiadne najnovšie plány</p>
                @endif
            </div>
        </div>
    </div>
</div>