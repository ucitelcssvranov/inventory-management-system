@extends('layouts.app')

@section('title', 'Inventúrne plány - Export zápisu')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="text-center mb-4"><i class="bi bi-journal-text me-2"></i> Inventúrne plány - Export zápisu</h1>
            
            <div class="card">
                <div class="card-body">
                    <h2>Inventarizačný zápis</h2>
                    <p><strong>Názov účtovnej jednotky:</strong> {{ $plan->unit_name }}</p>
                    <p><strong>Adresa:</strong> {{ $plan->unit_address }}</p>
                    <p><strong>Dátum inventúry:</strong> {{ $plan->inventory_day }}</p>
                    <p><strong>Výsledky porovnania skutočného a účtovného stavu:</strong></p>
                    {{-- vypíš výsledky --}}
                    <p><strong>Inventarizačné rozdiely a ich príčiny:</strong></p>
                    {{-- vypíš rozdiely a príčiny --}}
                    <p><strong>Návrhy na vysporiadanie rozdielov:</strong></p>
                    {{-- vypíš návrhy --}}
                    <p><strong>Podpisy osôb zodpovedných za inventarizáciu:</strong></p>
                    {{-- vypíš podpisy --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection