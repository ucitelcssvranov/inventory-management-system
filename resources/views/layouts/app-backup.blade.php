<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{ config('app.name', 'Inventarizácia CSŠ Vranov') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    
    <!-- Bootstrap 5.3 from CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUa6C8+T5+mZgVZMObEZM2WiClP7ps69Y2YW5yHnVGrFe1k7KhA4IKJu7w+q" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous")

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    Inventarizácia CSŠ Vranov
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <!-- Admin menu -->
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('assets.index') }}">
                                        <i class="bi bi-archive me-1"></i> Majetok
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory_plans.index') }}">
                                        <i class="bi bi-journal-text me-1"></i> Inventarizačné plány
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory-commissions.index') }}">
                                        <i class="bi bi-people me-1"></i> Komisie
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('locations.index') }}">
                                        <i class="bi bi-geo-alt me-1"></i> Lokácie
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('categories.index') }}">
                                        <i class="bi bi-tags me-1"></i> Kategórie
                                    </a>
                                </li>
                                <!-- Logout button for admin users -->
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
                                        <i class="bi bi-box-arrow-right me-1"></i> Odhlásiť sa
                                    </a>
                                </li>
                            @else
                                <!-- User menu -->
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory-commissions.index') }}">
                                        <i class="bi bi-people me-1"></i> Moje komisie
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory-commissions.index') }}">
                                        <i class="bi bi-people me-1"></i> Moje komisie
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory_plans.index') }}">
                                        <i class="bi bi-journal-text me-1"></i> Plány komisií
                                    </a>
                                </li>
                                <!-- Logout button for regular users -->
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form-navbar').submit();">
                                        <i class="bi bi-box-arrow-right me-1"></i> Odhlásiť sa
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="{{ route('inventory_reports.index') }}">
                                    <i class="bi bi-file-earmark-text me-1"></i> Správy z inventarizácie
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-person-circle me-1"></i>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ url('/profile') }}">
                                        <i class="bi bi-person-lines-fill me-1"></i> Môj profil
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-1"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                    
                                    <!-- Additional logout form for navbar button (regular users) -->
                                    <form id="logout-form-navbar" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                    
                                    <!-- Additional logout form for admin navbar button -->
                                    <form id="logout-form-admin" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container py-4">
            <main>
                @yield('content')
            </main>
        </div>
    </div>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- Autocomplete Script -->
        <!-- Custom Scripts -->
    <script src="{{ asset('js/inventory-app.js') }}"></script>
</body>
    
    <!-- Debug script -->
    <script>
        // Check if Bootstrap is loaded
        console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
        console.log('jQuery loaded:', typeof $ !== 'undefined');
        
        // Check for CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        console.log('CSRF token found:', !!csrfToken);
        if (csrfToken) {
            console.log('CSRF token value:', csrfToken.getAttribute('content'));
        }
    </script>
    
    @stack('scripts')
</body>
</html>
