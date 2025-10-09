@extends('layouts.app')

@section('title', $location->name . ' - Miestnosti')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Navigácia -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('locations.index') }}">
                            <i class="fas fa-building"></i> Budovy
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        <i class="fas fa-door-open"></i> {{ $location->name }}
                    </li>
                </ol>
            </nav>

            <!-- Header budovy -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2 editable-title" 
                                data-location-id="{{ $location->id }}" 
                                data-field="name" 
                                data-value="{{ $location->name }}">
                                <i class="fas fa-building text-primary me-2"></i>
                                {{ $location->name }}
                            </h1>
                            @if($location->notes)
                                <div class="editable-notes text-muted mb-0" 
                                     data-location-id="{{ $location->id }}" 
                                     data-field="notes" 
                                     data-value="{{ $location->notes }}">
                                    <i class="fas fa-sticky-note"></i>
                                    {{ $location->notes }}
                                </div>
                            @else
                                <div class="editable-notes text-muted mb-0 fst-italic" 
                                     data-location-id="{{ $location->id }}" 
                                     data-field="notes" 
                                     data-value="">
                                    <i class="fas fa-sticky-note"></i>
                                    Kliknite pre pridanie poznámky...
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group">
                                <button class="btn btn-outline-primary edit-title-btn" data-location-id="{{ $location->id }}">
                                    <i class="fas fa-edit"></i> Upraviť budovu
                                </button>
                                <a href="{{ route('locations.edit', $location) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-cog"></i> Úplná editácia
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-door-open text-primary me-2"></i>
                                <strong>{{ $miestnosti->count() }}</strong>
                                <span class="ms-1">miestností</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-boxes text-success me-2"></i>
                                <strong>{{ $location->assets_count }}</strong>
                                <span class="ms-1">kusov majetku</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            @if($location->createdBy)
                                <small class="text-muted">
                                    Vytvoril: {{ $location->createdBy->name }} 
                                    ({{ $location->created_at->format('d.m.Y') }})
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Miestnosti -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">
                    <i class="fas fa-door-closed me-2"></i>
                    Miestnosti
                </h2>
                <div>
                    <a href="{{ route('locations.create') }}?parent_id={{ $location->id }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Pridať miestnosť
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                @forelse($miestnosti as $miestnost)
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2 editable-room-number" 
                                          data-location-id="{{ $miestnost->id }}" 
                                          data-field="room_number" 
                                          data-value="{{ $miestnost->room_number }}">
                                        {{ $miestnost->room_number }}
                                    </span>
                                    @if($miestnost->assets_count > 0)
                                        <span class="badge bg-success">{{ $miestnost->assets_count }}</span>
                                    @endif
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link p-0" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('locations.show', $miestnost) }}">
                                            <i class="fas fa-eye"></i> Detail
                                        </a></li>
                                        <li><a class="dropdown-item edit-room-btn" href="#" data-location-id="{{ $miestnost->id }}">
                                            <i class="fas fa-edit"></i> Rýchla editácia
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('locations.edit', $miestnost) }}">
                                            <i class="fas fa-cog"></i> Úplná editácia
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('locations.destroy', $miestnost) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash"></i> Vymazať
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <div class="editable-room-description mb-2" 
                                     data-location-id="{{ $miestnost->id }}" 
                                     data-field="room_description" 
                                     data-value="{{ $miestnost->room_description ?? '' }}">
                                    @if($miestnost->room_description)
                                        <strong>{{ $miestnost->room_description }}</strong>
                                    @else
                                        <em class="text-muted">Kliknite pre pridanie popisu...</em>
                                    @endif
                                </div>
                                
                                @if($miestnost->notes)
                                    <div class="editable-notes" 
                                         data-location-id="{{ $miestnost->id }}" 
                                         data-field="notes" 
                                         data-value="{{ $miestnost->notes }}">
                                        <small class="text-muted">
                                            <i class="fas fa-sticky-note"></i>
                                            {{ $miestnost->notes }}
                                        </small>
                                    </div>
                                @else
                                    <div class="editable-notes" 
                                         data-location-id="{{ $miestnost->id }}" 
                                         data-field="notes" 
                                         data-value="">
                                        <small class="text-muted fst-italic">
                                            <i class="fas fa-sticky-note"></i>
                                            Poznámka...
                                        </small>
                                    </div>
                                @endif
                            </div>
                            
                            @if($miestnost->assets_count > 0)
                                <div class="card-footer bg-transparent">
                                    <small class="text-muted">
                                        <i class="fas fa-boxes"></i>
                                        {{ $miestnost->assets_count }} kusov majetku
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-door-closed fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Žiadne miestnosti</h4>
                            <p class="text-muted">V tejto budove zatiaľ nie sú definované žiadne miestnosti.</p>
                            <a href="{{ route('locations.create') }}?parent_id={{ $location->id }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Pridať prvú miestnosť
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inline editácia budovy
    document.querySelectorAll('.edit-title-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const locationId = this.dataset.locationId;
            const titleElement = document.querySelector(`[data-location-id="${locationId}"][data-field="name"]`);
            makeEditable(titleElement);
        });
    });

    // Rýchla editácia miestnosti
    document.querySelectorAll('.edit-room-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const locationId = this.dataset.locationId;
            openRoomEditModal(locationId);
        });
    });

    // Inline editácia všetkých editovateľných prvkov
    document.querySelectorAll('.editable-notes, .editable-room-description, .editable-room-number').forEach(element => {
        element.addEventListener('click', function() {
            makeEditable(this);
        });
    });

    function openRoomEditModal(locationId) {
        const roomNumber = document.querySelector(`[data-location-id="${locationId}"][data-field="room_number"]`).dataset.value;
        const roomDescription = document.querySelector(`[data-location-id="${locationId}"][data-field="room_description"]`).dataset.value;
        const notes = document.querySelector(`[data-location-id="${locationId}"][data-field="notes"]`).dataset.value;

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Rýchla editácia miestnosti
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Číslo miestnosti</label>
                            <input type="text" class="form-control" id="room_number" value="${roomNumber}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Popis miestnosti</label>
                            <input type="text" class="form-control" id="room_description" value="${roomDescription}" placeholder="napr. Učebňa matematiky">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Poznámky</label>
                            <textarea class="form-control" id="notes" rows="2" placeholder="Dodatočné poznámky...">${notes}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                        <button type="button" class="btn btn-primary" onclick="saveRoomEdit('${locationId}')">Uložiť</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        modal.addEventListener('hidden.bs.modal', function() {
            modal.remove();
        });
    }

    // Globálna funkcia pre uloženie editácie miestnosti
    window.saveRoomEdit = function(locationId) {
        const roomNumber = document.getElementById('room_number').value;
        const roomDescription = document.getElementById('room_description').value;
        const notes = document.getElementById('notes').value;

        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('room_number', roomNumber);
        formData.append('room_description', roomDescription);
        formData.append('notes', notes);

        fetch(`/locations/${locationId}/quick-update`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Aktualizuj UI
                document.querySelector(`[data-location-id="${locationId}"][data-field="room_number"]`).textContent = roomNumber;
                document.querySelector(`[data-location-id="${locationId}"][data-field="room_number"]`).dataset.value = roomNumber;
                
                const descElement = document.querySelector(`[data-location-id="${locationId}"][data-field="room_description"]`);
                descElement.innerHTML = roomDescription ? `<strong>${roomDescription}</strong>` : '<em class="text-muted">Kliknite pre pridanie popisu...</em>';
                descElement.dataset.value = roomDescription;
                
                const notesElement = document.querySelector(`[data-location-id="${locationId}"][data-field="notes"]`);
                notesElement.innerHTML = notes ? 
                    `<small class="text-muted"><i class="fas fa-sticky-note"></i> ${notes}</small>` :
                    `<small class="text-muted fst-italic"><i class="fas fa-sticky-note"></i> Poznámka...</small>`;
                notesElement.dataset.value = notes;

                bootstrap.Modal.getInstance(document.querySelector('.modal')).hide();
                showToast(data.message, 'success');
            } else {
                showToast('Chyba pri ukladaní', 'error');
            }
        })
        .catch(error => {
            showToast('Chyba pri ukladaní', 'error');
        });
    };

    function makeEditable(element) {
        const currentValue = element.dataset.value;
        const field = element.dataset.field;
        const locationId = element.dataset.locationId;
        
        const input = document.createElement(field === 'notes' ? 'textarea' : 'input');
        input.type = field === 'notes' ? undefined : 'text';
        input.value = currentValue;
        input.className = 'form-control form-control-sm';
        if (field === 'notes') {
            input.rows = 2;
            input.placeholder = 'Zadajte poznámku...';
        }
        
        const originalContent = element.innerHTML;
        element.innerHTML = '';
        element.appendChild(input);
        
        const btnGroup = document.createElement('div');
        btnGroup.className = 'mt-1';
        btnGroup.innerHTML = `
            <button class="btn btn-success btn-sm me-1 save-btn">
                <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-secondary btn-sm cancel-btn">
                <i class="fas fa-times"></i>
            </button>
        `;
        element.appendChild(btnGroup);
        
        input.focus();
        input.select();
        
        btnGroup.querySelector('.save-btn').addEventListener('click', function() {
            saveField(locationId, field, input.value, element, originalContent);
        });
        
        btnGroup.querySelector('.cancel-btn').addEventListener('click', function() {
            element.innerHTML = originalContent;
        });
        
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                saveField(locationId, field, input.value, element, originalContent);
            }
        });
        
        input.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                element.innerHTML = originalContent;
            }
        });
    }
    
    function saveField(locationId, field, value, element, originalContent) {
        const formData = new FormData();
        formData.append('_method', 'PATCH');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append(field, value);
        
        fetch(`/locations/${locationId}/quick-update`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                element.dataset.value = value;
                
                if (field === 'name') {
                    element.innerHTML = `<i class="fas fa-building text-primary me-2"></i>${value}`;
                } else if (field === 'room_number') {
                    element.innerHTML = value;
                } else if (field === 'room_description') {
                    element.innerHTML = value ? `<strong>${value}</strong>` : '<em class="text-muted">Kliknite pre pridanie popisu...</em>';
                } else if (field === 'notes') {
                    element.innerHTML = value ? 
                        `<small class="text-muted"><i class="fas fa-sticky-note"></i> ${value}</small>` :
                        `<small class="text-muted fst-italic"><i class="fas fa-sticky-note"></i> ${element.closest('.card-header') ? 'Poznámka...' : 'Kliknite pre pridanie poznámky...'}</small>`;
                }
                
                showToast(data.message, 'success');
            } else {
                element.innerHTML = originalContent;
                showToast('Chyba pri ukladaní', 'error');
            }
        })
        .catch(error => {
            element.innerHTML = originalContent;
            showToast('Chyba pri ukladaní', 'error');
        });
    }
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 3000);
    }

    // Confirm delete
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Naozaj chcete vymazať túto miestnosť? Táto akcia sa nedá vrátiť späť.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush