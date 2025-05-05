<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Jeu dinor 70 ans') }}</title>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-X79CM0D4S6"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-X79CM0D4S6');
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS and Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        /* Styles pour le popup de vérification d'âge */
        .age-verification-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.75);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .age-verification-popup {
            background-color: white;
            max-width: 400px;
            width: 90%;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
        }
        
        .age-verification-popup h2 {
            color: var(--honolulu-blue);
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        .age-verification-popup p {
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .age-verification-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .age-verification-buttons button {
            padding: 0.7rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: transform 0.1s, box-shadow 0.2s;
        }
        
        .age-verification-buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-age-yes {
            background-color: #0079B2;
            color: white;
        }
        
        .btn-age-no {
            background-color: #D03A2C;
            color: white;
        }
        
        :root {
            /* Nouvelle palette de couleurs flat */
            --honolulu-blue: #0079B2ff;
            --apple-green: #86B942ff;
            --school-bus-yellow: #F7DB15ff;
            --persian-red: #D03A2Cff;
            --sea-green: #049055ff;
            --light-gray: #f5f5f5;
            --dark-gray: #333333;
            --primary-color: var(--school-bus-yellow);
            --secondary-color: var(--persian-red);
        }
        
        body {
            font-family: 'EB Garamond', serif;
            position: relative;
            min-height: 100vh;
            text-align: center;
            font-weight: normal;
            font-size: 1.2rem;
            line-height: 1.6;
            color: #000000;
            letter-spacing: 0.02em;
        }
        .register-container{

  align-items: center !important;
  z-index: 1;
  position: relative
        }
        
        .min-h-screen {
            background-image: url('/assets/images/web.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            color: var(--dark-gray);
            font-family: 'EB Garamond', serif;
            letter-spacing: 0.02em;
            line-height: 1.8;
            position: relative;
            min-height: 100vh;
        }
        
        /* Ajout d'un pseudo-élément pour le fond flou */
        .min-h-screen::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/assets/images/web.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            filter: blur(20px);
            opacity: 0.8;
            z-index: -1;
            transform: scale(3); /* Légèrement agrandi pour éviter les bords transparents dus au flou */
        }
        
        @media (max-width: 768px) {
            .min-h-screen {
                background-size: auto 100%;
                background-position: center;
            }
            
            .min-h-screen::before {
                background-size: auto 100%;
            }
        }
        
        /* Suppression de l'effet matelassé en 3D */
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'EB Garamond', serif;
            font-weight: normal;
            letter-spacing: 0.05em;
            color: #000000;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        h1 {
            font-size: 2.5rem;
            position: relative;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 3px;
            background-color: var(--persian-red);
        }
        
        h2 {
            font-size: 2rem;
            color: var(--persian-red);
        }
        
        /* Style des boutons flat */
        .btn-primary {
            background-color: var(--school-bus-yellow) !important;
            border: none;
            font-family: 'EB Garamond', serif;
            font-size: 1.1rem;
            letter-spacing: 0.05em;
            padding: 0.5rem 1.2rem;
            position: relative;
            transition: all 0.3s ease;
            border-radius: 0.25rem;
            text-transform: uppercase;
            font-weight: normal;
            color: var(--dark-gray);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: #e6cc00 !important;
            opacity: 0.9;
        }
                
        .btn-primary:active {
            background-color: #d4bd00 !important;
        }
        
        .btn-secondary {
            background-color: var(--persian-red);
            border: none;
            color: white;
        }
        
        .btn-secondary:hover, .btn-secondary:focus {
            background-color: #b73224;
            color: white;
        }
        
        /* Style navbar flat */
        .navbar {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 2px solid var(--persian-red);
            padding: 0.6rem 0;
            position: relative;
            display:none;
        }
        
        .navbar-brand img {
            height: 50px;
            width: auto;
        }
        
        .nav-link {
            color: var(--dark-gray);
            font-size: 1.1rem;
            font-weight: normal;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--persian-red);
        }
        
        /* Style des cartes flat */
        .card {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.25rem;
            overflow: hidden;
            position: relative;
            padding: 0;
            margin-bottom: 1.5rem;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--persian-red);
            z-index: 1;
        }
        
        .card-body {
            padding: 1.8rem;
        }
        
        p {
            margin-bottom: 1.2rem;
            font-size: 1.2rem;
            line-height: 1.8;
        }
        
        .lead {
            font-size: 1.4rem;
            line-height: 1.9;
            margin-bottom: 1.5rem;
        }
        
        .alert {
            font-size: 1.2rem;
            line-height: 1.7;
            border-radius: 8px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Style pour les card-header */
        .card-header {
            padding: 1.2rem;
            background-color: var(--primary-red);
            color: white;
            border-bottom: none;
            display: none;
        }
        
        /* Style spécifique pour les card-headers avec bg-primary */
        .card-header.bg-primary {
            background-color: var(--honolulu-blue) !important;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .card-footer {
            padding: 1rem 1.25rem;
            background-color: white;
            border-top: 1px solid #e0e0e0;
        }
    </style>


</head>
<body class="font-sans antialiased">

    <div class="min-h-screen">
        <header class="py-2">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <a href="{{ route('home') }}" class="navbar-brand">
                            <img src="/assets/images/logo.jpg" alt="Jeu dinor 70 ans Logo" class="img-fluid hide hidden">
                            <span class="ms-2">Jeu dinor 70 ans</span>
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item">
                                    <a class="nav-link {{ Route::currentRouteName() == 'home' ? 'active' : '' }}" href="{{ route('home') }}">
                                        <i class="bi bi-house-door"></i> Accueil
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="/admin" target="_blank">
                                        <i class="bi bi-shield-lock"></i> Administration
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Modal pour les règles du jeu -->
    @include('partials.rules-modal')

    <!-- jQuery + Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    @livewireScripts
    @stack('scripts')

</body>
</html>
