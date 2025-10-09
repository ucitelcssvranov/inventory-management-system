@extends('layouts.app')

@section('title', 'Správy z inventarizácie')

@section('content')
<div class="container">
    <h2 class="mb-4"><i class="bi bi-file-earmark-text me-2"></i>Správy z inventarizácie</h2>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Názov plánu</th>
                        <th>Dátum</th>
                        <th>Typ inventúry</th>
                        <th>Hmotne zodpovedná osoba</th>
                        <th>Akcie</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>{{ $plan->name }}</td>
                            <td>{{ $plan->date }}</td>
                            <td>{{ $plan->type }}</td>
                            <td>{{ $plan->responsiblePerson->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('inventory_plans.export.soupis.pdf', $plan->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> Súpis PDF
                                </a>
                                <a href="{{ route('inventory_plans.export.zapis.pdf', $plan->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-earmark-pdf"></i> Zápis PDF
                                </a>
                                <a href="{{ route('inventory_plans.export.soupis.xlsx', $plan->id) }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel"></i> Súpis XLSX
                                </a>
                                <a href="{{ route('inventory_plans.export.zapis.xlsx', $plan->id) }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-file-earmark-excel"></i> Zápis XLSX
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox-fill fa-3x mb-3 d-block"></i>
                                Žiadne správy neboli nájdené.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
