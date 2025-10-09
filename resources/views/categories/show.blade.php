@extends('layouts.app')

@section('title', 'Detail kategórie')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-tag me-2"></i>Detail kategórie</h1>
        </div>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Názov</dt>
                    <dd class="col-sm-8">{{ $category->name }}</dd>
                    <dt class="col-sm-4">Popis</dt>
                    <dd class="col-sm-8">{{ $category->description }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
