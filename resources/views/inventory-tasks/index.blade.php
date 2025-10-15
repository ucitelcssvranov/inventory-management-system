@extends('layouts.app')

@section('title', 'Moje inventarizačné úlohy')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        
        <!-- Hlavička s celkovými štatistikami -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <i class="bi bi-list-task text-primary"></i> Moje inventarizačné úlohy
                </h1>
                <p class="text-muted mb-0">Inventarizačné plány pridelené komisiám, ktorých som členom</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card bg-primary text-white">
                            <div class="stats-icon">
                                <i class="bi bi-journals"></i>
                            </div>
                            <div class="stats-content">
                                <h4 class="stats-number">{{ $totalStats['total_plans'] }}</h4>
                                <p class="stats-label">Aktívnych plánov</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-success text-white">
                            <div class="stats-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="stats-content">
                                <h4 class="stats-number">{{ $totalStats['chairman_plans'] }}</h4>
                                <p class="stats-label">Predsedám</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-info text-white">
                            <div class="stats-icon">
                                <i class="bi bi-list-check"></i>
                            </div>
                            <div class="stats-content">
                                <h4 class="stats-number">{{ $totalStats['total_items'] }}</h4>
                                <p class="stats-label">Položiek celkom</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card bg-warning text-white">
                            <div class="stats-icon">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div class="stats-content">
                                <h4 class="stats-number">{{ $totalStats['overall_progress'] }}%</h4>
                                <p class="stats-label">Celkový pokrok</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($totalStats['total_items'] > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $totalStats['overall_progress'] }}%"
                                     aria-valuenow="{{ $totalStats['overall_progress'] }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ $totalStats['completed_items'] }} / {{ $totalStats['total_items'] }} dokončených
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Zoznam aktívnych plánov -->
        @if($activePlans->count() > 0)
            <div class="row">
                @foreach($activePlans as $plan)
                    @php
                        $stats = $planStats[$plan->id];
                        $isChairman = $stats['is_chairman'];
                    @endphp
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card inventory-plan-card h-100 {{ $isChairman ? 'border-primary' : 'border-secondary' }}">
                            <!-- Hlavička karty -->
                            <div class="card-header {{ $isChairman ? 'bg-primary text-white' : 'bg-light' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-folder-fill me-2"></i>
                                        {{ Str::limit($plan->name, 20) }}
                                    </h5>
                                    @if($isChairman)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-star-fill"></i> Predseda
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Obsah karty -->
                            <div class="card-body">
                                <!-- Základné informácie -->
                                <div class="mb-3">
                                    <div class="row text-sm">
                                        <div class="col-12 mb-2">
                                            <strong>Komisia:</strong> {{ $plan->commission->name }}
                                        </div>
                                        @if($plan->location)
                                        <div class="col-12 mb-2">
                                            <i class="bi bi-geo-alt text-muted me-1"></i>
                                            {{ $plan->location->name }}
                                        </div>
                                        @endif
                                        @if($plan->category)
                                        <div class="col-12 mb-2">
                                            <i class="bi bi-tags text-muted me-1"></i>
                                            {{ $plan->category->name }}
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status a pokrok -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge badge-status badge-{{ strtolower(str_replace('_', '-', $plan->status)) }}">
                                            {{ $plan->status_label }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $stats['progress_percentage'] }}% hotovo
                                        </small>
                                    </div>
                                    
                                    <div class="progress mb-2" style="height: 8px;">
                                        <div class="progress-bar 
                                                    @if($stats['progress_percentage'] >= 80) bg-success
                                                    @elseif($stats['progress_percentage'] >= 50) bg-warning  
                                                    @else bg-danger
                                                    @endif" 
                                             role="progressbar"
                                             style="width: {{ $stats['progress_percentage'] }}%"
                                             aria-valuenow="{{ $stats['progress_percentage'] }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="small text-muted">Dokončené</div>
                                            <strong class="text-success">{{ $stats['completed_items'] }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <div class="small text-muted">Zostáva</div>
                                            <strong class="text-warning">{{ $stats['remaining_items'] }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Termíny -->
                                @if($plan->date_start || $plan->date_end)
                                <div class="mb-3">
                                    <small class="text-muted">
                                        @if($plan->date_start)
                                            <i class="bi bi-calendar-event me-1"></i>
                                            Začiatok: {{ $plan->date_start->format('d.m.Y') }}
                                        @endif
                                        @if($plan->date_end)
                                            <br><i class="bi bi-calendar-check me-1"></i>
                                            Koniec: {{ $plan->date_end->format('d.m.Y') }}
                                        @endif
                                    </small>
                                </div>
                                @endif
                            </div>

                            <!-- Pätička s akciami -->
                            <div class="card-footer bg-transparent">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('inventory-tasks.show', $plan) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-play-fill me-1"></i>
                                        Začať inventarizáciu
                                    </a>
                                    
                                    @if($isChairman || auth()->user()->hasAdminPrivileges())
                                    <a href="{{ route('inventory_plans.show', $plan) }}" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-eye me-1"></i>
                                        Detail plánu
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Žiadne aktívne plány -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-4"></i>
                    <h4 class="text-muted">Žiadne aktívne úlohy</h4>
                    <p class="text-muted mb-4">
                        Momentálne nemáte pridelené žiadne inventarizačné úlohy.<br>
                        Kontaktujte správcu systému alebo počkajte na nové priradenia.
                    </p>
                    
                    @if($userCommissions->isEmpty())
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Nie ste členom žiadnej inventarizačnej komisie. Kontaktujte administrátora.
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Ste členom {{ $userCommissions->count() }} {{ $userCommissions->count() == 1 ? 'komisie' : 'komisií' }}, ale momentálne nie sú pridelené žiadne aktívne plány.
                        </div>
                    @endif

                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="bi bi-house me-1"></i>
                        Späť na dashboard
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.stats-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stats-icon {
    font-size: 2.5rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.stats-content {
    flex: 1;
}

.stats-number {
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
    line-height: 1;
}

.stats-label {
    margin: 0;
    font-size: 0.875rem;
    opacity: 0.9;
}

.inventory-plan-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.inventory-plan-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.badge-status {
    font-size: 0.75rem;
    padding: 0.4em 0.6em;
}

.badge-planned { background-color: #6c757d; }
.badge-approved { background-color: #17a2b8; }
.badge-assigned { background-color: #ffc107; color: #212529; }
.badge-in-progress { background-color: #007bff; }
.badge-completed { background-color: #28a745; }
.badge-signed { background-color: #6f42c1; }
.badge-archived { background-color: #495057; }

.text-sm {
    font-size: 0.875rem;
}
</style>
@endsection