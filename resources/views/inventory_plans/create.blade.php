@extends('layouts.app')

@section('title', 'Pridať inventúrny plán')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h2>Nový inventúrny plán</h2>
        <form method="POST" action="{{ route('inventory_plans.store') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Názov plánu</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Popis</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
            <div class="mb-3">
                <label for="description" class="form-label">Popis</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Typ inventúry</label>
                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="">Vyberte typ inventúry</option>
                    <option value="fyzická" {{ old('type') == 'fyzická' ? 'selected' : '' }}>Fyzická</option>
                    <option value="dokladová" {{ old('type') == 'dokladová' ? 'selected' : '' }}>Dokladová</option>
                    <option value="kombinovaná" {{ old('type') == 'kombinovaná' ? 'selected' : '' }}>Kombinovaná</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Zjednodušené dátumové polia -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_start" class="form-label">Dátum začatia inventúry</label>
                        <input type="date" name="date_start" id="date_start" 
                               class="form-control @error('date_start') is-invalid @enderror" 
                               value="{{ old('date_start', date('Y-m-d')) }}" required>
                        @error('date_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="date_end" class="form-label">Dátum ukončenia inventúry</label>
                        <input type="date" name="date_end" id="date_end" 
                               class="form-control @error('date_end') is-invalid @enderror" 
                               value="{{ old('date_end') }}" required>
                        @error('date_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="inventory_day" class="form-label">Hlavný deň inventúry</label>
                <input type="date" name="inventory_day" id="inventory_day" 
                       class="form-control @error('inventory_day') is-invalid @enderror" 
                       value="{{ old('inventory_day') }}" required>
                <div class="form-text">Deň, kedy sa bude vykonávať hlavná časť inventarizácie</div>
                @error('inventory_day')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="date" class="form-label">Dátum plánu</label>
                <input type="date" name="date" id="date" 
                       class="form-control @error('date') is-invalid @enderror" 
                       value="{{ old('date', date('Y-m-d')) }}" required>
                <div class="form-text">Dátum vytvorenia plánu</div>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="unit_name" class="form-label">Názov účtovnej jednotky</label>
                <input type="text" name="unit_name" id="unit_name" 
                       class="form-control @error('unit_name') is-invalid @enderror" 
                       value="{{ old('unit_name', 'Centrum stredného odborného vzdelávania Vranov nad Topľou') }}" required>
                @error('unit_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="unit_address" class="form-label">Adresa účtovnej jednotky</label>
                <input type="text" name="unit_address" id="unit_address" 
                       class="form-control @error('unit_address') is-invalid @enderror" 
                       value="{{ old('unit_address', 'Komenského 2, 093 01 Vranov nad Topľou') }}" required>
                @error('unit_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="storage_place" class="form-label">Miesto uloženia</label>
                <input type="text" name="storage_place" id="storage_place" 
                       class="form-control @error('storage_place') is-invalid @enderror" 
                       value="{{ old('storage_place', 'Administratívna budova školy') }}" required>
                @error('storage_place')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="responsible_person_id" class="form-label">Hmotne zodpovedná osoba</label>
                <select name="responsible_person_id" id="responsible_person_id" 
                        class="form-select @error('responsible_person_id') is-invalid @enderror" required>
                    <option value="">Vyberte zodpovednú osobu</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('responsible_person_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('responsible_person_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Voliteľné polia -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Voliteľné nastavenia</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location_id" class="form-label">Špecifická lokácia</label>
                                <select name="location_id" id="location_id" 
                                        class="form-select @error('location_id') is-invalid @enderror">
                                    <option value="">Všetky lokácie</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Ponechajte prázdne pre inventarizáciu všetkých lokácií</div>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Špecifická kategória</label>
                                <select name="category_id" id="category_id" 
                                        class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Všetky kategórie</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Ponechajte prázdne pre inventarizáciu všetkých kategórií</div>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('inventory_plans.index') }}" class="btn btn-secondary me-2">Zrušiť</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Uložiť plán
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Automatické nastavenie dátumu konca na základe začiatku
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    const inventoryDay = document.getElementById('inventory_day');
    
    dateStart.addEventListener('change', function() {
        if (this.value && !dateEnd.value) {
            // Nastavíme dátum konca na +7 dní od začiatku
            const startDate = new Date(this.value);
            startDate.setDate(startDate.getDate() + 7);
            dateEnd.value = startDate.toISOString().split('T')[0];
        }
        
        if (this.value && !inventoryDay.value) {
            // Nastavíme deň inventúry na +3 dni od začiatku
            const startDate = new Date(this.value);
            startDate.setDate(startDate.getDate() + 3);
            inventoryDay.value = startDate.toISOString().split('T')[0];
        }
    });
    
    // Validácia dátumov
    dateEnd.addEventListener('change', function() {
        if (dateStart.value && this.value && this.value < dateStart.value) {
            alert('Dátum ukončenia nemôže byť pred dátumom začiatku');
            this.value = '';
        }
    });
    
    inventoryDay.addEventListener('change', function() {
        if (dateStart.value && this.value && this.value < dateStart.value) {
            alert('Deň inventúry nemôže byť pred dátumom začiatku');
            this.value = '';
        }
        if (dateEnd.value && this.value && this.value > dateEnd.value) {
            alert('Deň inventúry nemôže byť po dátume ukončenia');
            this.value = '';
        }
    });
    
    // Inicializácia autocomplete pre select boxy s veľkým množstvom možností
    initializeAutocomplete('#responsible_person_id', {
        placeholder: 'Začnite písať meno alebo email...',
        noResultsText: 'Žiadni používatelia nenájdení',
        minLength: 2
    });
    
    // Voliteľné - autocomplete pre lokácie a kategórie ak je veľa možností  
    const locationSelect = document.getElementById('location_id');
    const categorySelect = document.getElementById('category_id');
    
    if (locationSelect && locationSelect.options.length > 10) {
        initializeAutocomplete('#location_id', {
            placeholder: 'Začnite písať názov lokácie...',
            noResultsText: 'Žiadne lokácie nenájdené'
        });
    }
    
    if (categorySelect && categorySelect.options.length > 10) {
        initializeAutocomplete('#category_id', {
            placeholder: 'Začnite písať názov kategórie...',
            noResultsText: 'Žiadne kategórie nenájdené'
        });
    }
});
</script>
@endsection