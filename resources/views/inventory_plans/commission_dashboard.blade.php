@extends('layouts.app')

@section('title', 'Dashboard komisií')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <!-- Header -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <i class="bi bi-diagram-3"></i> Dashboard správy inventarizačných komisií
                </h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="bi bi-list-check display-4 text-primary"></i>
                                <h4>{{ $unassignedPlans->count() }}</h4>
                                <p class="text-muted">Neschválené plány</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="bi bi-people display-4 text-success"></i>
                                <h4>{{ $assignedPlans->count() }}</h4>
                                <p class="text-muted">Priradené plány</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="bi bi-person-badge display-4 text-info"></i>
                                <h4>{{ $commissions->count() }}</h4>
                                <p class="text-muted">Aktívne komisie</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="bi bi-clock display-4 text-warning"></i>
                                <h4>{{ $assignedPlans->where('status', \App\Models\InventoryPlan::STATUS_IN_PROGRESS)->count() }}</h4>
                                <p class="text-muted">V procese</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Nepriradené plány -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul text-primary"></i> Neschválené inventarizačné plány
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($unassignedPlans->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Názov</th>
                                            <th>Dátum</th>
                                            <th>Zodpovedná osoba</th>
                                            <th>Akcie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unassignedPlans as $plan)
                                            <tr id="plan-row-{{ $plan->id }}">
                                                <td>
                                                    <strong>{{ $plan->name }}</strong>
                                                    <br><small class="text-muted">{{ $plan->type_label }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $plan->date_start->format('d.m.Y') }} - {{ $plan->date_end->format('d.m.Y') }}</small>
                                                </td>
                                                <td>
                                                    @if($plan->responsiblePerson)
                                                        <small>{{ $plan->responsiblePerson->name }}</small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary assign-commission-btn" 
                                                            data-plan-id="{{ $plan->id }}"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#assignCommissionModal"
                                                            title="Priradiť komisiu">
                                                        <i class="bi bi-person-plus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-check-circle display-1"></i>
                                <h5>Všetky schválené plány sú priradené</h5>
                                <p>Momentálne nie sú žiadne neschválené inventarizačné plány.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Priradené plány -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people text-success"></i> Priradené inventarizačné plány
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($assignedPlans->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Názov</th>
                                            <th>Komisia</th>
                                            <th>Stav</th>
                                            <th>Akcie</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignedPlans as $plan)
                                            <tr id="assigned-plan-row-{{ $plan->id }}">
                                                <td>
                                                    <strong>{{ $plan->name }}</strong>
                                                    <br><small class="text-muted">{{ $plan->date_start->format('d.m.Y') }}</small>
                                                </td>
                                                <td>
                                                    @if($plan->commission)
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                                {{ substr($plan->commission->name, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <small><strong>{{ $plan->commission->name }}</strong></small>
                                                                @if($plan->commission->chairman)
                                                                    <br><small class="text-muted">{{ $plan->commission->chairman->name }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Bez komisie</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $plan->status_color }}">{{ $plan->status_label }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        @if($plan->canBeStarted())
                                                            <button class="btn btn-sm btn-success start-inventory-btn" 
                                                                    data-plan-id="{{ $plan->id }}"
                                                                    title="Spustiť inventarizáciu">
                                                                <i class="bi bi-play"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        @if($plan->canBeCompleted())
                                                            <button class="btn btn-sm btn-warning complete-inventory-btn" 
                                                                    data-plan-id="{{ $plan->id }}"
                                                                    title="Dokončiť inventarizáciu">
                                                                <i class="bi bi-check"></i>
                                                            </button>
                                                        @endif

                                                        @if($plan->canBeSigned())
                                                            <button class="btn btn-sm btn-info sign-inventory-btn" 
                                                                    data-plan-id="{{ $plan->id }}"
                                                                    title="Podpísať inventarizáciu">
                                                                <i class="bi bi-pen"></i>
                                                            </button>
                                                        @endif

                                                        <button class="btn btn-sm btn-outline-danger remove-commission-btn" 
                                                                data-plan-id="{{ $plan->id }}"
                                                                title="Odstrániť priradenie">
                                                            <i class="bi bi-person-dash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-1"></i>
                                <h5>Žiadne priradené plány</h5>
                                <p>Momentálne nie sú žiadne plány priradené komisiám.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Komisie overview -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-people-fill text-info"></i> Prehľad komisií
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($commissions as $commission)
                                <div class="col-md-4 mb-3">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $commission->name }}</h6>
                                            <p class="card-text">
                                                <strong>Predseda:</strong> 
                                                @if($commission->chairman)
                                                    {{ $commission->chairman->name }}
                                                @else
                                                    <span class="text-muted">Bez predsedu</span>
                                                @endif
                                            </p>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <i class="bi bi-people"></i> {{ $commission->total_members_count }} členov
                                                    <br>
                                                    <i class="bi bi-list-check"></i> {{ $commission->inventory_plans_count }} plánov
                                                </small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center text-muted py-4">
                                        <i class="bi bi-person-plus display-1"></i>
                                        <h5>Žiadne aktívne komisie</h5>
                                        <p>Vytvorte inventarizačné komisie pre priradenie plánov.</p>
                                        <a href="{{ route('inventory-commissions.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Vytvoriť komisiu
                                        </a>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pre priradenie komisie -->
