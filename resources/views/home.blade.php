@extends('layouts.app')

@push('styles')
<style>
.hover-shadow-lg {
    transition: all 0.3s ease-in-out;
}

.hover-shadow-lg:hover {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
    transform: translateY(-2px);
}

.card-header {
    border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-left: 4px solid #0dcaf0;
}

.list-unstyled li {
    padding: 2px 0;
}

.section-divider {
    border-top: 3px solid #dee2e6;
    margin: 3rem 0 2rem 0;
    position: relative;
}

.section-divider::after {
    content: '';
    position: absolute;
    top: -2px;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 4px;
    background: #0d6efd;
    border-radius: 2px;
}
</style>
@endpush

@section('content')
    <!-- Uvítanie a prehľad -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white border-0 shadow">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h3 class="mb-1">
                                <i class="bi bi-house-door me-2"></i>
                                @if(auth()->user()->isAdmin())
                                    Administrácia systému
                                @else
                                    Môj inventarizačný panel
                                @endif
                            </h3>
                            <p class="mb-0 opacity-75">
                                @if(auth()->user()->isAdmin())
                                    Správa majetku a inventarizácie školy
                                @else
                                    Vaše pridelené úlohy a komisie
                                @endif
                            </p>
                        </div>
                        <div class="text-end">
                            <div class="display-6 opacity-50">
                                <i class="bi bi-clipboard-data"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
        <!-- ADMIN DASHBOARD -->
        @include('home.admin-dashboard')
    @else
        <!-- USER DASHBOARD -->
        @include('home.user-dashboard')
    @endif
@endsection