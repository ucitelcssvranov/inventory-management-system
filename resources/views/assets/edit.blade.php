@extends('layouts.app')

@section('title', 'Upraviť majetok')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-edit me-2"></i>Upraviť majetok</h1>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('assets.update', $asset) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="inventory_number" class="form-label">Inventárne číslo (automaticky generované)</label>
                        <input type="text" class="form-control bg-light"
                               id="inventory_number" 
                               value="{{ $asset->inventory_number }}" 
                               readonly>
                        <small class="form-text text-muted">
                            Inventárne číslo sa automaticky aktualizuje pri zmene dátumu nadobudnutia
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Názov *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $asset->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategória *</label>
                        <select class="form-select @error('category_id') is-invalid @enderror"
                                id="category_id" name="category_id" required>
                            <option value="">Vyberte kategóriu</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (old('category_id', $asset->category_id) == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="serial_number" class="form-label">Sériové číslo</label>
                        <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
                               id="serial_number" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}">
                        @error('serial_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Nepovinné pole</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="building_id" class="form-label">Budova *</label>
                                <select class="form-select @error('building_id') is-invalid @enderror"
                                        id="building_id"
                                        name="building_id"
                                        data-loads-rooms="#location_id">
                                    <option value="">Vyberte budovu</option>
                                </select>
                                @error('building_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location_id" class="form-label">Miestnosť *</label>
                                <select class="form-select @error('location_id') is-invalid @enderror"
                                        id="location_id"
                                        name="location_id"
                                        required>
                                    <option value="">Najprv vyberte budovu</option>
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="acquisition_date" class="form-label">Dátum nadobudnutia</label>
                        <input type="date" class="form-control @error('acquisition_date') is-invalid @enderror"
                               id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date', $asset->acquisition_date) }}">
                        @error('acquisition_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="acquisition_cost" class="form-label">Náklady na nadobudnutie</label>
                        <input type="number" class="form-control @error('acquisition_cost') is-invalid @enderror"
                               id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost', $asset->acquisition_cost) }}" step="0.01">
                        @error('acquisition_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="residual_value" class="form-label">Zostatková hodnota</label>
                        <input type="number" class="form-control @error('residual_value') is-invalid @enderror"
                               id="residual_value" name="residual_value" value="{{ old('residual_value', $asset->residual_value) }}" step="0.01">
                        @error('residual_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Stav</label>
                        <select class="form-select @error('status') is-invalid @enderror"
                                id="status" name="status">
                            <option value="active" {{ (old('status', $asset->status) == 'active') ? 'selected' : '' }}>Aktívne</option>
                            <option value="inactive" {{ (old('status', $asset->status) == 'inactive') ? 'selected' : '' }}>Neaktívne</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Popis</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $asset->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="owner_id" class="form-label">Vlastník</label>
                        <select name="owner_id" id="owner_id" class="form-select" required>
                            <option value="">Vyberte vlastníka</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $asset->owner_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="inventory_commission_id" class="form-label">Inventarizačná komisia</label>
                        <select name="inventory_commission_id" id="inventory_commission_id" class="form-select" required>
                            <option value="">Vyberte komisiu</option>
                            @foreach($inventoryCommissions as $inventoryCommission)
                                <option value="{{ $inventoryCommission->id }}" {{ $asset->inventory_commission_id == $inventoryCommission->id ? 'selected' : '' }}>{{ $inventoryCommission->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('assets.index') }}" class="btn btn-secondary me-2">Zrušiť</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Uložiť
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentLocationId = {{ $asset->location_id ?? 'null' }};
    let currentBuildingId = null;

    // Načítanie budov pri načítaní stránky
    loadBuildings();

    // Event listener pre zmenu budovy - načíta miestnosti
    document.getElementById('building_id').addEventListener('change', function() {
        const buildingId = this.value;
        const roomSelect = document.getElementById('location_id');
        
        // Vyčisti miestnosti
        roomSelect.innerHTML = '<option value="">Načítavam miestnosti...</option>';
        
        if (buildingId) {
            loadRooms(buildingId);
        } else {
            roomSelect.innerHTML = '<option value="">Najprv vyberte budovu</option>';
        }
    });

    // Funkcia na načítanie budov
    function loadBuildings() {
        fetch('/ajax/buildings', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const buildingSelect = document.getElementById('building_id');
            buildingSelect.innerHTML = '<option value="">Vyberte budovu</option>';
            
            if (data.success && data.buildings) {
                data.buildings.forEach(building => {
                    const option = document.createElement('option');
                    option.value = building.id;
                    option.textContent = building.name;
                    buildingSelect.appendChild(option);
                });
                
                // Ak máme súčasnú lokáciu, načítaj budovu pre túto lokáciu
                if (currentLocationId) {
                    findBuildingForLocation(currentLocationId);
                }
            }
        })
        .catch(error => {
            console.error('Chyba pri načítavaní budov:', error);
            const buildingSelect = document.getElementById('building_id');
            buildingSelect.innerHTML = '<option value="">Chyba pri načítavaní budov</option>';
        });
    }

    // Funkcia na načítanie miestností pre danú budovu
    function loadRooms(buildingId, selectLocationId = null) {
        fetch(`/ajax/rooms/${buildingId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const roomSelect = document.getElementById('location_id');
            roomSelect.innerHTML = '<option value="">Vyberte miestnosť</option>';
            
            if (data.success && data.rooms) {
                data.rooms.forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = room.display_name || room.name;
                    
                    // Označiť súčasnú lokáciu ako selected
                    if (selectLocationId && room.id == selectLocationId) {
                        option.selected = true;
                    }
                    
                    roomSelect.appendChild(option);
                });
            } else {
                roomSelect.innerHTML = '<option value="">Žiadne miestnosti nenájdené</option>';
            }
        })
        .catch(error => {
            console.error('Chyba pri načítavaní miestností:', error);
            const roomSelect = document.getElementById('location_id');
            roomSelect.innerHTML = '<option value="">Chyba pri načítavaní miestností</option>';
        });
    }
    
    // Funkcia na nájdenie budovy pre existujúcu lokáciu
    function findBuildingForLocation(locationId) {
        // Načítame informácie o lokácii
        fetch(`/ajax/locations/0`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.locations) {
                // Prejdeme všetky budovy a nájdeme tú, ktorá obsahuje našu lokáciu
                data.locations.forEach(building => {
                    loadRoomsAndCheck(building.id, locationId);
                });
            }
        })
        .catch(error => {
            console.error('Chyba pri hľadaní budovy pre lokáciu:', error);
        });
    }
    
    // Pomocná funkcia na kontrolu, či budova obsahuje danú lokáciu
    function loadRoomsAndCheck(buildingId, targetLocationId) {
        fetch(`/ajax/rooms/${buildingId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.rooms) {
                const foundRoom = data.rooms.find(room => room.id == targetLocationId);
                if (foundRoom) {
                    // Našli sme správnu budovu!
                    currentBuildingId = buildingId;
                    document.getElementById('building_id').value = buildingId;
                    loadRooms(buildingId, targetLocationId);
                }
            }
        })
        .catch(error => {
            console.error('Chyba pri kontrole miestností:', error);
        });
    }
});
</script>
@endsection