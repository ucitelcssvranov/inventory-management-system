@extends('layouts.app')

@section('title', 'Upraviť nastavenia')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-pencil"></i> Upraviť nastavenia
                    </h1>
                    <p class="text-muted mb-0">Konfigurácia parametrov inventarizačného systému</p>
                </div>
                <div>
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Späť na nastavenia
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6><i class="bi bi-exclamation-triangle"></i> Chyby pri ukladaní:</h6>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Settings Form -->
            <form method="POST" action="{{ route('settings.update') }}" id="settingsForm">
                @csrf
                @method('PUT')

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
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="form-group">
                                                <label for="setting_{{ $setting->key }}" class="form-label fw-bold">
                                                    {{ $setting->label }}
                                                    @if($setting->validation_rules && in_array('required', $setting->validation_rules))
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                
                                                @if($setting->description)
                                                    <div class="form-text mb-2">{{ $setting->description }}</div>
                                                @endif

                                                @switch($setting->input_type)
                                                    @case('checkbox')
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" 
                                                                   class="form-check-input" 
                                                                   id="setting_{{ $setting->key }}" 
                                                                   name="settings[{{ $setting->key }}]"
                                                                   value="1"
                                                                   {{ $setting->value ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="setting_{{ $setting->key }}">
                                                                {{ $setting->value ? 'Zapnuté' : 'Vypnuté' }}
                                                            </label>
                                                        </div>
                                                        @break

                                                    @case('select')
                                                        <select class="form-select" 
                                                                id="setting_{{ $setting->key }}" 
                                                                name="settings[{{ $setting->key }}]">
                                                            @if($setting->options)
                                                                @foreach($setting->options as $optionValue => $optionLabel)
                                                                    <option value="{{ $optionValue }}" 
                                                                            {{ $setting->value == $optionValue ? 'selected' : '' }}>
                                                                        {{ $optionLabel }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        @break

                                                    @case('textarea')
                                                        <textarea class="form-control" 
                                                                  id="setting_{{ $setting->key }}" 
                                                                  name="settings[{{ $setting->key }}]"
                                                                  rows="{{ $setting->type === 'json' ? 6 : 3 }}"
                                                                  placeholder="{{ $setting->description }}">{{ old("settings.{$setting->key}", $setting->value) }}</textarea>
                                                        @if($setting->type === 'json')
                                                            <div class="form-text">
                                                                <i class="bi bi-info-circle"></i> 
                                                                Zadajte platný JSON formát
                                                            </div>
                                                        @endif
                                                        @break

                                                    @case('number')
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="setting_{{ $setting->key }}" 
                                                               name="settings[{{ $setting->key }}]"
                                                               value="{{ old("settings.{$setting->key}", $setting->value) }}"
                                                               placeholder="{{ $setting->description }}">
                                                        @break

                                                    @default
                                                        <input type="text" 
                                                               class="form-control" 
                                                               id="setting_{{ $setting->key }}" 
                                                               name="settings[{{ $setting->key }}]"
                                                               value="{{ old("settings.{$setting->key}", $setting->value) }}"
                                                               placeholder="{{ $setting->description }}">
                                                @endswitch

                                                @error("settings.{$setting->key}")
                                                    <div class="text-danger small mt-1">
                                                        <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                                    </div>
                                                @enderror

                                                <div class="form-text mt-1">
                                                    <small class="text-muted">
                                                        Kľúč: <code>{{ $setting->key }}</code>
                                                        | Typ: <span class="badge bg-light text-dark">{{ $setting->type }}</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-lg"></i> Uložiť nastavenia
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="resetForm()">
                                        <i class="bi bi-arrow-clockwise"></i> Resetovať
                                    </button>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Polia označené <span class="text-danger">*</span> sú povinné
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-gear display-4 text-muted"></i>
                            <h4 class="mt-3">Žiadne editovateľné nastavenia</h4>
                            <p class="text-muted">V systéme nie sú definované žiadne editovateľné nastavenia.</p>
                            <a href="{{ route('settings.index') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Späť na nastavenia
                            </a>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function resetForm() {
    if (confirm('Naozaj chcete resetovať všetky zmeny?')) {
        document.getElementById('settingsForm').reset();
    }
}

// Auto-save functionality (optional)
let autoSaveTimeout;
document.querySelectorAll('input, select, textarea').forEach(element => {
    element.addEventListener('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // Visual indication that changes are pending
            const saveButton = document.querySelector('button[type="submit"]');
            if (saveButton && !saveButton.classList.contains('btn-warning')) {
                saveButton.classList.remove('btn-primary');
                saveButton.classList.add('btn-warning');
                saveButton.innerHTML = '<i class="bi bi-clock"></i> Neuložené zmeny';
            }
        }, 1000);
    });
});

// Reset save button when form is submitted
document.getElementById('settingsForm').addEventListener('submit', function() {
    const saveButton = document.querySelector('button[type="submit"]');
    saveButton.classList.remove('btn-warning');
    saveButton.classList.add('btn-primary');
    saveButton.innerHTML = '<i class="bi bi-check-lg"></i> Ukladá sa...';
    saveButton.disabled = true;
});

// Switch label updates
document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const label = this.nextElementSibling;
        if (label) {
            label.textContent = this.checked ? 'Zapnuté' : 'Vypnuté';
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.form-group {
    margin-bottom: 1.5rem;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.btn-warning {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

textarea[name*="json"] {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}
</style>
@endpush