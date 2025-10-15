{{-- Professional Page Header Component --}}
@props([
    'title' => 'Nadpis stránky',
    'subtitle' => null,
    'icon' => 'bi-gear',
    'actions' => null
])

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2">
                <i class="{{ $icon }} me-3"></i>
                {{ $title }}
            </h1>
            @if($subtitle)
                <p class="text-muted mb-0">{{ $subtitle }}</p>
            @endif
        </div>
        @if($actions)
            <div>
                {{ $actions }}
            </div>
        @endif
    </div>
</div>

<style>
.page-header {
    background: linear-gradient(135deg, var(--edu-white) 0%, var(--edu-light) 100%);
    border-radius: 0.75rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--edu-shadow);
}

.page-header h1 {
    font-family: 'Source Sans Pro', sans-serif;
    font-weight: 700;
    color: var(--edu-dark);
    margin: 0;
}
</style>