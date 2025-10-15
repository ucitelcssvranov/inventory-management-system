{{-- Professional Stats Card Component --}}
@props([
    'title' => 'Štatistika',
    'value' => '0',
    'icon' => 'bi-graph-up',
    'color' => 'primary',
    'link' => null,
    'subtitle' => null
])

@php
    $colors = [
        'primary' => ['bg' => 'var(--edu-primary)', 'border' => 'var(--edu-primary)'],
        'success' => ['bg' => 'var(--edu-accent)', 'border' => 'var(--edu-accent)'],
        'warning' => ['bg' => 'var(--edu-warning)', 'border' => 'var(--edu-warning)'],
        'info' => ['bg' => '#0ea5e9', 'border' => '#0ea5e9'],
        'danger' => ['bg' => 'var(--edu-danger)', 'border' => 'var(--edu-danger)']
    ];
    $colorConfig = $colors[$color] ?? $colors['primary'];
@endphp

<div class="card stats-card stats-{{ $color }} hover-shadow-lg" style="border-left-color: {{ $colorConfig['border'] }}">
    <div class="card-body text-center p-4">
        <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, {{ $colorConfig['bg'] }} 0%, {{ $colorConfig['bg'] }}dd 100%);">
            <i class="{{ $icon }}"></i>
        </div>
        <h3 class="stats-number">{{ $value }}</h3>
        <p class="stats-label">{{ $title }}</p>
        @if($subtitle)
            <small class="text-muted">{{ $subtitle }}</small>
        @endif
    </div>
    @if($link)
        <div class="card-footer bg-transparent border-0 p-3">
            <a href="{{ $link }}" class="btn btn-{{ $color }} btn-sm w-100">
                <i class="bi bi-arrow-right me-1"></i>
                {{ $slot }}
            </a>
        </div>
    @endif
</div>

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
</style>