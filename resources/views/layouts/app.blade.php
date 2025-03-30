<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Roue de la Fortune') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/main.css') }}" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container">
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="Roue de la Fortune" height="40">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Accueil</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Participer</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('rules') ? 'active' : '' }}" href="{{ route('rules') }}">Règlement</a>
                            </li>
                            @auth
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Administration
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <li><a class="dropdown-item" href="{{ route('filament.admin.pages.dashboard') }}">Tableau de bord</a></li>
                                        <li><a class="dropdown-item" href="{{ route('filament.admin.resources.participants.index') }}">Participants</a></li>
                                        <li><a class="dropdown-item" href="{{ route('filament.admin.resources.entries.index') }}">Participations</a></li>
                                        <li><a class="dropdown-item" href="{{ route('filament.admin.resources.prizes.index') }}">Prix</a></li>
                                        <li><a class="dropdown-item" href="{{ route('filament.admin.resources.qr-codes.index') }}">QR Codes</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Se déconnecter</button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Connexion</a>
                                </li>
                            @endauth
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <main class="app-content py-4">
            <div class="container">
                @yield('content')
            </div>
        </main>

        <footer class="app-footer bg-dark text-white py-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Roue de la Fortune</h5>
                        <p>Une expérience de jeu interactive pour gagner des prix.</p>
                    </div>
                    <div class="col-md-3">
                        <h5>Liens</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('home') }}" class="text-white">Accueil</a></li>
                            <li><a href="{{ route('register') }}" class="text-white">Participer</a></li>
                            <li><a href="{{ route('rules') }}" class="text-white">Règlement</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h5>Contact</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-envelope me-2"></i> contact@rouedelafortune.com</li>
                            <li><i class="fas fa-phone me-2"></i> +33 1 23 45 67 89</li>
                        </ul>
                    </div>
                </div>
                <hr class="bg-light">
                <div class="text-center">
                    <p>&copy; {{ date('Y') }} Roue de la Fortune. Tous droits réservés.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
