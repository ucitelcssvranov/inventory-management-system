@extends('layouts.app')

@section('title', 'Dashboard komisií')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <i class="bi bi-diagram-3"></i> Dashboard inventarizačných komisií
                </h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="bi bi-people display-4 text-primary"></i>
                                <h4>{{ $globalStats['my_commissions'] }}</h4>
                                <p class="text-muted">Moje komisie</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="bi bi-person-badge display-4 text-success"></i>
                                <h4>{{ $globalStats['my_leading_commissions'] }}</h4>
                                <p class="text-muted">Predsedám</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="bi bi-list-check display-4 text-info"></i>
                                <h4>{{ $globalStats['total_plans'] }}</h4>
                                <p class="text-muted">Celkem plánov</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-clock display-4 text-warning"></i>
                                <h4>{{ $globalStats['active_plans'] }}</h4>
                                <p class="text-muted">Aktívne plány</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kommisie overview -->
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people-fill text-info"></i> Moje komisie
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($commissions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Názov komisie</th>
                                            <th>Rola</th>
                                            <th>Plány</th>
                                            <th>Aktívne</th>
                                            <th>Dokončené</th>
                                            <th>Akcie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($commissions as $commission)
                                            @php
                                                $stats = $commissionStats[$commission->id] ?? [];
                                                $isChairman = $commission->chairman_id == auth()->id();
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $commission->name }}</strong>
                                                    @if($commission->description)
                                                        <br><small class="text-muted">{{ Str::limit($commission->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($isChairman)
                                                        <span class="badge bg-primary">Predseda</span>
                                                    @else
                                                        <span class="badge bg-secondary">Člen</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ $stats['total_plans'] ?? 0 }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-warning">{{ $stats['active_plans'] ?? 0 }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">{{ $stats['completed_plans'] ?? 0 }}</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('inventory-commissions.show', $commission) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-people display-1"></i>
                                <h5>Nie ste členom žiadnej komisie</h5>
                                <p>Kontaktujte administrátora pre priradenie do komisie.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Current Tasks -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-task text-warning"></i> Aktuálne úlohy
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($currentTasks) > 0)
                            @foreach($currentTasks as $task)
                                <div class="border-start border-3 border-{{ $task['priority'] == 'high' ? 'danger' : ($task['priority'] == 'medium' ? 'warning' : 'info') }} ps-3 mb-3">
                                    <h6 class="mb-1">{{ $task['title'] }}</h6>
                                    <p class="text-muted small mb-1">{{ $task['description'] }}</p>
                                    <a href="{{ $task['url'] }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-right"></i> Otvoriť
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted">
                                <i class="bi bi-check-circle display-4"></i>
                                <p class="mb-0">Žiadne aktívne úlohy</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-activity text-success"></i> Najnovšie aktivity
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(count($recentActivities) > 0)
                            @foreach($recentActivities->take(5) as $activity)
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-circle-fill text-{{ $activity['type'] == 'plan_update' ? 'primary' : 'secondary' }}"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                        <p class="text-muted small mb-1">{{ $activity['description'] }}</p>
                                        <small class="text-muted">{{ $activity['timestamp']->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted">
                                <i class="bi bi-calendar-x display-4"></i>
                                <p class="mb-0">Žiadne aktivity</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection