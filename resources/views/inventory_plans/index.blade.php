@extends('layouts.app')

@section('title', 'Inventarizačné plány')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="card-title mb-0">
                    <i class="bi bi-journal-text"></i> Správa inventarizačných plánov
                </h1>
            </div>
            <div class="card-body">
                <!-- Header with stats and add button -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3>Inventarizačné plány</h3>
                        <small class="text-muted">Plánovanie a správa inventarizácií</small>
                    </div>
                    <a href="{{ route('inventory_plans.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Vytvoriť plán
                    </a>
                </div>

                <!-- Plans Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">Názov plánu</th>
                                <th style="width: 15%;">Typ</th>
                                <th style="width: 15%;">Dátum</th>
                                <th style="width: 15%;">Zodpovedná osoba</th>
                                <th style="width: 15%;">Komisia</th>
                                <th style="width: 10%;">Stav</th>
                                <th style="width: 10%;">Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr id="plan-row-{{ $plan->id }}">
                                    <td>
                                        <div>
                                            <strong>{{ $plan->name }}</strong>
                                            @if($plan->description)
                                                <br><small class="text-muted">{{ Str::limit($plan->description, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $plan->type_label ?? $plan->type }}</span>
                                    </td>
                                    <td>
                                        @if($plan->date_start && $plan->date_end)
                                            <small>{{ $plan->date_start->format('d.m.Y') }} - {{ $plan->date_end->format('d.m.Y') }}</small>
                                        @elseif($plan->date)
                                            <small>{{ $plan->date->format('d.m.Y') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($plan->responsiblePerson)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                    {{ substr($plan->responsiblePerson->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <small><strong>{{ $plan->responsiblePerson->name }}</strong></small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Neuvedené</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($plan->commission)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-success text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
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
                                            <span class="text-warning">
                                                <i class="bi bi-exclamation-triangle"></i> Nepriradená
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $plan->status_color ?? 'secondary' }}">
                                            {{ $plan->status_label ?? $plan->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('inventory_plans.show', $plan) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Zobraziť detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('inventory_plans.edit', $plan) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Upraviť">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if(!$plan->commission)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success assign-commission-btn"
                                                        data-plan-id="{{ $plan->id }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#assignCommissionModal"
                                                        title="Priradiť komisiu">
                                                    <i class="bi bi-person-plus"></i>
                                                </button>
                                            @endif
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger delete-plan-btn"
                                                    data-plan-id="{{ $plan->id }}"
                                                    title="Vymazať">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-journal-text display-1 mb-3"></i>
                                            <h5>Žiadne inventarizačné plány</h5>
                                            <p>Začnite vytvorením prvého inventarizačného plánu.</p>
                                            <a href="{{ route('inventory_plans.create') }}" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Vytvoriť plán
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(method_exists($plans, 'hasPages') && $plans->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $plans->links() }}
                    </div>
                @endif
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
                            @foreach(\App\Models\InventoryCommission::active()->with(['chairman', 'members'])->get() as $commission)
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
        
        $('.card-body').prepend(alertDiv);
        
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.redirected) {
                // Handle redirect case - reload the page
                location.reload();
                return;
            }
            return response.json();
        })
        .then(data => {
            if (!data) return; // Skip if redirected
            
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

    // Delete plan
    $('.delete-plan-btn').click(function() {
        const planId = $(this).data('plan-id');
        const $row = $('#plan-row-' + planId);
        
        if (confirm('Naozaj chcete vymazať tento inventarizačný plán?')) {
            fetch(`/inventory_plans/${planId}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $row.fadeOut(500, function() {
                        $(this).remove();
                    });
                    showAlert('success', data.message || 'Plán bol úspešne vymazaný.');
                } else {
                    showAlert('danger', data.message || 'Chyba pri mazaní plánu.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Chyba pri mazaní plánu.');
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

