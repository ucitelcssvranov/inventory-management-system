@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-lines-fill me-2"></i> Môj profil
            </div>
            <div class="card-body">
                <p><strong>Meno:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                <p><strong>Rola:</strong> {{ Auth::user()->role }}</p>
                <p><strong>Dátum registrácie:</strong> {{ Auth::user()->created_at->format('d.m.Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
