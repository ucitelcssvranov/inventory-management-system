<!-- ADMIN DASHBOARD -->
<!-- Základné štatistiky -->
<div class="row mb-5">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm hover-shadow-lg bg-primary text-white">
            <div class="card-body text-center">
                <i class="bi bi-archive display-4 mb-3"></i>
                <h3 class="mb-1">{{ $stats['assets_count'] ?? 0 }}</h3>
                <small class="opacity-75">Položiek majetku</small>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('assets.index') }}" class="btn btn-light btn-sm w-100">
                    Spravovať majetok
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm hover-shadow-lg bg-success text-white">
            <div class="card-body text-center">
                <i class="bi bi-geo-alt display-4 mb-3"></i>
                <h3 class="mb-1">{{ $stats['locations_count'] ?? 0 }}</h3>
                <small class="opacity-75">Lokácií</small>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('locations.index') }}" class="btn btn-light btn-sm w-100">
                    Spravovať lokácie
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm hover-shadow-lg bg-warning text-dark">
            <div class="card-body text-center">
                <i class="bi bi-people display-4 mb-3"></i>
                <h3 class="mb-1">{{ $stats['inventory_commission_count'] ?? 0 }}</h3>
                <small class="opacity-75">Komisií</small>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('inventory-commissions.index') }}" class="btn btn-dark btn-sm w-100">
                    Spravovať komisie
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm hover-shadow-lg bg-info text-white">
            <div class="card-body text-center">
                <i class="bi bi-clipboard-check display-4 mb-3"></i>
                <h3 class="mb-1">{{ $stats['active_inventory_plans'] ?? 0 }}</h3>
                <small class="opacity-75">Aktívnych plánov</small>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('inventory_plans.index') }}" class="btn btn-light btn-sm w-100">
                    Inventarizačné plány
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Rýchle akcie pre administrátora -->
<div class="row mb-4">
    <div class="col-12">
        <h4 class="mb-3"><i class="bi bi-lightning me-2"></i>Rýchle akcie</h4>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('assets.create') }}" class="btn btn-outline-primary w-100 py-3">
            <i class="bi bi-plus-circle me-2"></i>Pridať majetok
        </a>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('inventory-commissions.create') }}" class="btn btn-outline-success w-100 py-3">
            <i class="bi bi-plus-circle me-2"></i>Vytvoriť komisiu
        </a>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('inventory_plans.create') }}" class="btn btn-outline-warning w-100 py-3">
            <i class="bi bi-plus-circle me-2"></i>Nový plán
        </a>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <a href="{{ route('inventory-commissions.index') }}" class="btn btn-outline-info w-100 py-3">
            <i class="bi bi-people me-2"></i>Správa komisií
        </a>
    </div>
</div>

<!-- Najnovší obsah -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Najnovší majetok</h5>
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