@extends('layouts.app')

@push('styles')
<style>
.stats-card {
    background: linear-gradient(135deg, var(--edu-white) 0%, #f8fafc 100%);
    border-left: 4px solid var(--edu-primary);
    transition: all 0.3s ease;
    cursor: pointer;
}

.stats-card:hover {
    box-shadow: var(--edu-shadow-lg);
    transform: translateY(-3px);
}

.stats-card.stats-primary { border-left-color: var(--edu-primary); }
.stats-card.stats-success { border-left-color: var(--edu-accent); }
.stats-card.stats-warning { border-left-color: var(--edu-warning); }
.stats-card.stats-info { border-left-color: #0ea5e9; }

.stats-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1rem;
    color: white;
}

.stats-icon.bg-primary { background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%); }
.stats-icon.bg-success { background: linear-gradient(135deg, var(--edu-accent) 0%, #10b981 100%); }
.stats-icon.bg-warning { background: linear-gradient(135deg, var(--edu-warning) 0%, #f59e0b 100%); }
.stats-icon.bg-info { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--edu-dark);
    margin: 0;
    line-height: 1;
}

.stats-label {
    color: var(--edu-secondary);
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
    margin: 0.5rem 0 0 0;
}

.welcome-card {
    background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
    color: white;
    border-radius: 1rem;
    padding: 3rem 2rem;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 150px;
    height: 150px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: rotate(45deg);
}

.welcome-card::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
}

.action-card {
    background: var(--edu-white);
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: var(--edu-shadow);
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    border: 2px solid transparent;
}

.action-card:hover {
    box-shadow: var(--edu-shadow-lg);
    transform: translateY(-3px);
    border-color: var(--edu-primary);
    color: inherit;
    text-decoration: none;
}

.action-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
    color: white;
}

.section-title {
    font-family: 'Source Sans Pro', sans-serif;
    font-weight: 700;
    color: var(--edu-dark);
    margin-bottom: 2rem;
    position: relative;
    padding-left: 1rem;
}

.section-title::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
    border-radius: 2px;
}

.recent-items-card {
    background: var(--edu-white);
    border-radius: 0.75rem;
    box-shadow: var(--edu-shadow);
    overflow: hidden;
}

.recent-items-header {
    background: linear-gradient(135deg, var(--edu-light) 0%, #f1f5f9 100%);
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--edu-border);
}

.recent-items-body {
    padding: 1.5rem;
    max-height: 300px;
    overflow-y: auto;
}

.recent-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--edu-border);
    transition: all 0.2s ease;
}

.recent-item:last-child {
    border-bottom: none;
}

.recent-item:hover {
    background: var(--edu-light);
    margin: 0 -1.5rem;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    border-radius: 0.5rem;
}


</style>
@endpush

@section('content')
    <!-- Welcome Section -->
    <div class="welcome-card">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-3">
                    @if(auth()->user()->isAdmin())
                        Vitajte v administrácii systému
                    @else
                        Vitajte v inventarizačnom systéme
                    @endif
                </h2>
                <p class="mb-0 opacity-90 fs-5">
                    @if(auth()->user()->isAdmin())
                        Kompletná správa majetku a inventarizačných procesov školy
                    @else
                        Prístup k Vašim prideľovaným úlohám a komisiam
                    @endif
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="display-1 opacity-25">
                    @if(auth()->user()->isAdmin())
                        <i class="bi bi-gear"></i>
                    @else
                        <i class="bi bi-person-badge"></i>
                    @endif
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