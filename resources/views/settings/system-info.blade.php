@extends('layouts.app')

@section('title', 'Systémové informácie')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-info-circle"></i> Systémové informácie
                    </h1>
                    <p class="text-muted mb-0">Technické informácie o inventarizačnom systéme</p>
                </div>
                <div>
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Späť na nastavenia
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Application Info -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-app"></i> Aplikácia
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Laravel verzia:</td>
                                        <td><code>{{ $info['laravel_version'] }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Prostredie:</td>
                                        <td>
                                            <span class="badge bg-{{ $info['environment'] === 'production' ? 'success' : ($info['environment'] === 'local' ? 'info' : 'warning') }}">
                                                {{ ucfirst($info['environment']) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Debug mód:</td>
                                        <td>
                                            <span class="badge bg-{{ $info['debug_mode'] ? 'warning' : 'success' }}">
                                                {{ $info['debug_mode'] ? 'Zapnutý' : 'Vypnutý' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Časová zóna:</td>
                                        <td><code>{{ $info['timezone'] }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Lokalizácia:</td>
                                        <td><code>{{ $info['locale'] }}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Server Info -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-server"></i> Server
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">PHP verzia:</td>
                                        <td><code>{{ $info['php_version'] }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Memory limit:</td>
                                        <td><code>{{ $info['memory_limit'] }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Max execution time:</td>
                                        <td><code>{{ $info['max_execution_time'] }}s</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Max upload size:</td>
                                        <td><code>{{ $info['upload_max_filesize'] }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Post max size:</td>
                                        <td><code>{{ $info['post_max_size'] }}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Database Info -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-database"></i> Databáza
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Typ databázy:</td>
                                        <td><code>{{ strtoupper($info['database_type']) }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Stav pripojenia:</td>
                                        <td>
                                            @php
                                                try {
                                                    DB::connection()->getDatabaseName();
                                                    $dbStatus = 'connected';
                                                } catch (\Exception $e) {
                                                    $dbStatus = 'error';
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $dbStatus === 'connected' ? 'success' : 'danger' }}">
                                                {{ $dbStatus === 'connected' ? 'Pripojené' : 'Chyba pripojenia' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Počet tabuliek:</td>
                                        <td>
                                            @php
                                                try {
                                                    $tables = DB::select('SHOW TABLES');
                                                    $tableCount = count($tables);
                                                } catch (\Exception $e) {
                                                    $tableCount = 'N/A';
                                                }
                                            @endphp
                                            <code>{{ $tableCount }}</code>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- System Stats -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bar-chart"></i> Štatistiky systému
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tbody>
                                    @php
                                        try {
                                            $assetCount = \App\Models\Asset::count();
                                            $userCount = \App\Models\User::count();
                                            $planCount = \App\Models\InventoryPlan::count();
                                            $locationCount = \App\Models\Location::count();
                                        } catch (\Exception $e) {
                                            $assetCount = $userCount = $planCount = $locationCount = 'N/A';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="fw-bold">Počet majetku:</td>
                                        <td><code>{{ $assetCount }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Počet používateľov:</td>
                                        <td><code>{{ $userCount }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Počet plánov:</td>
                                        <td><code>{{ $planCount }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Počet lokácií:</td>
                                        <td><code>{{ $locationCount }}</code></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Aktuálny čas:</td>
                                        <td><code>{{ now()->format('d.m.Y H:i:s') }}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cache Info -->
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-lightning"></i> Cache a výkon
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-primary">
                                            @php
                                                $cacheDriver = config('cache.default');
                                            @endphp
                                            {{ ucfirst($cacheDriver) }}
                                        </div>
                                        <small class="text-muted">Cache driver</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-info">
                                            @php
                                                $queueDriver = config('queue.default');
                                            @endphp
                                            {{ ucfirst($queueDriver) }}
                                        </div>
                                        <small class="text-muted">Queue driver</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-success">
                                            @php
                                                $sessionDriver = config('session.driver');
                                            @endphp
                                            {{ ucfirst($sessionDriver) }}
                                        </div>
                                        <small class="text-muted">Session driver</small>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-warning">
                                            @php
                                                $mailDriver = config('mail.default');
                                            @endphp
                                            {{ ucfirst($mailDriver) }}
                                        </div>
                                        <small class="text-muted">Mail driver</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-tools"></i> Systémové akcie
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="clearCache()">
                                        <i class="bi bi-arrow-clockwise"></i> Vymazať cache
                                    </button>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('settings.export') }}" class="btn btn-outline-info w-100">
                                        <i class="bi bi-download"></i> Export nastavení
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <button type="button" class="btn btn-outline-secondary w-100" onclick="refreshPage()">
                                        <i class="bi bi-arrow-clockwise"></i> Obnoviť stránku
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                'Accept': 'application/json'
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

function refreshPage() {
    location.reload();
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
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0;
}

.table-borderless td:first-child {
    width: 40%;
}

code {
    background-color: #f8f9fa;
    color: #e83e8c;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}

.badge {
    font-size: 0.8em;
}
</style>
@endpush