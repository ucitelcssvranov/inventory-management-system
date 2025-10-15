@extends('layouts.app')

@section('title', 'QR kódy na tlač')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="bi bi-qr-code me-2"></i>QR kódy na tlač</h1>
                    <p class="text-muted">Celkom {{ count($qrCodes) }} QR kódov</p>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Tlačiť
                    </button>
                    <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Späť na zoznam
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Layout -->
    <div class="row" id="print-area">
        @forelse($qrCodes as $index => $qrCode)
            <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4 qr-card">
                <div class="card h-100 text-center p-3">
                    <div class="card-body">
                        <!-- QR Code Image -->
                        @if($qrCode['qr_code_url'])
                            <div class="qr-code-container mb-3">
                                <img src="{{ $qrCode['qr_code_url'] }}" 
                                     alt="QR kód pre {{ $qrCode['inventory_number'] }}"
                                     class="img-fluid"
                                     style="max-width: 150px; max-height: 150px;">
                            </div>
                        @else
                            <div class="qr-code-placeholder mb-3 bg-light d-flex align-items-center justify-content-center" 
                                 style="height: 150px; border: 2px dashed #ddd;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-qr-code fs-1"></i>
                                    <div class="small">QR kód sa generuje...</div>
                                </div>
                            </div>
                        @endif

                        <!-- Asset Information -->
                        <div class="asset-info">
                            <h6 class="fw-bold mb-2">{{ $qrCode['inventory_number'] }}</h6>
                            
                            @if($qrCode['show_text'])
                                <div class="asset-details">
                                    <div class="small text-truncate mb-1" title="{{ $qrCode['name'] }}">
                                        <strong>{{ $qrCode['name'] }}</strong>
                                    </div>
                                    
                                    @if($qrCode['asset']->category)
                                        <div class="small text-muted mb-1">
                                            {{ $qrCode['asset']->category->name }}
                                        </div>
                                    @endif
                                    
                                    @if($qrCode['asset']->location)
                                        <div class="small text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $qrCode['asset']->location->name }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Page break every 12 items for better printing --}}
            @if(($index + 1) % 12 == 0 && !$loop->last)
                <div class="w-100 page-break"></div>
            @endif
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <h3 class="text-muted mt-3">Žiadne QR kódy na tlač</h3>
                    <p class="text-muted">Neboli vybrané žiadne položky majetku.</p>
                    <a href="{{ route('assets.index') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Späť na zoznam majetku
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
    @media print {
        .btn-group {
            display: none !important;
        }
        
        .qr-card {
            break-inside: avoid;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
        
        .container-fluid {
            padding: 0 !important;
        }
        
        /* Print optimization */
        body {
            font-size: 12px !important;
        }
        
        .qr-code-container img {
            max-width: 120px !important;
            max-height: 120px !important;
        }
    }
    
    @media screen {
        .qr-card {
            transition: transform 0.2s;
        }
        
        .qr-card:hover {
            transform: translateY(-5px);
        }
        
        .qr-code-container {
            transition: all 0.3s;
        }
        
        .qr-code-container:hover {
            transform: scale(1.05);
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for missing QR codes and generate them
    const placeholders = document.querySelectorAll('.qr-code-placeholder');
    
    if (placeholders.length > 0) {
        // Show a notice about generating QR codes
        const notice = document.createElement('div');
        notice.className = 'alert alert-info alert-dismissible fade show';
        notice.innerHTML = `
            <i class="bi bi-info-circle me-2"></i>
            Generujú sa QR kódy pre ${placeholders.length} položiek majetku...
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid').insertBefore(notice, document.querySelector('.row'));
        
        // Auto-refresh page after a delay to show generated QR codes
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    }
});

// Print function
function printQrCodes() {
    window.print();
}

// Keyboard shortcut for printing
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printQrCodes();
    }
});
</script>
@endpush
@endsection