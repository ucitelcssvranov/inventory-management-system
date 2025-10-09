@extends('layouts.app')

@section('title', 'Detail majetku')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-box-open me-2"></i>Detail majetku</h1>
        </div>
        <div class="card">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Inventárne číslo</dt>
                    <dd class="col-sm-8">{{ $asset->inventory_number }}</dd>
                    <dt class="col-sm-4">Názov</dt>
                    <dd class="col-sm-8">{{ $asset->name }}</dd>
                    <dt class="col-sm-4">Sériové číslo</dt>
                    <dd class="col-sm-8">{{ $asset->serial_number ?? '-' }}</dd>
                    <dt class="col-sm-4">Kategória</dt>
                    <dd class="col-sm-8">{{ $asset->category->name ?? '-' }}</dd>
                    <dt class="col-sm-4">Lokácia</dt>
                    <dd class="col-sm-8">{{ $asset->location->name ?? '-' }}</dd>
                    <dt class="col-sm-4">Dátum obstarania</dt>
                    <dd class="col-sm-8">{{ $asset->acquisition_date }}</dd>
                    <dt class="col-sm-4">Obstarávacia cena</dt>
                    <dd class="col-sm-8">{{ number_format($asset->acquisition_cost, 2) }} €</dd>
                    <dt class="col-sm-4">Zostatková hodnota</dt>
                    <dd class="col-sm-8">{{ number_format($asset->residual_value, 2) }} €</dd>
                    <dt class="col-sm-4">Stav</dt>
                    <dd class="col-sm-8">{{ $asset->status }}</dd>
                    <dt class="col-sm-4">Popis</dt>
                    <dd class="col-sm-8">{{ $asset->description }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
