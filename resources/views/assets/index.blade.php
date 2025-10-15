@extends('layouts.app')

@push('styles')
<style>
.filter-card {
    background: linear-gradient(135deg, var(--edu-white) 0%, var(--edu-light) 100%);
    border-radius: 0.75rem;
    box-shadow: var(--edu-shadow);
    margin-bottom: 2rem;
}

.asset-group-card {
    border-left: 4px solid var(--edu-primary);
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
}

.asset-group-card:hover {
    box-shadow: var(--edu-shadow-lg);
    transform: translateX(2px);
}

.asset-item {
    border-bottom: 1px solid var(--edu-border);
    padding: 1rem;
    transition: all 0.2s ease;
}

.asset-item:last-child {
    border-bottom: none;
}

.asset-item:hover {
    background: var(--edu-light);
}

.stats-summary {
    background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
    color: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.page-header {
    background: linear-gradient(135deg, var(--edu-white) 0%, var(--edu-light) 100%);
    border-radius: 0.75rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--edu-shadow);
}

.badge-professional {
    background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-weight: 500;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active { background: #dcfce7; color: #16a34a; }
.status-written-off { background: #fee2e2; color: #dc2626; }
.status-in-repair { background: #fef3c7; color: #d97706; }
.status-lost { background: #f3f4f6; color: #6b7280; }
</style>
@endpush

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2">
                    <i class="bi bi-archive me-3"></i>
                    Správa majetku školy
                </h1>
                <p class="text-muted mb-0">Centrálna evidencia a správa školského majetku</p>
            </div>
            <div>
                <a href="{{ route('assets.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>
                    Pridať majetok
                </a>
            </div>
        </div>
    </div>

    <!-- Global Selection Control -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="select-all-assets" onchange="toggleAllAssets(this)">
            <label class="form-check-label" for="select-all-assets">
                <strong>Vybrať všetky assety na stránke</strong>
            </label>
        </div>
    </div>

    <!-- Bulk Operations Toolbar -->
    <div class="bulk-operations-toolbar" id="bulk-toolbar" style="display: none;">
        <div class="d-flex justify-content-between align-items-center bg-primary text-white p-3 rounded mb-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-square me-2"></i>
                <span id="selected-count">0</span> položiek vybratých globálne
            </div>
            <div class="dropdown">
                <button type="button" class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="bulkOperationsDropdown">
                    <i class="bi bi-gear me-1"></i>Hromadné operácie
                </button>
                <ul class="dropdown-menu" aria-labelledby="bulkOperationsDropdown">
                    <li><a class="dropdown-item" href="#" onclick="showBulkOperationModal('change_status'); return false;">
                        <i class="bi bi-toggle-on me-2"></i>Zmeniť stav
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="showBulkOperationModal('change_location'); return false;">
                        <i class="bi bi-geo-alt me-2"></i>Zmeniť lokáciu
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="showBulkOperationModal('change_owner'); return false;">
                        <i class="bi bi-person me-2"></i>Zmeniť vlastníka
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="generateBulkQrCodes(); return false;">
                        <i class="bi bi-qr-code me-2"></i>Generovať QR kódy
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="showBulkQrCodes(); return false;">
                        <i class="bi bi-printer me-2"></i>Tlačiť QR kódy
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="showBulkOperationModal('delete'); return false;">
                        <i class="bi bi-trash me-2"></i>Vymazať
                    </a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-light btn-sm" onclick="clearSelection()">
                <i class="bi bi-x-circle me-1"></i>Zrušiť výber
            </button>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="stats-summary">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="h3 mb-1">{{ $groupedAssets->count() }}</div>
                <small class="opacity-75">Skupín majetku</small>
            </div>
            <div class="col-md-3">
                <div class="h3 mb-1">{{ $groupedAssets->sum('count') }}</div>
                <small class="opacity-75">Celkom položiek</small>
            </div>
            <div class="col-md-3">
                <div class="h3 mb-1">{{ $categories->count() }}</div>
                <small class="opacity-75">Kategórií</small>
            </div>
            <div class="col-md-3">
                <div class="h3 mb-1">
                    @if(request()->hasAny(['name', 'inventory_number', 'serial_number', 'category_id', 'commission', 'owner', 'status']))
                        <i class="bi bi-funnel text-warning"></i>
                    @else
                        <i class="bi bi-check-circle text-success"></i>
                    @endif
                </div>
                <small class="opacity-75">
                    @if(request()->hasAny(['name', 'inventory_number', 'serial_number', 'category_id', 'commission', 'owner', 'status']))
                        Aktívny filter
                    @else
                        Bez filtrov
                    @endif
                </small>
            </div>
        </div>
    </div>

    <!-- Advanced Filtering -->
    <div class="filter-card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Pokročilé vyhľadávanie a filtrovanie
            </h5>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Názov zariadenia</label>
                        <input type="text" name="name" class="form-control" placeholder="Zadajte názov..." value="{{ request('name') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Inventárne číslo</label>
                        <input type="text" name="inventory_number" class="form-control" placeholder="Zadajte inv. číslo..." value="{{ request('inventory_number') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Sériové číslo</label>
                        <input type="text" name="serial_number" class="form-control" placeholder="Zadajte sér. číslo..." value="{{ request('serial_number') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Kategória</label>
                        <select name="category_id" class="form-select">
                            <option value="">Všetky kategórie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Vlastník</label>
                        <select name="owner" class="form-select">
                            <option value="">Všetci vlastníci</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner }}" {{ request('owner') == $owner ? 'selected' : '' }}>
                                    {{ $owner }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Stav</label>
                        <select name="status" class="form-select">
                            <option value="">Všetky stavy</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktívny</option>
                            <option value="written_off" {{ request('status') == 'written_off' ? 'selected' : '' }}>Odpísaný</option>
                            <option value="in_repair" {{ request('status') == 'in_repair' ? 'selected' : '' }}>V oprave</option>
                            <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Stratený</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="bi bi-search me-1"></i>
                                Vyhľadať
                            </button>
                            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Assets List -->
    <div class="row">
        <div class="col-12">
            @forelse($groupedAssets as $index => $group)
                <div class="card asset-group-card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-0 fw-semibold">
                                    <i class="bi bi-box me-2 text-primary"></i>
                                    {{ $group['name'] }}
                                    <span class="badge-professional ms-2">{{ $group['count'] }}x</span>
                                </h6>
                            </div>
                                <div class="col-md-3">
                                    @if($group['category'])
                                        <span class="badge bg-secondary">{{ $group['category']->name }}</span>
                                    @endif
                                </div>
                                <div class="col-md-3 text-end">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    {{ $group['location'] ? $group['location']->name : 'Bez lokácie' }}
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <strong>Celková cena:</strong><br>
                                    <span class="h5 text-success">{{ number_format($group['total_cost'], 2) }} €</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Stavy:</strong><br>
                                    @php
                                        $statusClasses = [
                                            'active' => 'bg-success',
                                            'written_off' => 'bg-danger',
                                            'in_repair' => 'bg-warning',
                                            'lost' => 'bg-dark'
                                        ];
                                        $statusLabels = [
                                            'active' => 'Aktívny',
                                            'written_off' => 'Odpísaný',
                                            'in_repair' => 'V oprave',
                                            'lost' => 'Stratený'
                                        ];
                                    @endphp
                                    @foreach($group['statuses'] as $status => $count)
                                        <span class="badge {{ $statusClasses[$status] ?? 'bg-secondary' }} me-1">
                                            {{ $statusLabels[$status] ?? $status }}: {{ $count }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="col-md-3">
                                    <strong>Komisie:</strong><br>
                                    @if($group['commissions']->isNotEmpty())
                                        @foreach($group['commissions'] as $commission)
                                            <span class="badge bg-info me-1">{{ $commission }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <strong>Vlastníci:</strong><br>
                                    @if($group['owners']->isNotEmpty())
                                        @foreach($group['owners'] as $owner)
                                            <span class="badge bg-warning me-1">{{ $owner }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="false">
                                <i class="bi bi-list"></i> Zobraziť detaily ({{ $group['count'] }} položiek)
                            </button>
                            
                            <div class="collapse mt-3" id="collapse{{ $index }}">
                                <!-- Group Bulk Operations Toolbar -->
                                <div class="group-bulk-toolbar mb-3" id="group-bulk-toolbar-{{ $index }}" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center bg-info text-white p-2 rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-check-square me-2"></i>
                                            <span id="group-selected-count-{{ $index }}">0</span> položiek vybratých v tejto skupine
                                        </div>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-gear me-1"></i>Skupinové operácie
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="showGroupBulkOperationModal('change_status', {{ $index }}); return false;">
                                                    <i class="bi bi-toggle-on me-2"></i>Zmeniť stav
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="showGroupBulkOperationModal('change_location', {{ $index }}); return false;">
                                                    <i class="bi bi-geo-alt me-2"></i>Zmeniť lokáciu
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="showGroupBulkOperationModal('change_owner', {{ $index }}); return false;">
                                                    <i class="bi bi-person me-2"></i>Zmeniť vlastníka
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="showGroupBulkOperationModal('delete', {{ $index }}); return false;">
                                                    <i class="bi bi-trash me-2"></i>Vymazať
                                                </a></li>
                                            </ul>
                                        </div>
                                        <button type="button" class="btn btn-outline-light btn-sm" onclick="clearGroupSelection({{ $index }})">
                                            <i class="bi bi-x-circle me-1"></i>Zrušiť výber
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th width="40">
                                                    <input type="checkbox" class="form-check-input group-select-all" 
                                                           id="select-all-group-{{ $index }}" 
                                                           data-group="{{ $index }}"
                                                           onchange="toggleGroupAssets(this, {{ $index }})">
                                                </th>
                                                <th>Inventárne číslo</th>
                                                <th>Sériové číslo</th>
                                                <th>Obstarávacia cena</th>
                                                <th>Stav</th>
                                                <th>Akcie</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($group['assets'] as $asset)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input asset-checkbox group-asset-checkbox" 
                                                               value="{{ $asset->id }}" 
                                                               data-group="{{ $index }}"
                                                               onchange="updateBulkToolbars({{ $index }})">
                                                    </td>
                                                    <td><code>{{ $asset->inventory_number }}</code></td>
                                                    <td><code>{{ $asset->serial_number ?? '-' }}</code></td>
                                                    <td>{{ number_format($asset->acquisition_cost, 2) }} €</td>
                                                    <td>
                                                        <span class="badge {{ $statusClasses[$asset->status] }}">
                                                            {{ $statusLabels[$asset->status] }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-secondary btn-sm">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-sm">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Naozaj chcete zmazať?')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox-fill fa-3x mb-3 d-block"></i>
                        Žiadny majetok nebol nájdený.
                    </div>
                @endforelse
            </div>
            </div>
        </div>
    </div>

    <!-- Bulk Operation Modal -->
    <div class="modal fade" id="bulkOperationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkOperationTitle">Hromadná operácia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="bulkOperationForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="operation" id="bulkOperation">
                        <input type="hidden" name="asset_ids" id="bulkAssetIds">
                        
                        <div id="bulkOperationContent">
                            <!-- Content will be dynamically filled -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                        <button type="submit" class="btn btn-primary" id="bulkOperationSubmit">Vykonať</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Fallback styling pre dropdown ak Bootstrap nefunguje */
.dropdown-menu.show {
    display: block !important;
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    min-width: 10rem;
    padding: 0.5rem 0;
    margin: 0.125rem 0 0;
    background-color: #fff;
    border: 1px solid rgba(0,0,0,.15);
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
}

.dropdown {
    position: relative;
}

.bulk-operations-toolbar {
    animation: slideDown 0.3s ease-out;
}

.group-bulk-toolbar {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script>
let selectedAssets = [];
let groupSelectedAssets = {}; // Objekt pre sledovanie vybraných assetov v jednotlivých skupinách

// Toggle všetkých assetov (globálny select-all)
function toggleAllAssets(checkbox) {
    const assetCheckboxes = document.querySelectorAll('.asset-checkbox');
    selectedAssets = [];
    
    assetCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedAssets.push(cb.value);
        }
    });
    
    // Aktualizuj aj group select-all checkboxy
    document.querySelectorAll('.group-select-all').forEach(groupCheckbox => {
        groupCheckbox.checked = checkbox.checked;
    });
    
    updateBulkToolbar();
    updateAllGroupToolbars();
}

// Toggle assetov v konkrétnej skupine
function toggleGroupAssets(checkbox, groupIndex) {
    const groupCheckboxes = document.querySelectorAll(`.asset-checkbox[data-group="${groupIndex}"]`);
    
    if (!groupSelectedAssets[groupIndex]) {
        groupSelectedAssets[groupIndex] = [];
    }
    
    groupSelectedAssets[groupIndex] = [];
    
    groupCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            groupSelectedAssets[groupIndex].push(cb.value);
        }
    });
    
    updateGroupToolbar(groupIndex);
    updateGlobalToolbarFromGroups();
}

// Aktualizácia hlavného (globálneho) bulk toolbar
function updateBulkToolbar() {
    const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
    selectedAssets = Array.from(checkboxes).map(cb => cb.value);
    
    const toolbar = document.getElementById('bulk-toolbar');
    const countSpan = document.getElementById('selected-count');
    const selectAllCheckbox = document.getElementById('select-all-assets');
    
    if (selectedAssets.length > 0) {
        toolbar.style.display = 'block';
        countSpan.textContent = selectedAssets.length;
    } else {
        toolbar.style.display = 'none';
        selectAllCheckbox.checked = false;
    }
    
    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.asset-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.asset-checkbox:checked');
    
    if (checkedCheckboxes.length === allCheckboxes.length && allCheckboxes.length > 0) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedCheckboxes.length > 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
}

// Aktualizácia toolbar pre konkrétnu skupinu
function updateGroupToolbar(groupIndex) {
    const groupCheckboxes = document.querySelectorAll(`.asset-checkbox[data-group="${groupIndex}"]:checked`);
    const groupSelectedCount = groupCheckboxes.length;
    
    if (!groupSelectedAssets[groupIndex]) {
        groupSelectedAssets[groupIndex] = [];
    }
    
    groupSelectedAssets[groupIndex] = Array.from(groupCheckboxes).map(cb => cb.value);
    
    const groupToolbar = document.getElementById(`group-bulk-toolbar-${groupIndex}`);
    const groupCountSpan = document.getElementById(`group-selected-count-${groupIndex}`);
    const groupSelectAllCheckbox = document.getElementById(`select-all-group-${groupIndex}`);
    
    if (groupSelectedCount > 0) {
        groupToolbar.style.display = 'block';
        groupCountSpan.textContent = groupSelectedCount;
    } else {
        groupToolbar.style.display = 'none';
        groupSelectAllCheckbox.checked = false;
    }
    
    // Update group select all checkbox state
    const allGroupCheckboxes = document.querySelectorAll(`.asset-checkbox[data-group="${groupIndex}"]`);
    const checkedGroupCheckboxes = document.querySelectorAll(`.asset-checkbox[data-group="${groupIndex}"]:checked`);
    
    if (checkedGroupCheckboxes.length === allGroupCheckboxes.length && allGroupCheckboxes.length > 0) {
        groupSelectAllCheckbox.checked = true;
        groupSelectAllCheckbox.indeterminate = false;
    } else if (checkedGroupCheckboxes.length > 0) {
        groupSelectAllCheckbox.checked = false;
        groupSelectAllCheckbox.indeterminate = true;
    } else {
        groupSelectAllCheckbox.checked = false;
        groupSelectAllCheckbox.indeterminate = false;
    }
}

// Aktualizácia všetkých group toolbarov
function updateAllGroupToolbars() {
    document.querySelectorAll('.group-select-all').forEach(checkbox => {
        const groupIndex = checkbox.dataset.group;
        updateGroupToolbar(groupIndex);
    });
}

// Aktualizácia globálneho toolbar na základe zmien v skupinách
function updateGlobalToolbarFromGroups() {
    // Zbieranie všetkých vybraných assetov zo všetkých skupín
    const allSelectedFromGroups = [];
    Object.values(groupSelectedAssets).forEach(groupAssets => {
        allSelectedFromGroups.push(...groupAssets);
    });
    
    selectedAssets = allSelectedFromGroups;
    
    const toolbar = document.getElementById('bulk-toolbar');
    const countSpan = document.getElementById('selected-count');
    
    if (selectedAssets.length > 0) {
        toolbar.style.display = 'block';
        countSpan.textContent = selectedAssets.length;
    } else {
        toolbar.style.display = 'none';
    }
}

// Univerzálna funkcia pre aktualizáciu toolbarov
function updateBulkToolbars(groupIndex) {
    updateGroupToolbar(groupIndex);
    updateGlobalToolbarFromGroups();
}

// Vyčistenie výberu (globálne)
function clearSelection() {
    document.querySelectorAll('.asset-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.group-select-all').forEach(cb => cb.checked = false);
    document.getElementById('select-all-assets').checked = false;
    selectedAssets = [];
    groupSelectedAssets = {};
    updateBulkToolbar();
    updateAllGroupToolbars();
}

// Vyčistenie výberu v konkrétnej skupine
function clearGroupSelection(groupIndex) {
    document.querySelectorAll(`.asset-checkbox[data-group="${groupIndex}"]`).forEach(cb => cb.checked = false);
    document.getElementById(`select-all-group-${groupIndex}`).checked = false;
    groupSelectedAssets[groupIndex] = [];
    updateGroupToolbar(groupIndex);
    updateGlobalToolbarFromGroups();
}

// Zobrazenie bulk operation modal (globálne)
function showBulkOperationModal(operation) {
    if (selectedAssets.length === 0) {
        alert('Najprv vyberte asety pre hromadnú operáciu');
        return;
    }
    
    showBulkModal(operation, selectedAssets, 'Globálna hromadná operácia');
}

// Zobrazenie group bulk operation modal
function showGroupBulkOperationModal(operation, groupIndex) {
    if (!groupSelectedAssets[groupIndex] || groupSelectedAssets[groupIndex].length === 0) {
        alert('Najprv vyberte asety v tejto skupine pre hromadnú operáciu');
        return;
    }
    
    showBulkModal(operation, groupSelectedAssets[groupIndex], `Skupinová hromadná operácia (Skupina ${groupIndex + 1})`);
}

// Spoločná funkcia pre zobrazenie bulk modal
function showBulkModal(operation, assetIds, modalTitle) {
    const modal = new bootstrap.Modal(document.getElementById('bulkOperationModal'));
    const title = document.getElementById('bulkOperationTitle');
    const content = document.getElementById('bulkOperationContent');
    const submitBtn = document.getElementById('bulkOperationSubmit');
    
    title.textContent = modalTitle;
    document.getElementById('bulkOperation').value = operation;
    document.getElementById('bulkAssetIds').value = JSON.stringify(assetIds);
    
    let modalContent = '';
    let buttonText = 'Vykonať';
    let buttonClass = 'btn-primary';
    
    switch(operation) {
        case 'change_status':
            modalContent = `
                <p>Zmeniť stav pre <strong>${assetIds.length}</strong> vybratých assetov:</p>
                <div class="mb-3">
                    <label class="form-label">Nový stav:</label>
                    <select name="new_status" class="form-select" required>
                        <option value="">Vyberte stav</option>
                        <option value="active">Aktívny</option>
                        <option value="written_off">Odpísaný</option>
                        <option value="in_repair">V oprave</option>
                        <option value="lost">Stratený</option>
                    </select>
                </div>
            `;
            break;
            
        case 'change_location':
            modalContent = `
                <p>Zmeniť lokáciu pre <strong>${assetIds.length}</strong> vybratých assetov:</p>
                <div class="mb-3">
                    <label class="form-label">Nová lokácia:</label>
                    <select name="new_location_id" class="form-select" required>
                        <option value="">Načítavam lokácie...</option>
                    </select>
                </div>
            `;
            // Load locations via AJAX
            fetch('/ajax/locations', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const select = document.querySelector('select[name="new_location_id"]');
                    select.innerHTML = '<option value="">Vyberte lokáciu</option>';
                    if (data.success && data.locations) {
                        data.locations.forEach(location => {
                            select.innerHTML += `<option value="${location.id}">${location.name}</option>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Chyba pri načítavaní lokácií:', error);
                    const select = document.querySelector('select[name="new_location_id"]');
                    select.innerHTML = '<option value="">Chyba pri načítavaní</option>';
                });
            break;
            
        case 'change_owner':
            modalContent = `
                <p>Zmeniť vlastníka pre <strong>${assetIds.length}</strong> vybratých assetov:</p>
                <div class="mb-3">
                    <label class="form-label">Nový vlastník:</label>
                    <select name="new_owner_id" class="form-select" required>
                        <option value="">Načítavam používateľov...</option>
                    </select>
                </div>
            `;
            // Load users via AJAX
            fetch('/ajax/users/search', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const select = document.querySelector('select[name="new_owner_id"]');
                    select.innerHTML = '<option value="">Vyberte vlastníka</option>';
                    if (data.success && data.users) {
                        data.users.forEach(user => {
                            select.innerHTML += `<option value="${user.id}">${user.name} (${user.email})</option>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Chyba pri načítavaní používateľov:', error);
                    const select = document.querySelector('select[name="new_owner_id"]');
                    select.innerHTML = '<option value="">Chyba pri načítavaní</option>';
                });
            break;
            
        case 'delete':
            buttonText = 'VYMAZAŤ';
            buttonClass = 'btn-danger';
            modalContent = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Pozor!</strong> Táto operácia je nevratná.
                </div>
                <p>Naozaj chcete vymazať <strong>${assetIds.length}</strong> vybratých assetov?</p>
                <p class="text-muted">Vymazané assety nebude možné obnoviť.</p>
            `;
            break;
    }
    
    content.innerHTML = modalContent;
    submitBtn.textContent = buttonText;
    submitBtn.className = `btn ${buttonClass}`;
    
    modal.show();
}

// Submit bulk operation form
document.getElementById('bulkOperationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Remove the JSON string asset_ids and add individual array elements
    formData.delete('asset_ids');
    const assetIdsFromForm = JSON.parse(document.getElementById('bulkAssetIds').value);
    assetIdsFromForm.forEach(assetId => {
        formData.append('asset_ids[]', assetId);
    });
    
    const submitBtn = document.getElementById('bulkOperationSubmit');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Spracúvam...';
    
    fetch('{{ route("assets.bulk-operation") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('bulkOperationModal')).hide();
            
            // Show success message
            showAlert('success', data.message);
            
            // Clear selection and reload page after 2 seconds
            clearSelection();
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            // Show error message
            showAlert('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Show error message
        showAlert('danger', 'Nastala chyba pri vykonávaní operácie: ' + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update toolbar on page load in case there are pre-selected items
    updateBulkToolbar();
    
    // Fallback pre dropdown ak Bootstrap nie je načítaný
    const dropdownToggle = document.getElementById('bulkOperationsDropdown');
    if (dropdownToggle && !window.bootstrap) {
        console.warn('Bootstrap nie je načítaný, používam fallback pre dropdown');
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu.style.display === 'block') {
                dropdownMenu.style.display = 'none';
                dropdownMenu.classList.remove('show');
            } else {
                dropdownMenu.style.display = 'block';
                dropdownMenu.classList.add('show');
            }
        });
    }
    
    // Debug info
    if (window.bootstrap) {
        console.log('Bootstrap je načítaný správne');
    } else {
        console.warn('Bootstrap nie je načítaný!');
    }
});

// QR Code functions
function generateBulkQrCodes() {
    if (selectedAssets.length === 0) {
        alert('Najprv vyberte asety pre generovanie QR kódov');
        return;
    }

    // Show loading indicator
    showAlert('info', 'Generujú sa QR kódy...', false);

    fetch('{{ route("assets.bulk-generate-qr-codes") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            asset_ids: selectedAssets
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
        } else {
            showAlert('danger', data.message || 'Chyba pri generovaní QR kódov');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Chyba pri generovaní QR kódov');
    });
}

function showBulkQrCodes() {
    if (selectedAssets.length === 0) {
        alert('Najprv vyberte asety pre zobrazenie QR kódov');
        return;
    }

    // Create form and submit
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = '{{ route("assets.show-qr-codes") }}';
    form.target = '_blank';

    selectedAssets.forEach(assetId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'asset_ids[]';
        input.value = assetId;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function showAlert(type, message, autoHide = true) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.dynamic-alert');
    existingAlerts.forEach(alert => alert.remove());

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show dynamic-alert`;
    alert.innerHTML = `
        <i class="bi bi-${getAlertIcon(type)} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Find the best place to insert the alert
    const container = document.querySelector('.container-fluid');
    const toolbar = document.querySelector('.bulk-operations-toolbar');
    const firstContent = container.querySelector('.row') || container.firstElementChild;
    
    if (toolbar && toolbar.parentNode === container) {
        // Insert before toolbar if it exists and is visible
        container.insertBefore(alert, toolbar);
    } else if (firstContent) {
        // Insert before first content
        container.insertBefore(alert, firstContent);
    } else {
        // Fallback: prepend to container
        container.prepend(alert);
    }
    
    // Auto-hide after 5 seconds
    if (autoHide) {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
}

function getAlertIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'danger': return 'exclamation-triangle';
        case 'warning': return 'exclamation-triangle';
        case 'info': return 'info-circle';
        default: return 'info-circle';
    }
}

function showErrorAlert(message) {
    showAlert('danger', message);
}
</script>
@endpush