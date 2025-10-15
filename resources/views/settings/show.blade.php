@extends('layouts.app')

@section('title', 'Nastavenia systému')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="mb-4">
                <h1 class="h3 mb-0">
                    <i class="bi bi-info-circle"></i> Informácie o systéme
                </h1>
                <p class="text-muted mb-0">Základné nastavenia a informácie o inventarizačnom systéme</p>
            </div>

            <!-- Public Settings -->
            @if(!empty($settings))
                @foreach($settings as $groupName => $groupSettings)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-{{ \App\Services\SettingsService::getGroupIcon($groupName) }}"></i>
                                {{ \App\Services\SettingsService::getGroupTitle($groupName) }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($groupSettings as $setting)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="info-item">
                                            <h6 class="fw-bold mb-1 text-primary">{{ $setting['label'] }}</h6>
                                            @if($setting['description'])
                                                <p class="text-muted small mb-2">{{ $setting['description'] }}</p>
                                            @endif
                                            <div class="info-value">
                                                @if($setting['type'] === 'boolean')
                                                    <span class="badge bg-{{ $setting['cast_value'] ? 'success' : 'secondary' }} fs-6">
                                                        <i class="bi bi-{{ $setting['cast_value'] ? 'check-circle' : 'x-circle' }}"></i>
                                                        {{ $setting['cast_value'] ? 'Zapnuté' : 'Vypnuté' }}
                                                    </span>
                                                @elseif($setting['type'] === 'select' && isset($setting['options']))
                                                    <span class="badge bg-info fs-6">
                                                        {{ $setting['options'][$setting['value']] ?? $setting['value'] }}
                                                    </span>
                                                @else
                                                    <div class="value-display">
                                                        {{ $setting['value'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-info-circle display-4 text-muted"></i>
                        <h4 class="mt-3">Žiadne verejné informácie</h4>
                        <p class="text-muted">V systéme nie sú definované žiadne verejné nastavenia.</p>
                    </div>
                </div>
            @endif

            <!-- System Status -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-activity"></i> Stav systému
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-success me-2"></div>
                                <div>
                                    <div class="fw-bold">Systém</div>
                                    <small class="text-muted">Online</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-success me-2"></div>
                                <div>
                                    <div class="fw-bold">Databáza</div>
                                    <small class="text-muted">Pripojená</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-warning me-2"></div>
                                <div>
                                    <div class="fw-bold">Cache</div>
                                    <small class="text-muted">Aktívny</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator bg-info me-2"></div>
                                <div>
                                    <div class="fw-bold">Verzia</div>
                                    <small class="text-muted">{{ config('app.version', '1.0.0') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.info-item {
    padding: 1rem;
    border: 1px solid #e3f2fd;
    border-radius: 0.375rem;
    background-color: #fafafa;
    height: 100%;
    border-left: 4px solid #2196f3;
}

.info-value {
    margin-bottom: 0.5rem;
}

.value-display {
    font-size: 1.1em;
    font-weight: 500;
    color: #333;
    padding: 0.25rem 0.5rem;
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    display: inline-block;
    min-width: 120px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.badge {
    font-size: 0.9em;
}
</style>
@endpush