<div class="modal fade" id="assignCommissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignCommissionForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Priradiť komisiu k inventarizačnému plánu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="commission_id" class="form-label">Vyberte komisiu <span class="text-danger">*</span></label>
                        <select class="form-select" id="commission_id" name="commission_id" required>
                            <option value="">Vyberte komisiu...</option>
                            @foreach($commissions as $commission)
                                <option value="{{ $commission->id }}">
                                    {{ $commission->name }} 
                                    @if($commission->chairman)
                                        ({{ $commission->chairman->name }})
                                    @endif
                                    - {{ $commission->total_members_count }} členov
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Informácia:</strong> Po priradení komisie bude inventarizačný plán presunutý do stavu "Priradený komisii" a komisia môže začať s inventarizáciou.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zrušiť</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Priradiť komisiu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let currentPlanId = null;

    // Helper function to show alerts
    function showAlert(type, message) {
        $('.alert-dynamic').remove();
        
        const alertDiv = $(`
            <div class="alert alert-${type} alert-dismissible fade show alert-dynamic">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('.container').prepend(alertDiv);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Assign commission modal
    $('.assign-commission-btn').click(function() {
        currentPlanId = $(this).data('plan-id');
    });

    // Handle assign commission form
    $('#assignCommissionForm').submit(function(e) {
        e.preventDefault();
        
        if (!currentPlanId) {
            showAlert('danger', 'Chyba: Nebol vybratý inventarizačný plán.');
            return;
        }

        const formData = new FormData(this);
        
        fetch(`/inventory-plans/${currentPlanId}/assign-commission`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#assignCommissionModal').modal('hide');
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'Chyba pri priraďovaní komisie.');
        });
    });

    // Start inventory
    $('.start-inventory-btn').click(function() {
        const planId = $(this).data('plan-id');
        
        if (confirm('Naozaj chcete spustiť inventarizáciu?')) {
            fetch(`/inventory-plans/${planId}/start`, {
                method: 'PATCH',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Chyba pri spúšťaní inventarizácie.');
            });
        }
    });

    // Complete inventory
    $('.complete-inventory-btn').click(function() {
        const planId = $(this).data('plan-id');
        
        if (confirm('Naozaj chcete dokončiť inventarizáciu?')) {
            fetch(`/inventory-plans/${planId}/complete`, {
                method: 'PATCH',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Chyba pri dokončovaní inventarizácie.');
            });
        }
    });

    // Sign inventory
    $('.sign-inventory-btn').click(function() {
        const planId = $(this).data('plan-id');
        
        if (confirm('Naozaj chcete podpísať inventarizáciu?')) {
            fetch(`/inventory-plans/${planId}/sign`, {
                method: 'PATCH',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Chyba pri podpisovaní inventarizácie.');
            });
        }
    });

    // Remove commission assignment
    $('.remove-commission-btn').click(function() {
        const planId = $(this).data('plan-id');
        
        if (confirm('Naozaj chcete odstrániť priradenie komisie?')) {
            fetch(`/inventory-plans/${planId}/remove-commission`, {
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
                    showAlert('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Chyba pri odstraňovaní priradenia.');
            });
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 24px;
    height: 24px;
    font-size: 10px;
    font-weight: bold;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush
@endsection