@extends('layouts.app')

@section('title', 'Detail inventúrneho plánu')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('inventory_plans.index') }}" class="btn btn-outline-secondary me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1><i class="fas fa-clipboard-list me-2"></i>Detail inventúrneho plánu</h1>
        </div>
        
        {{-- Základné informácie --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Základné informácie</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Názov</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->name }}</dd>
                    
                    <dt class="col-sm-4">Popis</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->description ?? '-' }}</dd>
                    
                    <dt class="col-sm-4">Typ inventarizácie</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-info">{{ $inventoryPlan->type_label }}</span>
                    </dd>
                    
                    <dt class="col-sm-4">Stav</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-{{ $inventoryPlan->status_color }}">
                            {{ $inventoryPlan->status_label }}
                        </span>
                    </dd>
                    
                    <dt class="col-sm-4">Dátum začiatku</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->date_start ? $inventoryPlan->date_start->format('d.m.Y') : '-' }}</dd>
                    
                    <dt class="col-sm-4">Dátum ukončenia</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->date_end ? $inventoryPlan->date_end->format('d.m.Y') : '-' }}</dd>
                    
                    <dt class="col-sm-4">Deň inventúry</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->inventory_day ? $inventoryPlan->inventory_day->format('d.m.Y') : '-' }}</dd>
                    
                    <dt class="col-sm-4">Lokácie</dt>
                    <dd class="col-sm-8">
                        @if($inventoryPlan->locations->count() > 0)
                            @foreach($inventoryPlan->locations as $location)
                                <span class="badge bg-primary me-1">{{ $location->name }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </dd>
                    
                    <dt class="col-sm-4">Kategória</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->category->name ?? '-' }}</dd>
                    
                    <dt class="col-sm-4">Zodpovedná osoba</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->responsiblePerson->name ?? '-' }}</dd>
                    
                    <dt class="col-sm-4">Vytvoril</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->createdBy->name ?? '-' }}</dd>
                    
                    <dt class="col-sm-4">Dátum vytvorenia</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->created_at->format('d.m.Y H:i') }}</dd>
                </dl>
            </div>
        </div>

        {{-- Komisia --}}
        @if($inventoryPlan->commission)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Priradená komisia</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Názov komisie</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->commission->name }}</dd>
                    
                    <dt class="col-sm-4">Predseda</dt>
                    <dd class="col-sm-8">{{ $inventoryPlan->commission->chairman->name ?? '-' }}</dd>
                    
                    <dt class="col-sm-4">Členovia</dt>
                    <dd class="col-sm-8">
                        @if($inventoryPlan->commission->members->count() > 0)
                            @foreach($inventoryPlan->commission->members as $member)
                                <span class="badge bg-secondary me-1">{{ $member->name }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
        @endif

        {{-- Položky plánu --}}
        @if($inventoryPlan->items->count() > 0)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Položky inventúrneho plánu</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Inventárne číslo</th>
                                <th>Názov</th>
                                <th>Kategória</th>
                                <th>Lokácia</th>
                                <th>Komisia</th>
                                <th>Stav</th>
                                <th>Počítania</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventoryPlan->items->take(20) as $item)
                            <tr>
                                <td>{{ $item->asset->inventory_number ?? '-' }}</td>
                                <td>{{ $item->asset->name ?? '-' }}</td>
                                <td>{{ $item->asset->category->name ?? '-' }}</td>
                                <td>{{ $item->asset->location->name ?? '-' }}</td>
                                <td>{{ $item->commission->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item->assignment_status_label }}</span>
                                </td>
                                <td>{{ $item->counts->count() }}</td>
                            </tr>
                            @endforeach
                            @if($inventoryPlan->items->count() > 20)
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    ... a ďalších {{ $inventoryPlan->items->count() - 20 }} položiek
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    {{-- Štatistiky --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Štatistiky</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Celkový počet položiek:</span>
                        <strong>{{ $stats['total_items'] ?? 0 }}</strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Priradené položky:</span>
                        <strong>{{ $stats['assigned_items'] ?? 0 }}</strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Dokončené položky:</span>
                        <strong>{{ $stats['completed_items'] ?? 0 }}</strong>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Komisie:</span>
                        <strong>{{ $stats['total_commissions'] ?? 0 }}</strong>
                    </div>
                </div>
                
                {{-- Pokrok --}}
                @if(($stats['total_items'] ?? 0) > 0)
                <div class="mt-4">
                    <h6>Pokrok inventarizácie</h6>
                    @php
                        $progress = round((($stats['completed_items'] ?? 0) / ($stats['total_items'] ?? 1)) * 100, 1);
                    @endphp
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $progress }}%
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Akcie --}}
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Akcie</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @can('update', $inventoryPlan)
                    <a href="{{ route('inventory_plans.edit', $inventoryPlan) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Upraviť
                    </a>
                    @endcan
                    
                    @if($inventoryPlan->canBeAssigned())
                    <a href="{{ route('inventory-plans.assign-commission', $inventoryPlan) }}" class="btn btn-info">
                        <i class="fas fa-users"></i> Priradiť komisiu
                    </a>
                    @endif
                    
                    <a href="{{ route('inventory_plans.export.soupis.pdf', $inventoryPlan) }}" class="btn btn-secondary">
                        <i class="fas fa-file-pdf"></i> Export súpisu (PDF)
                    </a>
                    
                    <a href="{{ route('inventory_plans.export.soupis.xlsx', $inventoryPlan) }}" class="btn btn-secondary">
                        <i class="fas fa-file-excel"></i> Export súpisu (XLSX)
                    </a>
                    
                    <a href="{{ route('inventory_plans.export.zapis.pdf', $inventoryPlan) }}" class="btn btn-secondary">
                        <i class="fas fa-file-pdf"></i> Export zápisu (PDF)
                    </a>
                    
                    <a href="{{ route('inventory_plans.export.zapis.xlsx', $inventoryPlan) }}" class="btn btn-secondary">
                        <i class="fas fa-file-excel"></i> Export zápisu (XLSX)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
