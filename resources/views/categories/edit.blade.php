@extends('layouts.app')

@section('title', 'Upraviť kategóriu')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-edit me-2"></i>Upraviť kategóriu</h1>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Názov *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $category->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Popis</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary me-2">Zrušiť</a>
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