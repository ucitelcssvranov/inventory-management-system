@extends('layouts.app')

@section('title', 'Inventarizačné komisie')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <i class="bi bi-people"></i> Správa inventarizačných komisií
                </h1>
            </div>
            <div class="card-body">
                <!-- Add New Commission Button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3>Inventarizačné komisie</h3>
                        <small class="text-muted">Skupiny používateľov zodpovedné za inventarizáciu</small>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCommissionModal">
                        <i class="bi bi-plus-circle"></i> Pridať komisiu
                    </button>
                </div>

                <!-- Commissions Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Názov komisie</th>
                                <th style="width: 20%;">Predseda</th>
                                <th style="width: 15%;">Počet členov</th>
                                <th style="width: 15%;">Plány</th>
                                <th style="width: 15%;">Vytvorené</th>
                                <th style="width: 10%;">Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commissions as $commission)
                                <tr data-commission-id="{{ $commission->id }}" id="commission-row-{{ $commission->id }}">
                                    <td>
                                        <div>
                                            <strong class="commission-name" data-field="name">{{ $commission->name }}</strong>
                                            @if($commission->description)
                                                <br><small class="text-muted commission-description" data-field="description">{{ $commission->description }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($commission->chairman)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                    {{ substr($commission->chairman->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $commission->chairman->name }}</strong>
                                                    <br><small class="text-muted">{{ $commission->chairman->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Bez predsedu</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $commission->total_members_count }} 
                                            {{ $commission->total_members_count == 1 ? 'člen' : 'členov' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($commission->inventory_plans_count > 0)
                                            <span class="badge bg-success">{{ $commission->inventory_plans_count }} plánov</span>
                                        @else
                                            <span class="text-muted">Žiadne plány</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $commission->created_at->format('d.m.Y') }}
                                            @if($commission->createdBy)
                                                <br>{{ $commission->createdBy->name }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('inventory-commissions.show', $commission) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Zobraziť detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('inventory-commissions.edit', $commission) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Upraviť">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger delete-commission-btn"
                                                    data-commission-id="{{ $commission->id }}"
                                                    title="Vymazať">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-people display-1 mb-3"></i>
                                            <h5>Žiadne inventarizačné komisie</h5>
                                            <p>Začnite vytvorením prvej komisie.</p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCommissionModal">
                                                <i class="bi bi-plus-circle"></i> Pridať komisiu
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($commissions->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $commissions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Commission Modal -->
<div class="modal fade" id="addCommissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addCommissionForm" action="{{ route('inventory-commissions.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Pridať inventarizačnú komisiu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Názov komisie <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="chairman_id" class="form-label">Predseda komisie <span class="text-danger">*</span></label>
                                <select class="form-select" id="chairman_id" name="chairman_id" required>
                                    <option value="">Vyberte predsedu...</option>
                                    @foreach(App\Models\User::orderBy('name')->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Popis</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Voliteľný popis komisie..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="members" class="form-label">Členovia komisie</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach(App\Models\User::orderBy('name')->get() as $user)
                                <div class="form-check">
                                    <input class="form-check-input member-checkbox" type="checkbox" name="members[]" value="{{ $user->id }}" id="member_{{ $user->id }}">
                                    <label class="form-check-label" for="member_{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->email }})
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted">Predseda sa automaticky nepridáva medzi členov.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Vytvoriť komisiu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Helper function to show alerts
    function showAlert(type, message) {
        // Remove existing alerts
        $('.alert-dynamic').remove();
        
        // Create new alert
        const alertDiv = $(`
            <div class="alert alert-${type} alert-dismissible fade show alert-dynamic">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        // Insert at the top of the card body
        $('.card-body').prepend(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Handle delete buttons
    $('.delete-commission-btn').click(function() {
        const commissionId = $(this).data('commission-id');
        const $row = $('#commission-row-' + commissionId);
        
        if (confirm('Naozaj chcete vymazať túto komisiu?')) {
            // Send DELETE request
            fetch(`/inventory-commissions/${commissionId}`, {
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
                    showAlert('success', data.message || 'Komisia bola úspešne vymazaná.');
                } else {
                    showAlert('danger', data.message || 'Chyba pri mazaní komisie.');
                }
            })
            .catch(error => {
                console.error('Chyba pri DELETE požiadavke:', error);
                showAlert('danger', 'Chyba pri mazaní komisie: ' + error.message);
            });
        }
    });

    // Disable chairman from being selected as member
    const chairmanSelect = document.getElementById('chairman_id');
    if (chairmanSelect) {
        chairmanSelect.addEventListener('change', function() {
            const chairmanId = this.value;
            const memberCheckboxes = document.querySelectorAll('.member-checkbox');
            
            memberCheckboxes.forEach(checkbox => {
                if (checkbox.value === chairmanId) {
                    checkbox.checked = false;
                    checkbox.disabled = chairmanId !== '';
                } else {
                    checkbox.disabled = false;
                }
            });
        });
    }

    // Handle form submission with proper error handling
    const addCommissionForm = document.getElementById('addCommissionForm');
    if (addCommissionForm) {
        addCommissionForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            
            if (!csrfToken) {
                showAlert('danger', 'Chyba: CSRF token sa nenašiel.');
                return;
            }
            
            // Add CSRF token to form data
            formData.append('_token', csrfToken.getAttribute('content'));
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (response.status === 422) {
                    // Validation errors
                    return response.json().then(data => {
                        let errorMessages = [];
                        if (data.errors) {
                            Object.values(data.errors).forEach(errors => {
                                errorMessages = errorMessages.concat(errors);
                            });
                        }
                        showAlert('danger', 'Chyby vo formulári: ' + errorMessages.join(', '));
                    });
                } else if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                } else {
                    return response.json().then(data => {
                        if (data.success || data.redirect) {
                            // Close modal and reload page
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addCommissionModal'));
                            if (modal) {
                                modal.hide();
                            }
                            location.reload();
                        } else {
                            showAlert('danger', data.message || 'Chyba pri vytváraní komisie.');
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Chyba pri vytváraní komisie: ' + error.message);
            });
        });
    }

    // Inline editing for commission name and description
    document.querySelectorAll('[data-field]').forEach(element => {
        element.addEventListener('dblclick', function() {
            const field = this.dataset.field;
            const commissionId = this.closest('[data-commission-id]').dataset.commissionId;
            const currentValue = this.textContent.trim();
            
            if (field === 'name' || field === 'description') {
                makeEditable(this, field, commissionId, currentValue);
            }
        });
    });

    function makeEditable(element, field, commissionId, currentValue) {
        const input = document.createElement(field === 'description' ? 'textarea' : 'input');
        input.type = field === 'description' ? undefined : 'text';
        input.value = currentValue;
        input.className = 'form-control form-control-sm';
        if (field === 'description') {
            input.rows = 2;
        }
        
        element.innerHTML = '';
        element.appendChild(input);
        input.focus();
        input.select();
        
        function saveEdit() {
            const newValue = input.value.trim();
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            
            if (!csrfToken) {
                element.textContent = currentValue;
                showAlert('danger', 'Chyba: CSRF token sa nenašiel.');
                return;
            }
            
            fetch(`/inventory-commissions/${commissionId}/quick-update`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({[field]: newValue})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    element.textContent = newValue || (field === 'description' ? '' : currentValue);
                    showAlert('success', data.message || 'Zmeny boli úspešne uložené.');
                } else {
                    element.textContent = currentValue;
                    showAlert('danger', data.message || 'Chyba pri ukladaní zmien.');
                }
            })
            .catch(error => {
                element.textContent = currentValue;
                console.error('Error:', error);
                showAlert('danger', 'Chyba pri ukladaní zmien: ' + error.message);
            });
        }
        
        input.addEventListener('blur', saveEdit);
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && field !== 'description') {
                saveEdit();
            }
            if (e.key === 'Escape') {
                element.textContent = currentValue;
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
    font-weight: bold;
}

[data-field] {
    cursor: pointer;
    border-radius: 3px;
    padding: 2px 4px;
    transition: background-color 0.2s;
}

[data-field]:hover {
    background-color: #f8f9fa;
}

/* Button group improvements */
.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endpush
@endsection