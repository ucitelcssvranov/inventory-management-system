@extends('layouts.app')

@section('title', 'Majetok')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-box-seam me-2"></i>Majetok</h1>
            <a href="{{ route('assets.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Pridať majetok
            </a>
        </div>

        <form method="GET" class="mb-3">
            <div class="row g-2 mb-2">
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Názov zariadenia" value="{{ request('name') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="inventory_number" class="form-control" placeholder="Inventárne číslo" value="{{ request('inventory_number') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="serial_number" class="form-control" placeholder="Sériové číslo" value="{{ request('serial_number') }}">
                </div>
                <div class="col-md-3">
                    <select name="category_id" class="form-select">
                        <option value="">Všetky kategórie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-2">
                    <select name="commission" class="form-select">
                        <option value="">Všetky komisie</option>
                        @foreach($commissions as $commission)
                            <option value="{{ $commission }}" {{ request('commission') == $commission ? 'selected' : '' }}>
                                {{ $commission }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="owner" class="form-select">
                        <option value="">Všetci vlastníci</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner }}" {{ request('owner') == $owner ? 'selected' : '' }}>
                                {{ $owner }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Všetky stavy</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktívny</option>
                        <option value="written_off" {{ request('status') == 'written_off' ? 'selected' : '' }}>Odpísaný</option>
                        <option value="in_repair" {{ request('status') == 'in_repair' ? 'selected' : '' }}>V oprave</option>
                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Stratený</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel"></i> Filtrovať
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i> Zrušiť filter
                    </a>
                </div>
            </div>
        </form>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">Nájdených {{ $assets->count() }} položiek</small>
                    @if(request()->hasAny(['name', 'inventory_number', 'serial_number', 'category_id', 'commission', 'owner', 'status']))
                        <small class="text-info">
                            <i class="bi bi-funnel"></i> Aktívny filter
                        </small>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Inventárne číslo</th>
                                <th>Názov</th>
                                <th>Sériové číslo</th>
                                <th>Kategória</th>
                                <th>Lokácia</th>
                                <th>Komisia</th>
                                <th>Vlastník</th>
                                <th>Obstarávacia cena</th>
                                <th>Stav</th>
                                <th>Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                                <tr>
                                    <td><code>{{ $asset->inventory_number }}</code></td>
                                    <td>{{ $asset->name }}</td>
                                    <td>
                                        <code>{{ $asset->serial_number ?? '-' }}</code>
                                    </td>
                                    <td>
                                        @if($asset->category)
                                            <span class="badge bg-secondary">{{ $asset->category->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($asset->location)
                                            <i class="bi bi-geo-alt me-1"></i>{{ $asset->location->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $asset->inventory_commission ?? '-' }}</td>
                                    <td>{{ $asset->owner ?? '-' }}</td>
                                    <td>{{ number_format($asset->acquisition_cost, 2) }} €</td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'active' => 'bg-success',
                                                'written_off' => 'bg-danger',
                                                'in_repair' => 'bg-warning',
                                                'lost' => 'bg-dark'
                                            ];
                                            $statusLabels = [
                                                'active' => 'Aktívny',
                                                'written_off' => 'Odpísaný',
                                                'in_repair' => 'V oprave',
                                                'lost' => 'Stratený'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClasses[$asset->status] }}">
                                            {{ $statusLabels[$asset->status] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('assets.show', $asset->id) }}" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-eye"></i> Zobraziť
                                        </a>
                                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Upraviť
                                        </a>
                                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Naozaj chcete zmazať?')">
                                                <i class="bi bi-trash"></i> Zmazať
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox-fill fa-3x mb-3 d-block"></i>
                                        Žiadny majetok nebol nájdený.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

