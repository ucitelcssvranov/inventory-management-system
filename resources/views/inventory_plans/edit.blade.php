@extends('layouts.app')

@section('title', 'Upraviť inventúrny plán')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-pencil-square me-2"></i>Upraviť inventúrny plán</h2>
            <a href="{{ route('inventory_plans.show', $inventoryPlan) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Späť na detail
            </a>
        </div>

        <form method="POST" action="{{ route('inventory_plans.update', $inventoryPlan) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Názov plánu</label>
                <input type="text" name="name" id="name" 
                       class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name', $inventoryPlan->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Popis</label>
                <textarea class="form-control @error('description') is-invalid @enderror"
                          id="description" name="description" rows="3">{{ old('description', $inventoryPlan->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Typ inventúry</label>
                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                    <option value="">Vyberte typ inventúry</option>
                    <option value="fyzická" {{ old('type', $inventoryPlan->type) == 'fyzická' ? 'selected' : '' }}>
                        Fyzická inventúra
                    </option>
                    <option value="dokladová" {{ old('type', $inventoryPlan->type) == 'dokladová' ? 'selected' : '' }}>
                        Dokladová inventúra
                    </option>
                    <option value="kombinovaná" {{ old('type', $inventoryPlan->type) == 'kombinovaná' ? 'selected' : '' }}>
                        Kombinovaná inventúra
                    </option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="date_start" class="form-label">Dátum začatia inventúry</label>
                        <input type="date" name="date_start" id="date_start" 
                               class="form-control @error('date_start') is-invalid @enderror" 
                               value="{{ old('date_start', $inventoryPlan->date_start ? $inventoryPlan->date_start->format('Y-m-d') : '') }}" required>
                        @error('date_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="date_end" class="form-label">Dátum ukončenia inventúry</label>
                        <input type="date" name="date_end" id="date_end" 
                               class="form-control @error('date_end') is-invalid @enderror" 
                               value="{{ old('date_end', $inventoryPlan->date_end ? $inventoryPlan->date_end->format('Y-m-d') : '') }}" required>
                        @error('date_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="inventory_day" class="form-label">Deň inventúry</label>
                        <input type="date" name="inventory_day" id="inventory_day" 
                               class="form-control @error('inventory_day') is-invalid @enderror" 
                               value="{{ old('inventory_day', $inventoryPlan->inventory_day ? $inventoryPlan->inventory_day->format('Y-m-d') : '') }}" required>
                        @error('inventory_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="date" class="form-label">Dátum plánu</label>
                <input type="date" name="date" id="date" 
                       class="form-control @error('date') is-invalid @enderror" 
                       value="{{ old('date', $inventoryPlan->date ? $inventoryPlan->date->format('Y-m-d') : '') }}" required>
                @error('date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="unit_name" class="form-label">Názov účtovnej jednotky</label>
                <input type="text" name="unit_name" id="unit_name" 
                       class="form-control @error('unit_name') is-invalid @enderror" 
                       value="{{ old('unit_name', $inventoryPlan->unit_name) }}" required>
                @error('unit_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="unit_address" class="form-label">Adresa účtovnej jednotky</label>
                <input type="text" name="unit_address" id="unit_address" 
                       class="form-control @error('unit_address') is-invalid @enderror" 
                       value="{{ old('unit_address', $inventoryPlan->unit_address) }}" required>
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
                        <option value="{{ $user->id }}" {{ old('responsible_person_id', $inventoryPlan->responsible_person_id) == $user->id ? 'selected' : '' }}>
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
                        <option value="{{ $category->id }}" {{ old('plan_category_id', $inventoryPlan->category_id) == $category->id ? 'selected' : '' }}>
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
                        <option value="{{ $commission->id }}" {{ old('commission_id', $inventoryPlan->commission_id) == $commission->id ? 'selected' : '' }}>
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
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="location_ids" class="form-label">Lokácie plánu <span class="text-danger">*</span></label>
                                <select name="location_ids[]" id="location_ids" 
                                        class="form-select @error('location_ids') is-invalid @enderror" 
                                        multiple size="8" required>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" 
                                                {{ collect(old('location_ids', $inventoryPlan->locations->pluck('id')->toArray()))->contains($location->id) ? 'selected' : '' }}>
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
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('inventory_plans.show', $inventoryPlan) }}" class="btn btn-secondary me-2">
                    <i class="bi bi-x-circle me-1"></i>Zrušiť
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Uložiť zmeny
                </button>
            </div>
        </form>
    </div>
</div>
@endsection