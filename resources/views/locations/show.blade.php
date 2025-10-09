@extends('layouts.app')

@section('title', 'Detail lokácie')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-map-marker-alt me-2"></i>Detail lokácie</h1>
        </div>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Názov</dt>
                    <dd class="col-sm-8">{{ $location->name }}</dd>
                    <dt class="col-sm-4">Budova</dt>
                    <dd class="col-sm-8">{{ $location->building }}</dd>
                    <dt class="col-sm-4">Miestnosť</dt>
                    <dd class="col-sm-8">{{ $location->room }}</dd>
                    <dt class="col-sm-4">Popis</dt>
                    <dd class="col-sm-8">{{ $location->description }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
