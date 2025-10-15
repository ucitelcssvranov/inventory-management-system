@extends('layouts.app')

@section('title', 'Nastavenia systému')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-gear"></i> Nastavenia systému
                    </h1>
                    <p class="text-muted mb-0">Konfigurácia inventarizačného systému</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('settings.edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Upraviť nastavenia
                    </a>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i> Ďalšie akcie
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('settings.system-info') }}">
                                <i class="bi bi-info-circle"></i> Systémové informácie
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('settings.export') }}">
                                <i class="bi bi-download"></i> Export nastavení
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="clearCache()">
                                <i class="bi bi-arrow-clockwise"></i> Vymazať cache
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Settings Groups -->
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
                                        <div class="setting-item">
                                            <h6 class="fw-bold mb-1">{{ $setting['label'] }}</h6>
                                            @if($setting['description'])
                                                <p class="text-muted small mb-2">{{ $setting['description'] }}</p>
                                            @endif
                                            <div class="setting-value">
                                                @if($setting['type'] === 'boolean')
                                                    <span class="badge bg-{{ $setting['cast_value'] ? 'success' : 'secondary' }}">
                                                        {{ $setting['cast_value'] ? 'Zapnuté' : 'Vypnuté' }}
                                                    </span>
                                                @elseif($setting['type'] === 'select' && isset($setting['options']))
                                                    <span class="badge bg-info">
                                                        {{ $setting['options'][$setting['value']] ?? $setting['value'] }}
                                                    </span>
                                                @else
                                                    <code class="bg-light px-2 py-1 rounded">{{ $setting['value'] }}</code>
                                                @endif
                                            </div>
                                            <div class="setting-meta mt-2">
                                                <small class="text-muted">
                                                    Kľúč: <code>{{ $setting['key'] }}</code>
                                                    @if(!$setting['is_editable'])
                                                        <span class="badge bg-warning ms-1">Len na čítanie</span>
                                                    @endif
                                                    @if($setting['is_public'])
                                                        <span class="badge bg-info ms-1">Verejné</span>
                                                    @endif
                                                </small>
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
                        <i class="bi bi-gear display-4 text-muted"></i>
                        <h4 class="mt-3">Žiadne nastavenia</h4>
                        <p class="text-muted">V systéme nie sú definované žiadne nastavenia.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function clearCache() {
    if (confirm('Naozaj chcete vymazať cache systému?')) {
        fetch('{{ route("settings.clear-cache") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Cache bol úspešne vymazaný.');
            } else {
                showAlert('error', data.message || 'Chyba pri mazaní cache.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Nastala chyba pri komunikácii so serverom.');
        });
    }
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
.setting-item {
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
    height: 100%;
}

.setting-value {
    margin-bottom: 0.5rem;
}

.setting-meta {
    border-top: 1px solid #dee2e6;
    padding-top: 0.5rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
@endpush