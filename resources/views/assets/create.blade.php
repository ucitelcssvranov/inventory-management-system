@extends('layouts.app')

@section('title', 'Pridať majetok')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-plus me-2"></i>Pridať majetok</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('assets.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="inventory_number_preview" class="form-label">Inventárne číslo (automaticky generované)</label>
                                <input type="text" class="form-control bg-light" 
                                       id="inventory_number_preview" 
                                       readonly 
                                       placeholder="Bude generované na základe dátumu nadobudnutia">
                                <small class="form-text text-muted">
                                    Inventárne číslo sa automaticky vygeneruje vo formáte: ROK-ID
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Názov *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategória</label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" 
                                        name="category_id">
                                    <option value="">Vyberte kategóriu</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="serial_number" class="form-label">Sériové číslo</label>
                                <input type="text" class="form-control @error('serial_number') is-invalid @enderror" 
                                       id="serial_number" name="serial_number" value="{{ old('serial_number') }}">
                                @error('serial_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Nepovinné pole</small>
                            </div>
                        </div>
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

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="acquisition_date" class="form-label">Dátum obstarania *</label>
                                <input type="date" class="form-control @error('acquisition_date') is-invalid @enderror" 
                                       id="acquisition_date" name="acquisition_date" value="{{ old('acquisition_date') }}" required>
                                @error('acquisition_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="acquisition_cost" class="form-label">Obstarávacia cena (€) *</label>
                                <input type="number" step="0.01" class="form-control @error('acquisition_cost') is-invalid @enderror" 
                                       id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost') }}" required>
                                @error('acquisition_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="residual_value" class="form-label">Zostatková hodnota (€)</label>
                                <input type="number" step="0.01" class="form-control @error('residual_value') is-invalid @enderror" 
                                       id="residual_value" name="residual_value" value="{{ old('residual_value') }}">
                                @error('residual_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="owner_id" class="form-label">Vlastník</label>
                        <select name="owner_id" id="owner_id" class="form-select" required>
                            <option value="">Vyberte vlastníka</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Stav *</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktívny</option>
                            <option value="written_off" {{ old('status') == 'written_off' ? 'selected' : '' }}>Odpísaný</option>
                            <option value="in_repair" {{ old('status') == 'in_repair' ? 'selected' : '' }}>V oprave</option>
                            <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Stratený</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Popis</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
    // Inicializácia autocomplete pre kategórie ak ich je veľa
    initializeAutocomplete('#category_id', {
        placeholder: 'Začnite písať názov kategórie...',
        noResultsText: 'Žiadne kategórie nenájdené'
    });

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
            }
        })
        .catch(error => {
            console.error('Chyba pri načítavaní budov:', error);
            const buildingSelect = document.getElementById('building_id');
            buildingSelect.innerHTML = '<option value="">Chyba pri načítavaní budov</option>';
        });
    }

    // Funkcia na načítanie miestností pre danú budovu
    function loadRooms(buildingId) {
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

    // Funkcia na generovanie inventárneho čísla
    function generateInventoryNumber() {
        const acquisitionDateInput = document.getElementById('acquisition_date');
        const inventoryNumberPreview = document.getElementById('inventory_number_preview');
        
        if (!acquisitionDateInput.value) {
            inventoryNumberPreview.value = '';
            inventoryNumberPreview.placeholder = 'Najprv zadajte dátum nadobudnutia';
            return;
        }

        // Zobrazíme loading state
        inventoryNumberPreview.value = 'Generuje sa...';
        inventoryNumberPreview.disabled = true;

        fetch('{{ route("assets.generate-inventory-number") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                acquisition_date: acquisitionDateInput.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                inventoryNumberPreview.value = data.inventory_number;
            } else {
                inventoryNumberPreview.value = '';
                inventoryNumberPreview.placeholder = 'Chyba pri generovaní';
                console.error('Chyba pri generovaní inventárneho čísla:', data.message);
            }
        })
        .catch(error => {
            console.error('Chyba pri komunikácii so serverom:', error);
            inventoryNumberPreview.value = '';
            inventoryNumberPreview.placeholder = 'Chyba pri generovaní';
        })
        .finally(() => {
            inventoryNumberPreview.disabled = false;
        });
    }

    // Event listener pre zmenu dátumu nadobudnutia
    document.getElementById('acquisition_date').addEventListener('change', generateInventoryNumber);
});
</script>
@endsection
