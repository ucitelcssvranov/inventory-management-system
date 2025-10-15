@extends('layouts.app')

@section('title', 'Pridať inventúrny plán')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle me-2"></i>Nový inventúrny plán</h2>
            <a href="{{ route('inventory_plans.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Späť na zoznam
            </a>
        </div>

        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Informácie o vytváraní plánu:</strong>
            <ul class="mb-0 mt-2">
                <li>Položky plánu sa automaticky vytvoria na základe vybraných filtrov</li>
                <li>Môžete filtrovať podľa lokácií a/alebo kategórií</li>
                <li>Ak nevyberiete žiadne filtre, zahrnuté budú všetky dostupné položky</li>
                <li>Po vytvorení môžete plán priradiť inventarizačnej komisii</li>
            </ul>
        </div>

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
                    <option value="fyzická" {{ old('type') == 'fyzická' ? 'selected' : '' }}>
                        Fyzická - kontrola skutočného stavu majetku
                    </option>
                    <option value="dokladová" {{ old('type') == 'dokladová' ? 'selected' : '' }}>
                        Dokladová - kontrola účtovných záznamov
                    </option>
                    <option value="kombinovaná" {{ old('type') == 'kombinovaná' ? 'selected' : '' }}>
                        Kombinovaná - fyzická aj dokladová kontrola
                    </option>
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
                <label for="responsible_person_id" class="form-label">Zodpovedná osoba</label>
                <select name="responsible_person_id" id="responsible_person_id" 
                        class="form-select @error('responsible_person_id') is-invalid @enderror" required>
                    <option value="">Vyberte zodpovednú osobu</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('responsible_person_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Osoba zodpovedná za realizáciu inventarizácie</div>
                @error('responsible_person_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="plan_category_id" class="form-label">Kategória plánu</label>
                <select name="plan_category_id" id="plan_category_id" 
                        class="form-select @error('plan_category_id') is-invalid @enderror">
                    <option value="">Vyberte kategóriu plánu</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('plan_category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Hlavná kategória tohto inventúrneho plánu</div>
                @error('plan_category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="commission_id" class="form-label">Inventarizačná komisia <span class="text-danger">*</span></label>
                <select name="commission_id" id="commission_id" 
                        class="form-select @error('commission_id') is-invalid @enderror" required>
                    <option value="">Vyberte komisiu</option>
                    @foreach($commissions as $commission)
                        <option value="{{ $commission->id }}" {{ old('commission_id') == $commission->id ? 'selected' : '' }}>
                            {{ $commission->name }}
                            @if($commission->chairman)
                                (Predseda: {{ $commission->chairman->name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Komisia zodpovedná za vykonanie inventúry</div>
                @error('commission_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>


            <!-- Lokácie plánu -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Lokácie inventúrneho plánu</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="location_ids" class="form-label">Lokácie plánu <span class="text-danger">*</span></label>
                                <select name="location_ids[]" id="location_ids" 
                                        class="form-select @error('location_ids') is-invalid @enderror" 
                                        multiple size="8" required>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" 
                                                {{ collect(old('location_ids', []))->contains($location->id) ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Vyberte lokácie, ktoré patria do tohto inventúrneho plánu. Držte Ctrl a kliknite pre výber viacerých.
                                </div>
                                @error('location_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Kategória <span class="text-muted">(voliteľné)</span></label>
                                <select name="category_id" id="category_id" 
                                        class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Všetky kategórie</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Vyberte kategóriu pre inventarizáciu iba položiek tejto kategórie.
                                </div>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-lightbulb me-1"></i>
                                    <strong>Tip:</strong> Ak nevyberiete žiadne lokácie ani kategóriu, 
                                    budú zahrnuté všetky položky v systéme.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('inventory_plans.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Zrušiť a vrátiť sa
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-1"></i>Vytvoriť inventúrny plán
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
    
    // Vylepšenie multiple select pre lokácie
    const locationSelect = document.getElementById('location_ids');
    const categorySelect = document.getElementById('category_id');
    
    // Pridanie funkcionalít pre multiple select
    if (locationSelect) {
        // Pridanie možnosti "Vybrať všetky" / "Zrušiť výber"
        const selectAllBtn = document.createElement('button');
        selectAllBtn.type = 'button';
        selectAllBtn.className = 'btn btn-sm btn-outline-secondary me-2 mb-2';
        selectAllBtn.innerHTML = '<i class="bi bi-check-all me-1"></i>Vybrať všetky';
        
        const clearAllBtn = document.createElement('button');
        clearAllBtn.type = 'button';
        clearAllBtn.className = 'btn btn-sm btn-outline-secondary mb-2';
        clearAllBtn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Zrušiť výber';
        
        // Vloženie tlačidiel pred select
        locationSelect.parentNode.insertBefore(selectAllBtn, locationSelect);
        locationSelect.parentNode.insertBefore(clearAllBtn, locationSelect);
        
        selectAllBtn.addEventListener('click', function() {
            Array.from(locationSelect.options).forEach(option => option.selected = true);
            updateLocationSummary();
        });
        
        clearAllBtn.addEventListener('click', function() {
            Array.from(locationSelect.options).forEach(option => option.selected = false);
            updateLocationSummary();
        });
        
        // Aktualizácia súhrnu vybraných lokácií
        function updateLocationSummary() {
            const selected = Array.from(locationSelect.selectedOptions);
            const summary = locationSelect.parentNode.querySelector('.selection-summary');
            if (summary) summary.remove();
            
            if (selected.length > 0) {
                const summaryDiv = document.createElement('div');
                summaryDiv.className = 'selection-summary mt-2 alert alert-success py-1';
                summaryDiv.innerHTML = `<small><i class="bi bi-check-circle me-1"></i>Vybraných lokácií: <strong>${selected.length}</strong></small>`;
                locationSelect.parentNode.appendChild(summaryDiv);
            }
        }
        
        locationSelect.addEventListener('change', updateLocationSummary);
        updateLocationSummary(); // Inicializácia
    }
});
</script>

<style>
#location_ids {
    background-color: #f8f9fa;
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
}

#location_ids:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

#location_ids option {
    padding: 8px 12px;
    border-bottom: 1px solid #e9ecef;
}

#location_ids option:hover {
    background-color: #e3f2fd;
}

#location_ids option:checked {
    background-color: #0d6efd;
    color: white;
    font-weight: 500;
}

.selection-summary {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
@endsection