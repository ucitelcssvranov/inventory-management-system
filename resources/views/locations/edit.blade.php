@extends('layouts.app')

@section('title', 'Upraviť lokáciu')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary me-3">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <h1 class="card-title mb-0">
                        <i class="bi bi-pencil"></i> Upraviť 
                        @if($location->type === 'budova')
                            budovu
                        @else
                            miestnosť
                        @endif
                    </h1>
                </div>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-exclamation-triangle"></i> Chyby vo formulári:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('locations.update', $location) }}">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" name="type" value="{{ $location->type }}">
                    @if($location->parent_id)
                        <input type="hidden" name="parent_id" value="{{ $location->parent_id }}">
                    @endif

                    @if($location->type === 'budova')
                        <!-- Formulár pre budovu -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Názov budovy *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $location->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Popis budovy</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $location->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <!-- Formulár pre miestnosť -->
                        <div class="mb-3">
                            <label class="form-label">Budova</label>
                            <input type="text" class="form-control" value="{{ $location->parent->name ?? 'Neurčená' }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="room_number" class="form-label">Číslo miestnosti *</label>
                            <input type="text" class="form-control @error('room_number') is-invalid @enderror"
                                   id="room_number" name="room_number" value="{{ old('room_number', $location->room_number) }}" required>
                            @error('room_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="room_description" class="form-label">Popis miestnosti</label>
                            <input type="text" class="form-control @error('room_description') is-invalid @enderror"
                                   id="room_description" name="room_description" value="{{ old('room_description', $location->room_description) }}">
                            @error('room_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="notes" class="form-label">Poznámky</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes', $location->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('locations.index') }}" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Zrušiť
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Uložiť zmeny
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection