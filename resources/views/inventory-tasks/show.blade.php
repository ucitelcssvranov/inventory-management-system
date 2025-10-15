@extends('layouts.app')

@section('title', 'Inventarizácia: ' . $plan->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        
        <!-- Hlavička s informáciami o pláne -->
        <div class="card shadow-sm mb-4">
            <div class="card-header {{ $isChairman ? 'bg-primary text-white' : 'bg-light' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="card-title mb-1">
                            <i class="bi bi-list-task me-2"></i>
                            {{ $plan->name }}
                        </h1>
                        <p class="mb-0 {{ $isChairman ? 'text-white-50' : 'text-muted' }}">
                            Komisia: {{ $plan->commission->name }}
                            @if($isChairman)
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="bi bi-star-fill"></i> Predseda komisie
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-lg badge-status badge-{{ strtolower(str_replace('_', '-', $plan->status)) }}">
                            {{ $plan->status_label }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Základné informácie -->
                    <div class="col-md-8">
                        <div class="row">
                            @if($plan->location)
                            <div class="col-md-6 mb-3">
                                <strong>Lokácia:</strong><br>
                                <i class="bi bi-geo-alt text-muted me-1"></i>
                                {{ $plan->location->name }}
                            </div>
                            @endif
                            @if($plan->category)
                            <div class="col-md-6 mb-3">
                                <strong>Kategória:</strong><br>
                                <i class="bi bi-tags text-muted me-1"></i>
                                {{ $plan->category->name }}
                            </div>
                            @endif
                            @if($plan->responsiblePerson)
                            <div class="col-md-6 mb-3">
                                <strong>Zodpovedná osoba:</strong><br>
                                <i class="bi bi-person text-muted me-1"></i>
                                {{ $plan->responsiblePerson->name }}
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <strong>Termín:</strong><br>
                                @if($plan->date_start && $plan->date_end)
                                    <i class="bi bi-calendar-range text-muted me-1"></i>
                                    {{ $plan->date_start->format('d.m.Y') }} - {{ $plan->date_end->format('d.m.Y') }}
                                @elseif($plan->date_start)
                                    <i class="bi bi-calendar-event text-muted me-1"></i>
                                    Od {{ $plan->date_start->format('d.m.Y') }}
                                @else
                                    <span class="text-muted">Nešpecifikovaný</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Štatistiky -->
                    <div class="col-md-4">
                        <div class="stats-summary">
                            <h6 class="text-muted mb-3">Pokrok inventarizácie</h6>
                            
                            <div class="progress mb-3" style="height: 15px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar"
                                     style="width: {{ $stats['progress_percentage'] }}%"
                                     aria-valuenow="{{ $stats['progress_percentage'] }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ $stats['progress_percentage'] }}%
                                </div>
                            </div>

                            <div class="row text-center small">
                                <div class="col-4">
                                    <div class="text-muted">Celkom</div>
                                    <strong>{{ $stats['total_items'] }}</strong>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted">Dokončené</div>
                                    <strong class="text-success">{{ $stats['completed_items'] + $stats['verified_items'] }}</strong>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted">Zostáva</div>
                                    <strong class="text-warning">{{ $stats['total_items'] - $stats['completed_items'] - $stats['verified_items'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Návigation tabs -->
        <div class="card shadow-sm">
            <div class="card-header p-0">
                <ul class="nav nav-tabs card-header-tabs" id="inventoryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="to-do-tab" data-bs-toggle="tab" data-bs-target="#to-do" type="button" role="tab">
                            <i class="bi bi-list-ul me-1"></i>
                            Na inventarizáciu 
                            <span class="badge bg-warning rounded-pill ms-1">{{ $stats['assigned_items'] }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="in-progress-tab" data-bs-toggle="tab" data-bs-target="#in-progress" type="button" role="tab">
                            <i class="bi bi-clock me-1"></i>
                            Prebieha 
                            <span class="badge bg-info rounded-pill ms-1">{{ $stats['in_progress_items'] }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                            <i class="bi bi-check-circle me-1"></i>
                            Dokončené 
                            <span class="badge bg-success rounded-pill ms-1">{{ $stats['completed_items'] }}</span>
                        </button>
                    </li>
                    @if($isChairman)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="verified-tab" data-bs-toggle="tab" data-bs-target="#verified" type="button" role="tab">
                            <i class="bi bi-patch-check me-1"></i>
                            Overené 
                            <span class="badge bg-primary rounded-pill ms-1">{{ $stats['verified_items'] }}</span>
                        </button>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="inventoryTabsContent">
                    
                    <!-- Tab: Na inventarizáciu -->
                    <div class="tab-pane fade show active" id="to-do" role="tabpanel">
                        @if($itemsByStatus['assigned']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover inventory-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Asset</th>
                                            <th>Inventárne číslo</th>
                                            <th>Lokácia</th>
                                            <th>Kategória</th>
                                            <th>Očakávaný počet</th>
                                            <th>Akcie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemsByStatus['assigned'] as $item)
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <strong>{{ $item->asset->name }}</strong>
                                                @if($item->asset->description)
                                                    <br><small class="text-muted">{{ Str::limit($item->asset->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <code>{{ $item->asset->inventory_number }}</code>
                                            </td>
                                            <td>{{ $item->asset->location->name ?? '-' }}</td>
                                            <td>{{ $item->asset->category->name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $item->expected_qty }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-primary btn-sm start-item-btn" 
                                                        data-item-id="{{ $item->id }}">
                                                    <i class="bi bi-play-fill"></i> Začať
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-check-all display-3 text-success"></i>
                                <h5 class="mt-3">Všetky položky sú spracované!</h5>
                                <p class="text-muted">Nie sú žiadne nové položky na inventarizáciu.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Tab: Prebieha -->
                    <div class="tab-pane fade" id="in-progress" role="tabpanel">
                        @if($itemsByStatus['in_progress']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover inventory-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Asset</th>
                                            <th>Inventárne číslo</th>
                                            <th>Očakávaný počet</th>
                                            <th>Aktuálny počet</th>
                                            <th>Stav</th>
                                            <th>Poznámky</th>
                                            <th>Akcie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemsByStatus['in_progress'] as $item)
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <strong>{{ $item->asset->name }}</strong>
                                            </td>
                                            <td>
                                                <code>{{ $item->asset->inventory_number }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $item->expected_qty }}</span>
                                            </td>
                                            <td colspan="4">
                                                <!-- Formulár pre zadanie počtu -->
                                                <form class="count-form" data-item-id="{{ $item->id }}">
                                                    @csrf
                                                    <div class="row align-items-end">
                                                        <div class="col-md-2">
                                                            <input type="number" name="actual_qty" class="form-control form-control-sm" 
                                                                   placeholder="Počet" min="0" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <select name="condition" class="form-select form-select-sm">
                                                                <option value="good">Dobrý stav</option>
                                                                <option value="new">Nový</option>
                                                                <option value="fair">Použiteľný</option>
                                                                <option value="poor">Slabý stav</option>
                                                                <option value="damaged">Poškodené</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <input type="text" name="notes" class="form-control form-control-sm" 
                                                                   placeholder="Poznámky (voliteľné)">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <button type="submit" class="btn btn-success btn-sm me-1">
                                                                <i class="bi bi-check"></i> Dokončiť
                                                            </button>
                                                            <button type="button" class="btn btn-secondary btn-sm cancel-count-btn"
                                                                    data-item-id="{{ $item->id }}">
                                                                <i class="bi bi-x"></i> Zrušiť
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-clock-history display-3 text-info"></i>
                                <h5 class="mt-3">Žiadne položky v procese</h5>
                                <p class="text-muted">Momentálne sa neinventarizujú žiadne položky.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Tab: Dokončené -->
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                        @if($itemsByStatus['completed']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover inventory-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Asset</th>
                                            <th>Inventárne číslo</th>
                                            <th>Očakávaný / Skutočný</th>
                                            <th>Stav</th>
                                            <th>Poznámky</th>
                                            <th>Inventarizoval</th>
                                            @if($isChairman)
                                            <th>Akcie</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemsByStatus['completed'] as $item)
                                        @php $lastCount = $item->counts->last(); @endphp
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <strong>{{ $item->asset->name }}</strong>
                                            </td>
                                            <td>
                                                <code>{{ $item->asset->inventory_number }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $item->expected_qty }}</span>
                                                /
                                                <span class="badge {{ $lastCount && $lastCount->actual_qty == $item->expected_qty ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $lastCount->actual_qty ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($lastCount)
                                                    <span class="badge bg-{{ $lastCount->condition == 'good' ? 'success' : ($lastCount->condition == 'new' ? 'info' : ($lastCount->condition == 'damaged' || $lastCount->condition == 'poor' ? 'danger' : 'warning')) }}">
                                                        {{ ['new' => 'Nový', 'good' => 'Dobrý', 'fair' => 'Použiteľný', 'poor' => 'Slabý', 'damaged' => 'Poškodené'][$lastCount->condition] ?? $lastCount->condition }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lastCount->notes ?? '-' }}
                                            </td>
                                            <td>
                                                @if($lastCount && $lastCount->counter)
                                                    {{ $lastCount->counter->name }}
                                                    <br><small class="text-muted">{{ $lastCount->counted_at->format('d.m.Y H:i') }}</small>
                                                @endif
                                            </td>
                                            @if($isChairman)
                                            <td>
                                                <button class="btn btn-success btn-sm verify-item-btn" 
                                                        data-item-id="{{ $item->id }}">
                                                    <i class="bi bi-patch-check"></i> Overiť
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm reset-item-btn" 
                                                        data-item-id="{{ $item->id }}">
                                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                                </button>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox display-3 text-muted"></i>
                                <h5 class="mt-3">Žiadne dokončené položky</h5>
                                <p class="text-muted">Zatiaľ neboli dokončené žiadne inventarizácie.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Tab: Overené (iba pre predsedu) -->
                    @if($isChairman)
                    <div class="tab-pane fade" id="verified" role="tabpanel">
                        @if($itemsByStatus['verified']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover inventory-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Asset</th>
                                            <th>Inventárne číslo</th>
                                            <th>Očakávaný / Skutočný</th>
                                            <th>Stav</th>
                                            <th>Poznámky</th>
                                            <th>Inventarizoval</th>
                                            <th>Overil</th>
                                            <th>Akcie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($itemsByStatus['verified'] as $item)
                                        @php $lastCount = $item->counts->last(); @endphp
                                        <tr data-item-id="{{ $item->id }}">
                                            <td>
                                                <strong>{{ $item->asset->name }}</strong>
                                            </td>
                                            <td>
                                                <code>{{ $item->asset->inventory_number }}</code>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $item->expected_qty }}</span>
                                                /
                                                <span class="badge {{ $lastCount && $lastCount->actual_qty == $item->expected_qty ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $lastCount->actual_qty ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($lastCount)
                                                    <span class="badge bg-{{ $lastCount->condition == 'good' ? 'success' : ($lastCount->condition == 'new' ? 'info' : ($lastCount->condition == 'damaged' || $lastCount->condition == 'poor' ? 'danger' : 'warning')) }}">
                                                        {{ ['new' => 'Nový', 'good' => 'Dobrý', 'fair' => 'Použiteľný', 'poor' => 'Slabý', 'damaged' => 'Poškodené'][$lastCount->condition] ?? $lastCount->condition }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $lastCount->notes ?? '-' }}
                                            </td>
                                            <td>
                                                @if($lastCount && $lastCount->counter)
                                                    {{ $lastCount->counter->name }}
                                                    <br><small class="text-muted">{{ $lastCount->counted_at->format('d.m.Y H:i') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <i class="bi bi-check-circle text-success"></i>
                                                <br><small class="text-muted">{{ $item->verified_at ? $item->verified_at->format('d.m.Y H:i') : '-' }}</small>
                                            </td>
                                            <td>
                                                <button class="btn btn-outline-danger btn-sm reset-item-btn" 
                                                        data-item-id="{{ $item->id }}">
                                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-patch-check display-3 text-primary"></i>
                                <h5 class="mt-3">Žiadne overené položky</h5>
                                <p class="text-muted">Zatiaľ neboli overené žiadne inventarizácie.</p>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Navigácia -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('inventory-tasks.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Späť na úlohy
                    </a>
                    
                    @if($isChairman || auth()->user()->hasAdminPrivileges())
                    <div>
                        <a href="{{ route('inventory_plans.show', $plan) }}" class="btn btn-outline-primary me-2">
                            <i class="bi bi-eye"></i> Detail plánu
                        </a>
                        
                        @if($stats['total_items'] > 0 && ($stats['completed_items'] + $stats['verified_items']) == $stats['total_items'])
                        <button class="btn btn-success complete-plan-btn" data-plan-id="{{ $plan->id }}">
                            <i class="bi bi-check-circle"></i> Dokončiť plán
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-lg {
    font-size: 0.9rem;
    padding: 0.5em 0.75em;
}

.badge-status {
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
}

.badge-planned { background-color: #6c757d; }
.badge-approved { background-color: #17a2b8; }
.badge-assigned { background-color: #ffc107; color: #212529; }
.badge-in-progress { background-color: #007bff; }
.badge-completed { background-color: #28a745; }
.badge-signed { background-color: #6f42c1; }
.badge-archived { background-color: #495057; }

.stats-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid #e9ecef;
}

.inventory-table th {
    font-weight: 600;
    font-size: 0.875rem;
    border-bottom: 2px solid #dee2e6;
}

.inventory-table td {
    vertical-align: middle;
}

.count-form {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #e9ecef;
}

.nav-tabs .nav-link {
    color: #495057;
    border: none;
    border-bottom: 3px solid transparent;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background: none;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #007bff;
    background: rgba(0, 123, 255, 0.05);
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Helper function na zobrazenie alertov
    function showAlert(type, message) {
        $('.alert-dynamic').remove();
        
        const alertDiv = $(`
            <div class="alert alert-${type} alert-dismissible fade show alert-dynamic">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('.container').prepend(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Spustenie inventarizácie položky
    $('.start-item-btn').click(function() {
        const itemId = $(this).data('item-id');
        const button = $(this);
        
        button.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Spúšťa sa...');
        
        $.ajax({
            url: `/inventory-tasks/items/${itemId}/start`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('danger', response.message);
                    button.prop('disabled', false).html('<i class="bi bi-play-fill"></i> Začať');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Chyba pri spustení inventarizácie';
                showAlert('danger', message);
                button.prop('disabled', false).html('<i class="bi bi-play-fill"></i> Začať');
            }
        });
    });

    // Odoslanie formulára s počtom
    $('.count-form').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const itemId = form.data('item-id');
        const formData = new FormData(form[0]);
        
        form.find('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Ukladá sa...');
        
        $.ajax({
            url: `/inventory-tasks/items/${itemId}/count`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('danger', response.message);
                    form.find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-check"></i> Dokončiť');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Chyba pri zaznamenaní počtu';
                showAlert('danger', message);
                form.find('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-check"></i> Dokončiť');
            }
        });
    });

    // Overenie položky (iba predseda)
    $('.verify-item-btn').click(function() {
        const itemId = $(this).data('item-id');
        const button = $(this);
        
        if (!confirm('Ste si istí, že chcete overiť túto inventarizáciu?')) {
            return;
        }
        
        button.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Overuje sa...');
        
        $.ajax({
            url: `/inventory-tasks/items/${itemId}/verify`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('danger', response.message);
                    button.prop('disabled', false).html('<i class="bi bi-patch-check"></i> Overiť');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Chyba pri overovaní';
                showAlert('danger', message);
                button.prop('disabled', false).html('<i class="bi bi-patch-check"></i> Overiť');
            }
        });
    });

    // Reset položky (iba predseda)
    $('.reset-item-btn').click(function() {
        const itemId = $(this).data('item-id');
        const button = $(this);
        
        if (!confirm('Ste si istí, že chcete resetovať túto položku? Všetky zaznamenané počty budú vymazané.')) {
            return;
        }
        
        button.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Resetuje sa...');
        
        $.ajax({
            url: `/inventory-tasks/items/${itemId}/reset`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert('danger', response.message);
                    button.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Reset');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Chyba pri resetovaní';
                showAlert('danger', message);
                button.prop('disabled', false).html('<i class="bi bi-arrow-clockwise"></i> Reset');
            }
        });
    });

    // Zrušenie inventarizácie prebieha
    $('.cancel-count-btn').click(function() {
        location.reload();
    });
});
</script>
@endpush
@endsection