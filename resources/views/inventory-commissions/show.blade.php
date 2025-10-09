@extends('layouts.app')

@section('title', 'Detail inventarizačnej komisie')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-12">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('inventory-commissions.index') }}" class="text-decoration-none">
                        <i class="bi bi-people"></i> Inventarizačné komisie
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $inventoryCommission->name }}</li>
            </ol>
        </nav>

        <!-- Commission Header -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="card-title mb-0">
                        <i class="bi bi-people"></i> {{ $inventoryCommission->name }}
                    </h1>
                    <div>
                        <a href="{{ route('inventory-commissions.edit', $inventoryCommission) }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i> Upraviť
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($inventoryCommission->description)
                    <p class="text-muted mb-3">{{ $inventoryCommission->description }}</p>
                @endif
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Základné informácie</h5>
                        <dl class="row">
                            <dt class="col-sm-4">Vytvorené:</dt>
                            <dd class="col-sm-8">{{ $inventoryCommission->created_at->format('d.m.Y H:i') }}</dd>
                            
                            @if($inventoryCommission->createdBy)
                            <dt class="col-sm-4">Vytvoril:</dt>
                            <dd class="col-sm-8">{{ $inventoryCommission->createdBy->name }}</dd>
                            @endif
                            
                            @if($inventoryCommission->updated_at != $inventoryCommission->created_at)
                            <dt class="col-sm-4">Upravené:</dt>
                            <dd class="col-sm-8">
                                {{ $inventoryCommission->updated_at->format('d.m.Y H:i') }}
                                @if($inventoryCommission->updatedBy)
                                    <br><small class="text-muted">{{ $inventoryCommission->updatedBy->name }}</small>
                                @endif
                            </dd>
                            @endif
                        </dl>
                    </div>
                    
                    <div class="col-md-6">
                        <h5>Štatistiky</h5>
                        <dl class="row">
                            <dt class="col-sm-6">Členov spolu:</dt>
                            <dd class="col-sm-6">
                                <span class="badge bg-info">{{ $inventoryCommission->total_members_count }}</span>
                            </dd>
                            
                            <dt class="col-sm-6">Pridelených plánov:</dt>
                            <dd class="col-sm-6">
                                <span class="badge bg-{{ $inventoryCommission->inventoryPlans->count() > 0 ? 'success' : 'secondary' }}">
                                    {{ $inventoryCommission->inventoryPlans->count() }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Chairman and Members -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-person-badge"></i> Predseda a členovia
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Chairman -->
                        @if($inventoryCommission->chairman)
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Predseda komisie</h6>
                                <div class="d-flex align-items-center p-3 bg-primary bg-opacity-10 rounded">
                                    <div class="avatar-md bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block">{{ $inventoryCommission->chairman->name }}</strong>
                                        <small class="text-muted">{{ $inventoryCommission->chairman->email }}</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> Komisia nemá určeného predsedu.
                            </div>
                        @endif

                        <!-- Members -->
                        <div>
                            <h6 class="text-muted mb-2">
                                Členovia komisie 
                                @if($inventoryCommission->members->count() > 0)
                                    <span class="badge bg-info">{{ $inventoryCommission->members->count() }}</span>
                                @endif
                            </h6>
                            
                            @if($inventoryCommission->members->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($inventoryCommission->members as $member)
                                        <div class="list-group-item d-flex align-items-center px-0">
                                            <div class="avatar-sm bg-secondary text-white rounded-circle me-3 d-flex align-items-center justify-content-center">
                                                {{ substr($member->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong class="d-block">{{ $member->name }}</strong>
                                                <small class="text-muted">{{ $member->email }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Komisia nemá žiadnych členov okrem predsedu.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Plans -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-clipboard-check"></i> Pridelené inventarizačné plány
                        </h4>
                    </div>
                    <div class="card-body">
                        @if($inventoryCommission->inventoryPlans->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($inventoryCommission->inventoryPlans as $plan)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <strong class="d-block">{{ $plan->name }}</strong>
                                            <small class="text-muted">
                                                {{ $plan->items->count() }} položiek
                                                @if($plan->start_date && $plan->end_date)
                                                    • {{ $plan->start_date->format('d.m.Y') }} - {{ $plan->end_date->format('d.m.Y') }}
                                                @endif
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $plan->status === 'completed' ? 'success' : ($plan->status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($plan->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-clipboard-x display-4 mb-3"></i>
                                <h6>Žiadne pridelené plány</h6>
                                <p class="mb-0">Tejto komisii nie sú zatiaľ pridelené žiadne inventarizačné plány.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="bi bi-lightning"></i> Rýchle akcie
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#editMembersModal">
                            <i class="bi bi-people"></i> Upraviť členov komisie
                        </button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="{{ route('inventory-commissions.edit', $inventoryCommission) }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-pencil"></i> Upraviť komisiu
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Members Modal -->
<div class="modal fade" id="editMembersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editMembersForm">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Upraviť členov komisie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_chairman_id" class="form-label">Predseda komisie <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_chairman_id" name="chairman_id" required>
                            @foreach(App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}" {{ $inventoryCommission->chairman_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_members" class="form-label">Členovia komisie</label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            @foreach(App\Models\User::orderBy('name')->get() as $user)
                                <div class="form-check">
                                    <input class="form-check-input edit-member-checkbox" type="checkbox" name="members[]" value="{{ $user->id }}" id="edit_member_{{ $user->id }}"
                                        {{ $inventoryCommission->members->contains($user->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="edit_member_{{ $user->id }}">
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
                        <i class="bi bi-check-circle"></i> Uložiť zmeny
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Handle chairman change in edit modal
document.getElementById('edit_chairman_id').addEventListener('change', function() {
    const chairmanId = this.value;
    const memberCheckboxes = document.querySelectorAll('.edit-member-checkbox');
    
    memberCheckboxes.forEach(checkbox => {
        if (checkbox.value === chairmanId) {
            checkbox.checked = false;
            checkbox.disabled = chairmanId !== '';
        } else {
            checkbox.disabled = false;
        }
    });
});

// Handle edit members form submission
document.getElementById('editMembersForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    
    // Get chairman_id
    data.chairman_id = formData.get('chairman_id');
    
    // Get members array
    data.members = [];
    const memberCheckboxes = document.querySelectorAll('.edit-member-checkbox:checked');
    memberCheckboxes.forEach(checkbox => {
        data.members.push(checkbox.value);
    });
    
    fetch(`{{ route('inventory-commissions.show', $inventoryCommission) }}/update-members`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Chyba pri aktualizácii členov komisie.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Chyba pri aktualizácii členov komisie.');
    });
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

.avatar-md {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: bold;
}
</style>
@endpush
@endsection
