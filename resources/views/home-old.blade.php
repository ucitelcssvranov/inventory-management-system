@extends('layouts.app')

@push('styles')
<style>
.hover-shadow-lg {
    transition: all 0.3s ease-in-out;
}

.hover-shadow-lg:hover {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
    transform: translateY(-2px);
}

.card-header {
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-left: 4px solid #0dcaf0;
}

.list-unstyled li {
    padding: 2px 0;
}

.section-divider {
    border-top: 3px solid #dee2e6;
    margin: 3rem 0 2rem 0;
    position: relative;
}

.section-divider::after {
    content: '';
    position: absolute;
    top: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 4px;
    background: #0d6efd;
    border-radius: 2px;
}
</style>
@endpush

@section('content')
    <!-- Uvítanie a prehľad -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-info-circle-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="alert-heading mb-1">Systém inventarizácie CSŠ Vranov</h4>
                        <p class="mb-0">Komplexný systém na správu majetku, lokácií a inventarizačných procesov školy. Vyberte si sekciu podľa vašich potrieb.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hlavné sekcie systému -->
    <h2 class="mb-4"><i class="bi bi-grid-3x3-gap me-2"></i>Hlavné sekcie systému</h2>
    
    <!-- Základné moduly -->
    <div class="row mb-5">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow-lg">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-box-seam fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-0">Majetok</h5>
                            <small class="opacity-75">Správa inventáru školy</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Evidencia všetkého majetku školy s inventárnymi číslami, kategorizáciou a priradením k lokáciám.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="bi bi-check-circle text-success me-2"></i>Inventárne čísla</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Sériové čísla</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Správcovia majetku</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Stav a hodnotenie</li>
                    </ul>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Celkový počet položiek:</small>
                        <span class="badge bg-primary fs-6">{{ $stats['assets_count'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('assets.index') }}" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right me-1"></i> Spravovať majetok
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow-lg">
                <div class="card-header bg-success text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-0">Lokácie</h5>
                            <small class="opacity-75">Budovy a miestnosti</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Hierarchická štruktúra budov a miestností školy pre presné umiestnenie majetku.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="bi bi-check-circle text-success me-2"></i>Budovy školy</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Miestnosti a učebne</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Hierarchická štruktúra</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Priradenie majetku</li>
                    </ul>
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted d-block">Budovy</small>
                            <span class="badge bg-success fs-6">{{ $stats['buildings_count'] ?? 0 }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Miestnosti</small>
                            <span class="badge bg-success fs-6">{{ $stats['rooms_count'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('locations.index') }}" class="btn btn-success w-100">
                        <i class="bi bi-arrow-right me-1"></i> Spravovať lokácie
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow-lg">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-tags fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-0">Kategórie</h5>
                            <small class="opacity-75">Klasifikácia majetku</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Kategorizácia majetku podľa typu, účelu a charakteristík pre lepšiu organizáciu.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="bi bi-check-circle text-success me-2"></i>IKT technika</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Nábytok</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Učebné pomôcky</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Vlastné kategórie</li>
                    </ul>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Definované kategórie:</small>
                        <span class="badge bg-warning text-dark fs-6">{{ $stats['categories_count'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('categories.index') }}" class="btn btn-warning w-100">
                        <i class="bi bi-arrow-right me-1"></i> Spravovať kategórie
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventarizačné procesy -->
    <h3 class="mb-4"><i class="bi bi-clipboard-check me-2"></i>Inventarizačné procesy</h3>
    
    <div class="row mb-5">
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow-lg">
                <div class="card-header bg-info text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-journal-text fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-0">Inventúrne plány</h5>
                            <small class="opacity-75">Plánovanie a realizácia inventúr</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Tvorba, plánovanie a realizácia inventúr s exportom dokumentov a správ.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="bi bi-check-circle text-success me-2"></i>Inventúrne komisie</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Súpisy a zápisy</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>PDF/Excel export</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Rozdiely a chýbajúci majetok</li>
                    </ul>
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted d-block">Celkovo plánov</small>
                            <span class="badge bg-info fs-6">{{ $stats['inventory_plans_count'] ?? 0 }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Aktívnych</small>
                            <span class="badge bg-info fs-6">{{ $stats['active_inventory_plans'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('inventory_plans.index') }}" class="btn btn-info w-100">
                        <i class="bi bi-arrow-right me-1"></i> Spravovať inventúry
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow-lg">
                <div class="card-header bg-secondary text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-badge fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-0">Inventarizačné komisie</h5>
                            <small class="opacity-75">Správa inventarizačných tímov</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Organizácia inventarizačných komisií a priradenie učiteľov k jednotlivým tímom.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="bi bi-check-circle text-success me-2"></i>Inventarizačné komisie</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Priradenie učiteľov</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Zodpovednosti</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Organizačná štruktúra</li>
                    </ul>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Inventarizačné komisie:</small>
                        <span class="badge bg-secondary fs-6">{{ $stats['inventory_commission_count'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('inventory-commissions.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-arrow-right me-1"></i> Spravovať komisie
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Roly a správa -->
    @if(Auth::user()->role === 'spravca' || Auth::user()->role === 'ucitel')
    <h3 class="mb-4"><i class="bi bi-gear me-2"></i>Správa a špeciálne sekcie</h3>
    
    <div class="row mb-4">
        @if(Auth::user()->role === 'spravca')
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow-lg border-danger">
                <div class="card-header bg-danger text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-tools fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-0">Správa systému</h5>
                            <small class="opacity-75">Administrátorské funkcie</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Pokročilé nástroje pre správu celého systému, používateľov a nastavení.</p>
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Pozor:</strong> Sekcia len pre správcov systému.
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ url('/admin') }}" class="btn btn-danger w-100">
                        <i class="bi bi-arrow-right me-1"></i> Otvoriť správu
                    </a>
                </div>
            </div>
        </div>
        @endif

        @if(Auth::user()->role === 'ucitel')
        <div class="col-lg-6 col-md-6 mb-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow-lg border-info">
                <div class="card-header bg-info text-white">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-video3 fs-4 me-3"></i>
                        <div>
                            <h5 class="mb-0">Učiteľská sekcia</h5>
                            <small class="opacity-75">Nástroje pre pedagógov</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Špeciálne nástroje a funkcie určené pre učiteľov a pedagogický personál.</p>
                    <ul class="list-unstyled mb-3">
                        <li><i class="bi bi-check-circle text-success me-2"></i>Pedagogické nástroje</li>
                        <li><i class="bi bi-check-circle text-success me-2"></i>Správy pre učiteľov</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ url('/teacher') }}" class="btn btn-info w-100">
                        <i class="bi bi-arrow-right me-1"></i> Otvoriť sekciu
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Prehľad používateľa a rýchle akcie -->
    <div class="section-divider"></div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Informácie o účte</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-5">Meno:</dt>
                                <dd class="col-7"><strong>{{ Auth::user()->name }}</strong></dd>
                                <dt class="col-5">Email:</dt>
                                <dd class="col-7">{{ Auth::user()->email }}</dd>
                                <dt class="col-5">Rola:</dt>
                                <dd class="col-7">
                                    <span class="badge 
                                        @if(Auth::user()->role === 'spravca') bg-danger
                                        @elseif(Auth::user()->role === 'ucitel') bg-info
                                        @else bg-secondary
                                        @endif">
                                        {{ ucfirst(Auth::user()->role) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('profile') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-person-lines-fill me-1"></i> Upraviť profil
                                </a>
                                <a href="{{ route('inventory_reports.index') }}" class="btn btn-outline-info">
                                    <i class="bi bi-file-earmark-text me-1"></i> Správy z inventarizácie
                                </a>
                                <a href="{{ route('logout') }}" class="btn btn-outline-danger"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-1"></i> Odhlásiť sa
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Rýchly prehľad systému</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 text-center">
                            <div class="border rounded p-2">
                                <div class="fs-4 text-primary">{{ $stats['assets_count'] ?? 0 }}</div>
                                <small class="text-muted">Majetok</small>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border rounded p-2">
                                <div class="fs-4 text-success">{{ $stats['locations_count'] ?? 0 }}</div>
                                <small class="text-muted">Lokácie</small>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border rounded p-2">
                                <div class="fs-4 text-info">{{ $stats['inventory_plans_count'] ?? 0 }}</div>
                                <small class="text-muted">Inv. plány</small>
                            </div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border rounded p-2">
                                <div class="fs-4 text-secondary">{{ $stats['users_count'] ?? 0 }}</div>
                                <small class="text-muted">Používatelia</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer informácie -->
    <div class="mt-5 pt-4 border-top">
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    <i class="bi bi-building me-1"></i>
                    <strong>Inventarizačný systém CSŠ Vranov</strong><br>
                    Komplexná správa majetku a inventarizačných procesov
                </small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    Posledné prihlásenie: {{ Auth::user()->updated_at->format('d.m.Y H:i') }}
                </small>
            </div>
        </div>
    </div>
@endsection

    