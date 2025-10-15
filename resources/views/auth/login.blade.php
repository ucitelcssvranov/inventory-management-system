@extends('layouts.app')

@push('styles')
<style>
.login-container {
    min-height: 100vh;
    background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
    display: flex;
    align-items: center;
    padding: 2rem 0;
}

.login-card {
    background: var(--edu-white);
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    max-width: 450px;
    width: 100%;
}

.login-header {
    background: linear-gradient(135deg, var(--edu-white) 0%, var(--edu-light) 100%);
    padding: 3rem 2rem 2rem;
    text-align: center;
    border-bottom: 1px solid var(--edu-border);
}

.school-logo {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--edu-primary);
}

.login-title {
    font-family: 'Source Sans Pro', sans-serif;
    font-weight: 700;
    color: var(--edu-dark);
    margin-bottom: 0.5rem;
}

.login-subtitle {
    color: var(--edu-secondary);
    font-size: 1rem;
    margin: 0;
}

.login-form {
    padding: 2rem;
}

.form-floating {
    margin-bottom: 1.5rem;
}

.form-floating .form-control {
    border: 2px solid var(--edu-border);
    border-radius: 0.5rem;
    padding: 1rem 0.75rem;
    height: auto;
    background: var(--edu-white);
}

.form-floating .form-control:focus {
    border-color: var(--edu-primary);
    box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
}

.form-floating label {
    color: var(--edu-secondary);
    font-weight: 500;
}

.btn-login {
    background: linear-gradient(135deg, var(--edu-primary) 0%, #2563eb 100%);
    border: none;
    border-radius: 0.5rem;
    padding: 0.875rem 2rem;
    font-weight: 600;
    color: white;
    width: 100%;
    transition: all 0.3s ease;
}

.btn-login:hover {
    background: linear-gradient(135deg, #1e40af 0%, var(--edu-primary) 100%);
    transform: translateY(-1px);
    box-shadow: var(--edu-shadow-lg);
}

.remember-me {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 1.5rem 0;
}

.form-check-input:checked {
    background-color: var(--edu-primary);
    border-color: var(--edu-primary);
}

.forgot-password {
    color: var(--edu-primary);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
}

.forgot-password:hover {
    color: #1e40af;
    text-decoration: underline;
}

.login-footer {
    background: var(--edu-light);
    padding: 1.5rem 2rem;
    text-align: center;
    border-top: 1px solid var(--edu-border);
}

.login-footer small {
    color: var(--edu-secondary);
}

.divider {
    position: relative;
    text-align: center;
    margin: 1.5rem 0;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--edu-border);
    z-index: 1;
}

.divider-text {
    background: var(--edu-white);
    padding: 0 1rem;
    color: var(--edu-secondary);
    font-size: 0.9rem;
    position: relative;
    z-index: 2;
}

.btn-microsoft {
    background: #0078d4;
    border: 2px solid #0078d4;
    border-radius: 0.5rem;
    padding: 0.875rem 2rem;
    font-weight: 600;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-microsoft:hover {
    background: #106ebe;
    border-color: #106ebe;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 120, 212, 0.3);
}

.btn-microsoft:focus {
    box-shadow: 0 0 0 3px rgba(0, 120, 212, 0.2);
}

@media (max-width: 768px) {
    .login-container {
        padding: 1rem;
    }
    
    .login-header {
        padding: 2rem 1.5rem 1.5rem;
    }
    
    .login-form {
        padding: 1.5rem;
    }
    
    .school-logo {
        font-size: 3rem;
    }
}
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <!-- Header -->
                    <div class="login-header">
                        <div class="school-logo">🏫</div>
                        <h2 class="login-title">Prihlásenie do systému</h2>
                        <p class="login-subtitle">Cirkevná stredná škola Vranov nad Topľou</p>
                    </div>

                    <!-- Login Form -->
                    <div class="login-form">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Field -->
                            <div class="form-floating">
                                <input id="email" 
                                       type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autocomplete="email" 
                                       autofocus
                                       placeholder="Emailová adresa">
                                <label for="email">
                                    <i class="bi bi-envelope me-2"></i>Emailová adresa
                                </label>
                                @error('email')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Password Field -->
                            <div class="form-floating">
                                <input id="password" 
                                       type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" 
                                       required 
                                       autocomplete="current-password"
                                       placeholder="Heslo">
                                <label for="password">
                                    <i class="bi bi-lock me-2"></i>Heslo
                                </label>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="remember-me">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember" 
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Zapamätať si ma
                                    </label>
                                </div>
                                @if (Route::has('password.request'))
                                    <a class="forgot-password" href="{{ route('password.request') }}">
                                        Zabudli ste heslo?
                                    </a>
                                @endif
                            </div>

                            <!-- Login Button -->
                            <button type="submit" class="btn btn-login">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Prihlásiť sa
                            </button>
                        </form>

                        <!-- Microsoft Login Section -->
                        <div class="text-center my-3">
                            <div class="divider">
                                <span class="divider-text">alebo</span>
                            </div>
                        </div>

                        <a href="{{ route('login.microsoft') }}" class="btn btn-microsoft d-block">
                            <i class="bi bi-microsoft me-2"></i>
                            Prihlásiť sa cez Microsoft
                        </a>
                    </div>

                    <!-- Footer -->
                    <div class="login-footer">
                        <small>
                            © {{ date('Y') }} Inventarizačný systém v{{ config('app.version', '1.0') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
