@extends('layouts.app')

@section('title', 'Export Inventúrneho súpisu')

@section('content')
<div class="container">
    <h1 class="my-4"><i class="bi bi-file-earmark-text me-2"></i> Export Inventúrneho súpisu</h1>

    <div class="card">
        <div class="card-body">
            <h2>Inventúrny súpis</h2>
            <p><strong>Názov účtovnej jednotky:</strong> {{ $plan->unit_name }}</p>
            <p><strong>Typ inventúry:</strong> {{ $plan->type }}</p>
            <p><strong>Miesto uloženia:</strong> {{ $plan->storage_place }}</p>
            <p><strong>Hmotne zodpovedná osoba:</strong> {{ $plan->responsiblePerson->name ?? '-' }}</p>

            <h3 class="mt-4">Zoznam aktív</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Názov</th>
                        <th>Množstvo</th>
                        <th>Jednotková cena</th>
                        <th>Spolu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plan->items as $item)
                        <tr>
                            <td>{{ $item->asset->name ?? '-' }}</td>
                            <td>{{ $item->expected_qty ?? 1 }}</td>
                            <td>
                                {{ isset($item->asset->acquisition_cost) ? number_format($item->asset->acquisition_cost, 2) . ' €' : '-' }}
                            </td>
                            <td>
                                {{ isset($item->asset->acquisition_cost) ? number_format(($item->expected_qty ?? 1) * $item->asset->acquisition_cost, 2) . ' €' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection