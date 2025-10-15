<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{{ config('app.name', 'Inventarizačný systém') }} - CSŠ Vranov nad Topľou</title>
    <meta name="description" content="Profesionálny inventarizačný systém pre Cirkevnú strednú školu Vranov nad Topľou">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts - Professional typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">

    <!-- Custom Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')

    <!-- Professional Styling -->
    <style>
        :root {
            --edu-primary: #1e3a8a;
            --edu-secondary: #64748b;
            --edu-accent: #059669;
            --edu-warning: #d97706;
            --edu-danger: #dc2626;
            --edu-light: #f8fafc;
            --edu-white: #ffffff;
            --edu-dark: #1e293b;
            --edu-border: #e2e8f0;
            --edu-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --edu-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--edu-light) 0%, #f1f5f9 100%);
            color: var(--edu-dark);
            line-height: 1.6;
        }

        .navbar {
            background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
            border: none;
            box-shadow: var(--edu-shadow-lg);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-family: 'Source Sans Pro', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--edu-white) !important;
            display: flex;
            align-items: center;
        }

        .navbar-brand::before {
            content: "🏫";
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--edu-white) !important;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }

        .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--edu-accent) 0%, #10b981 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .dropdown-menu {
            border: none;
            box-shadow: var(--edu-shadow-lg);
            border-radius: 0.5rem;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background-color: var(--edu-light);
            color: var(--edu-primary);
        }

        .dropdown-header {
            background: linear-gradient(135deg, var(--edu-light) 0%, #f1f5f9 100%);
            border-bottom: 1px solid var(--edu-border);
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }

        .main-content {
            min-height: calc(100vh - 200px);
        }

        .footer {
            background: linear-gradient(135deg, var(--edu-light) 0%, #f1f5f9 100%) !important;
            border-top: 1px solid var(--edu-border);
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--edu-shadow);
            transition: all 0.3s ease;
            background: var(--edu-white);
        }

        .card.hover-shadow-lg:hover {
            box-shadow: var(--edu-shadow-lg);
            transform: translateY(-2px);
        }

        .btn {
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
            color: var(--edu-white);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1e40af 0%, var(--edu-primary) 100%);
            transform: translateY(-1px);
            box-shadow: var(--edu-shadow-lg);
        }

        .alert {
            border: none;
            border-radius: 0.75rem;
            padding: 1rem 1.5rem;
            border-left: 4px solid;
        }

        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left-color: #3b82f6;
            color: #1e40af;
        }

        .table {
            background: var(--edu-white);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--edu-shadow);
        }

        .table thead th {
            background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
            color: var(--edu-white);
            font-weight: 600;
            border: none;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 0.875rem 1rem;
            border-color: var(--edu-border);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: var(--edu-light);
        }

        .form-control, .form-select {
            border: 2px solid var(--edu-border);
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--edu-primary);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

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

        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .page-header {
                padding: 1.5rem;
                text-align: center;
            }
        }

        /* Main content container with side margins */
        .main-content-container {
            margin-left: 10%;
            margin-right: 10%;
            width: 80%;
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .main-content-container {
                margin-left: 5%;
                margin-right: 5%;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Professional Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
            <div class="container-fluid px-4">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <span class="fw-bold">Inventarizačný systém</span>
                    <small class="ms-2 opacity-75 d-none d-md-inline">CSŠ Vranov</small>
                    @auth
                        @if(auth()->user()->isAdmin() && auth()->user()->isInUserMode())
                            <span class="badge bg-warning text-dark ms-2">User Mode</span>
                        @endif
                    @endauth
                </a>
                
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Navigácia">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <!-- Main Navigation -->
                    <ul class="navbar-nav me-auto">
                        @auth
                            @if(auth()->user()->hasAdminPrivileges())
                                <!-- Admin Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('assets.index') }}">
                                        <i class="bi bi-archive me-2"></i>
                                        <span>Majetok</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory_plans.index') }}">
                                        <i class="bi bi-journal-text me-2"></i>
                                        <span>Inventarizačné plány</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory-commissions.index') }}">
                                        <i class="bi bi-people me-2"></i>
                                        <span>Komisie</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('locations.index') }}">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        <span>Lokácie</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('categories.index') }}">
                                        <i class="bi bi-tags me-2"></i>
                                        <span>Kategórie</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('settings.index') }}">
                                        <i class="bi bi-gear me-2"></i>
                                        <span>Nastavenia</span>
                                    </a>
                                </li>
                            @else
                                <!-- User Navigation -->
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory-commissions.index') }}">
                                        <i class="bi bi-people me-2"></i>
                                        <span>Moje komisie</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center" href="{{ route('inventory_plans.index') }}">
                                        <i class="bi bi-journal-text me-2"></i>
                                        <span>Plány komisií</span>
                                    </a>
                                </li>
                            @endif
                        @endauth
                    </ul>

                    <!-- User Menu -->
                    <ul class="navbar-nav">
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>
                                        Prihlásenie
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center" href="{{ route('inventory_reports.index') }}">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    <span class="d-none d-lg-inline">Správy</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                        <span>{{ Auth::user()->name }}</span>
                                    </div>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="navbarDropdown">
                                    <div class="dropdown-header px-3 py-2">
                                        <strong>{{ Auth::user()->name }}</strong>
                                        <div class="small text-muted">{{ Auth::user()->email }}</div>
                                        @if(Auth::user()->isAdmin())
                                            <div class="small">
                                                @if(Auth::user()->isInUserMode())
                                                    <span class="badge bg-warning text-dark">Bežný používateľ</span>
                                                @else
                                                    <span class="badge bg-primary">Administrátor</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    
                                    @if(Auth::user()->isAdmin())
                                        <!-- User Mode Switch for Admins -->
                                        @if(Auth::user()->isInUserMode())
                                            <form method="POST" action="{{ route('user-mode.switch-to-admin') }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item d-flex align-items-center text-primary">
                                                    <i class="bi bi-shield-fill-check me-2"></i>
                                                    Prepnúť na admin režim
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('user-mode.switch-to-user') }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item d-flex align-items-center text-warning">
                                                    <i class="bi bi-person-fill me-2"></i>
                                                    Prepnúť na user režim
                                                </button>
                                            </form>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                    @endif
                                    
                                    <a class="dropdown-item d-flex align-items-center" href="{{ url('/profile') }}">
                                        <i class="bi bi-person-lines-fill me-2"></i>
                                        Môj profil
                                    </a>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('settings.show') }}">
                                        <i class="bi bi-gear me-2"></i>
                                        Nastavenia
                                    </a>
                                        Nastavenia
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    @if(auth()->user()->isAdmin())
                                        <a class="dropdown-item text-danger d-flex align-items-center" href="{{ route('logout') }}"
                                           onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
                                            <i class="bi bi-box-arrow-right me-2"></i>
                                            Odhlásiť sa
                                        </a>
                                    @else
                                        <a class="dropdown-item text-danger d-flex align-items-center" href="{{ route('logout') }}"
                                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="bi bi-box-arrow-right me-2"></i>
                                            Odhlásiť sa
                                        </a>
                                    @endif
                                    
                                    <!-- Logout Forms -->
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
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

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="container-fluid main-content-container py-4">
                @yield('content')
            </div>
        </main>

        <!-- Professional Footer -->
        <footer class="footer mt-auto py-4 bg-light border-top">
            <div class="container-fluid px-4">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            © {{ date('Y') }} Cirkevná stredná škola Vranov nad Topľou
                        </small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted">
                            Inventarizačný systém v{{ config('app.version', '1.0') }}
                        </small>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    
    <!-- Bootstrap Bundle JS (includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    
    <!-- Custom Scripts -->
    <script src="{{ asset('js/inventory-app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>