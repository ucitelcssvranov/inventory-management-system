@extends('layouts.app')

@section('title', 'Upraviť inventúrny plán')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('inventory_plans.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-edit me-2"></i>Upraviť inventúrny plán</h1>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('inventory_plans.update', $inventoryPlan) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Názov *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $inventoryPlan->name) }}" required>
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
                        <label for="planned_date" class="form-label">Plánované dátum</label>
                        <input type="date" class="form-control @error('planned_date') is-invalid @enderror"
                               id="planned_date" name="planned_date" value="{{ old('planned_date', $inventoryPlan->planned_date) }}">
                        @error('planned_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="location_id" class="form-label">Lokácia</label>
                        <select class="form-select @error('location_id') is-invalid @enderror"
                                id="location_id" name="location_id">
                            <option value="">Vyberte lokáciu</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ (old('location_id', $inventoryPlan->location_id) == $location->id) ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Kategória</label>
                        <select class="form-select @error('category_id') is-invalid @enderror"
                                id="category_id" name="category_id">
                            <option value="">Vyberte kategóriu</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (old('category_id', $inventoryPlan->category_id) == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Typ inventúry</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="fyzická" @if($inventoryPlan->type == 'fyzická') selected @endif>Fyzická</option>
                            <option value="dokladová" @if($inventoryPlan->type == 'dokladová') selected @endif>Dokladová</option>
                            <option value="kombinovaná" @if($inventoryPlan->type == 'kombinovaná') selected @endif>Kombinovaná</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_start" class="form-label">Dátum začatia inventúry</label>
                        <input type="date" name="date_start" id="date_start" class="form-control" value="{{ $inventoryPlan->date_start }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="date_end" class="form-label">Dátum ukončenia inventúry</label>
                        <input type="date" name="date_end" id="date_end" class="form-control" value="{{ $inventoryPlan->date_end }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="inventory_day" class="form-label">Deň inventúry</label>
                        <input type="date" name="inventory_day" id="inventory_day" class="form-control" value="{{ $inventoryPlan->inventory_day }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="unit_name" class="form-label">Názov účtovnej jednotky</label>
                        <input type="text" name="unit_name" id="unit_name" class="form-control" value="{{ $inventoryPlan->unit_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="unit_address" class="form-label">Adresa účtovnej jednotky</label>
                        <input type="text" name="unit_address" id="unit_address" class="form-control" value="{{ $inventoryPlan->unit_address }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="storage_place" class="form-label">Miesto uloženia</label>
                        <input type="text" name="storage_place" id="storage_place" class="form-control" value="{{ $inventoryPlan->storage_place }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="responsible_person_id" class="form-label">Hmotne zodpovedná osoba</label>
                        <select name="responsible_person_id" id="responsible_person_id" class="form-select" required>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @if($inventoryPlan->responsible_person_id == $user->id) selected @endif>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('inventory_plans.index') }}" class="btn btn-secondary me-2">Zrušiť</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Uložiť
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection