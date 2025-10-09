@extends('layouts.app')

@section('title', 'Lokácie')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <i class="bi bi-geo-alt"></i> Správa lokácií
                </h1>
            </div>
            <div class="card-body">
                <!-- Navigation Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('locations.index') }}" class="text-decoration-none">
                                <i class="bi bi-house"></i> Všetky lokácie
                            </a>
                        </li>
                        @if($currentLocation)
                            @if($currentLocation->type === 'room' && $currentLocation->parent)
                                <li class="breadcrumb-item">
                                    <a href="{{ route('locations.show', $currentLocation->parent->id) }}" class="text-decoration-none">
                                        {{ $currentLocation->parent->name }}
                                    </a>
                                </li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ $currentLocation->name }}
                            </li>
                        @endif
                    </ol>
                </nav>

                <!-- Add New Location Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        @if($currentLocation)
                            <h3>{{ $currentLocation->name }}</h3>
                            <small class="text-muted">{{ $currentLocation->description }}</small>
                        @else
                            <h3>Všetky budovy</h3>
                        @endif
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                        <i class="bi bi-plus-circle"></i> Pridať
                        @if($currentLocation && $currentLocation->type === 'budova')
                            miestnosť
                        @else
                            budovu
                        @endif
                    </button>
                </div>

                <!-- Locations Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th><i class="bi bi-hash"></i> ID</th>
                                <th><i class="bi bi-building"></i> Názov</th>
                                <th><i class="bi bi-card-text"></i> Popis</th>
                                <th><i class="bi bi-diagram-3"></i> Typ</th>
                                <th><i class="bi bi-gear"></i> Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($locations as $location)
                                <tr id="location-row-{{ $location->id }}">
                                    <td>{{ $location->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($location->type === 'budova')
                                                <i class="bi bi-building text-primary me-2"></i>
                                            @else
                                                <i class="bi bi-door-open text-info me-2"></i>
                                            @endif
                                            @if($location->type === 'budova')
                                                <span class="location-name" data-field="name" data-location-id="{{ $location->id }}">
                                                    {{ $location->name }}
                                                </span>
                                            @else
                                                <span class="location-room-number" data-field="room_number" data-location-id="{{ $location->id }}">
                                                    {{ $location->room_number ?? 'Bez čísla' }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($location->type === 'budova')
                                            <span class="location-description" data-field="description" data-location-id="{{ $location->id }}">
                                                {{ $location->description ?? '-' }}
                                            </span>
                                        @else
                                            <span class="location-room-description" data-field="room_description" data-location-id="{{ $location->id }}">
                                                {{ $location->room_description ?? '-' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($location->type === 'budova')
                                            <span class="badge bg-primary">Budova</span>
                                        @else
                                            <span class="badge bg-info">Miestnosť</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($location->type === 'budova')
                                                <a href="{{ route('locations.show', $location->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Zobraziť miestnosti">
                                                    <i class="bi bi-arrow-right-circle"></i>
                                                </a>
                                            @endif
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning edit-location-btn"
                                                    data-location-id="{{ $location->id }}"
                                                    title="Upraviť">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger delete-location-btn"
                                                    data-location-id="{{ $location->id }}"
                                                    title="Zmazať">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox display-4"></i>
                                        <div class="mt-2">
                                            @if($currentLocation)
                                                V tejto budove zatiaľ nie sú žiadne miestnosti.
                                            @else
                                                Zatiaľ nie sú vytvorené žiadne budovy.
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($locations->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $locations->links() }}
                    </div>
                @endif

                <!-- Back Button for Rooms View -->
                @if($currentLocation && $currentLocation->type === 'building')
                    <div class="mt-3">
                        <a href="{{ route('locations.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Späť na budovy
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Location Modal -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLocationModalLabel">
                    Pridať novú 
                    @if($currentLocation && $currentLocation->type === 'budova')
                        miestnosť
                    @else
                        budovu
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('locations.store') }}" method="POST">
                @csrf
                @if($currentLocation && $currentLocation->type === 'budova')
                    <input type="hidden" name="parent_id" value="{{ $currentLocation->id }}">
                    <input type="hidden" name="type" value="miestnost">
                @else
                    <input type="hidden" name="type" value="budova">
                @endif
                
                <div class="modal-body">
                    @if($currentLocation && $currentLocation->type === 'budova')
                        <!-- Formulár pre miestnosť -->
                        <div class="mb-3">
                            <label for="room_number" class="form-label">Číslo miestnosti</label>
                            <input type="text" class="form-control" id="room_number" name="room_number" required placeholder="napr. 101, A-12">
                        </div>
                        <div class="mb-3">
                            <label for="room_description" class="form-label">Popis miestnosti</label>
                            <input type="text" class="form-control" id="room_description" name="room_description" placeholder="napr. Riaditeľova kancelária">
                        </div>
                    @else
                        <!-- Formulár pre budovu -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Názov budovy</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="napr. Hlavná budova">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Popis budovy (voliteľný)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Krátky popis budovy..."></textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="submit" class="btn btn-primary">Pridať</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Make location names, descriptions, room numbers and room descriptions editable
    $('.location-name, .location-description, .location-room-number, .location-room-description').click(function() {
        const $this = $(this);
        const currentValue = $this.text().trim();
        const field = $this.data('field');
        const locationId = $this.data('location-id');
        
        // Don't edit if already editing
        if ($this.find('input').length > 0) {
            return;
        }
        
        // Create input field
        const $input = $('<input>', {
            type: 'text',
            class: 'form-control form-control-sm',
            value: currentValue === '-' ? '' : currentValue,
            'data-original-value': currentValue
        });
        
        // Replace content with input
        $this.html($input);
        $input.focus().select();
        
        // Handle save on blur or enter
        $input.on('blur keypress', function(e) {
            if (e.type === 'keypress' && e.which !== 13) {
                return;
            }
            
            const newValue = $(this).val().trim();
            const originalValue = $(this).data('original-value');
            
            // If value hasn't changed, just restore
            if (newValue === originalValue || (newValue === '' && originalValue === '-')) {
                $this.text(originalValue);
                return;
            }
            
            // Save the new value
            saveLocationField(locationId, field, newValue, $this);
        });
        
        // Handle escape key
        $input.on('keydown', function(e) {
            if (e.which === 27) { // Escape key
                const originalValue = $(this).data('original-value');
                $this.text(originalValue);
            }
        });
    });
    
    // Function to save location field via AJAX
    function saveLocationField(locationId, field, value, $element) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'PATCH');
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
                // Update the display
                const displayValue = value || '-';
                $element.text(displayValue);
                
                // Show success feedback
                $element.addClass('bg-success text-white');
                setTimeout(() => {
                    $element.removeClass('bg-success text-white');
                }, 1000);
            } else {
                // Show error and restore original value
                const originalValue = $element.find('input').data('original-value');
                $element.text(originalValue);
                
                $element.addClass('bg-danger text-white');
                setTimeout(() => {
                    $element.removeClass('bg-danger text-white');
                }, 2000);
                
                console.error('Chyba pri ukladaní:', data.message);
            }
        })
        .catch(error => {
            // Restore original value on error
            const originalValue = $element.find('input').data('original-value');
            $element.text(originalValue);
            
            $element.addClass('bg-danger text-white');
            setTimeout(() => {
                $element.removeClass('bg-danger text-white');
            }, 2000);
            
            console.error('Chyba pri AJAX požiadavke:', error);
        });
    }
    
    // Handle edit buttons
    $('.edit-location-btn').click(function() {
        const locationId = $(this).data('location-id');
        // Redirect to edit page
        window.location.href = `/locations/${locationId}/edit`;
    });
    
    // Handle delete buttons
    $('.delete-location-btn').click(function() {
        const locationId = $(this).data('location-id');
        const $row = $('#location-row-' + locationId);
        
        if (confirm('Naozaj chcete zmazať túto lokáciu? Táto akcia sa nedá vrátiť späť.')) {
            // Send DELETE request
            fetch(`/locations/${locationId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row with animation
                    $row.fadeOut(500, function() {
                        $(this).remove();
                    });
                } else {
                    alert('Chyba pri mazaní: ' + (data.message || 'Neznáma chyba'));
                }
            })
            .catch(error => {
                console.error('Chyba pri DELETE požiadavke:', error);
                alert('Chyba pri mazaní lokácie.');
            });
        }
    });
});
</script>
@endpush