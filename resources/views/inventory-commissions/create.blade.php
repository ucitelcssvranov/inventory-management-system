@extends('layouts.app')

@section('title', 'Pridať inventarizačnú komisiu')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('inventory-commissions.index') }}" class="text-decoration-none">
                        <i class="bi bi-people"></i> Inventarizačné komisie
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Pridať komisiu</li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <i class="bi bi-plus-circle"></i> Pridať inventarizačnú komisiu
                </h1>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('inventory-commissions.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Názov komisie <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required
                                       placeholder="Napr. Inventarizačná komisia - informatika">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="chairman_id" class="form-label">Predseda komisie <span class="text-danger">*</span></label>
                                <select class="form-select @error('chairman_id') is-invalid @enderror" id="chairman_id" name="chairman_id" required>
                                    <option value="">Vyberte predsedu...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('chairman_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('chairman_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Popis komisie</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Voliteľný popis účelu a zodpovedností komisie...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Členovia komisie</label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            @foreach($users as $user)
                                <div class="form-check">
                                    <input class="form-check-input member-checkbox" type="checkbox" name="members[]" 
                                           value="{{ $user->id }}" id="member_{{ $user->id }}"
                                           {{ in_array($user->id, old('members', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="member_{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->email }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Predseda sa automaticky nepridáva medzi členov. Členovia sú okrem predsedu ďalší používatelia komisie.
                        </small>
                        @error('members')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @error('members.*')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('inventory-commissions.index') }}" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Zrušiť
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Vytvoriť komisiu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Disable chairman from being selected as member
document.getElementById('chairman_id').addEventListener('change', function() {
    const chairmanId = this.value;
    const memberCheckboxes = document.querySelectorAll('.member-checkbox');
    
    memberCheckboxes.forEach(checkbox => {
        if (checkbox.value === chairmanId) {
            checkbox.checked = false;
            checkbox.disabled = chairmanId !== '';
        } else {
            checkbox.disabled = false;
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const chairmanSelect = document.getElementById('chairman_id');
    if (chairmanSelect.value) {
        chairmanSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
