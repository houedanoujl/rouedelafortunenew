<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Roue de la Fortune') }}</title>

    @if(session('is_test_account'))
    <script>
        // Version simplifiée et sécurisée de l'intercepteur de cookies pour mode test
        (function() {
            console.log('🛡️ MODE TEST: Nettoyage des cookies activé');
            
            // Liste des cookies à supprimer
            const cookiesToDelete = ['70_ans_dinor_session', 'contest_played_1'];
            
            // Fonction pour supprimer un cookie sur tous les chemins possibles
            function deleteCookie(name) {
                const paths = ['/', '/spin', '/register', '/result', '/home', ''];
                
                // Pour chaque chemin, essayer toutes les combinaisons possibles
                paths.forEach(path => {
                    document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${path}`;
                    document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${path}; domain=${window.location.hostname}`;
                });
                
                console.log(`Cookie ${name} supprimé pour le mode test`);
            }
            
            // Supprimer les cookies immédiatement au chargement
            cookiesToDelete.forEach(cookieName => {
                deleteCookie(cookieName);
            });
            
            // Vérifier périodiquement (toutes les 5 secondes) si les cookies sont recréés
            setInterval(() => {
                cookiesToDelete.forEach(cookieName => {
                    // Vérifier si le cookie existe avant de tenter de le supprimer
                    if (document.cookie.split(';').some(item => item.trim().startsWith(cookieName + '='))) {
                        deleteCookie(cookieName);
                    }
                });
            }, 5000);
            
            // Supprimer aussi du localStorage
            localStorage.removeItem('contest_played_1');
        })();
    </script>
    @endif

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

    <!-- Bannière de mode test pour les employés de Big Five et Sania -->
    @if(session('is_test_account'))
    <div class="test-mode-banner">
        <strong>MODE TEST ACTIVÉ</strong> pour les employés de SIFCA &{{ session('test_account_company') }} - 
        Les restrictions de jeu hebdomadaires sont désactivées . Aucun lot attribué dans ce mode ne sera envoyé ou remis.
        <button onclick="clearStorageAndRedirect()" class="test-mode-button">Retour à l'accueil</button>
    </div>
    <script>
        // Journaliser tous les cookies au chargement de la page
        console.log('COOKIES AU DÉMARRAGE:', document.cookie);
        
        function clearStorageAndRedirect() {
            // Afficher une fenêtre de confirmation
            if (!confirm("Êtes-vous sûr de vouloir nettoyer toutes les données et retourner à l'accueil ?")) {
                console.log('Opération annulée par l\'utilisateur');
                return; // Sortir de la fonction si l'utilisateur annule
            }

            console.log('====== DÉBUT DU NETTOYAGE ======');
            console.log('Cookies actuels:', document.cookie);
            console.log('LocalStorage actuel:', Object.keys(localStorage));
            
            // Supprimer les éléments du localStorage - méthode 1
            try {
                console.log('Tentative de suppression du localStorage contest_played_1');
                localStorage.removeItem('contest_played_1');
                console.log('Tentative de suppression du localStorage played_this_week');
                localStorage.removeItem('played_this_week');
                
                // Tentative de nettoyer complètement le localStorage
                console.log('Tentative de nettoyage complet du localStorage');
                localStorage.clear();
                console.log('LocalStorage nettoyé avec succès');
            } catch (e) {
                console.error('Erreur lors du nettoyage du localStorage:', e);
            }
            
            // Supprimer tous les cookies - méthode plus robuste
            try {
                console.log('Tentative de suppression des cookies');
                const cookies = document.cookie.split(';');
                console.log('Nombre de cookies trouvés:', cookies.length);
                
                // CIBLAGE SPÉCIFIQUE des cookies problématiques
                console.log('SUPPRESSION SPÉCIFIQUE des cookies problématiques');
                
                // Supprimer 70_ans_dinor_session avec toutes les combinaisons possibles
                console.log('Tentative de suppression du cookie 70_ans_dinor_session');
                document.cookie = '70_ans_dinor_session=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                document.cookie = '70_ans_dinor_session=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=' + window.location.hostname;
                document.cookie = '70_ans_dinor_session=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/spin';
                document.cookie = '70_ans_dinor_session=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/result';
                document.cookie = '70_ans_dinor_session=; expires=Thu, 01 Jan 1970 00:00:00 GMT;';
                
                // Supprimer contest_played_1 avec toutes les combinaisons possibles
                console.log('Tentative de suppression du cookie contest_played_1');
                document.cookie = 'contest_played_1=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                document.cookie = 'contest_played_1=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=' + window.location.hostname;
                document.cookie = 'contest_played_1=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/spin';
                document.cookie = 'contest_played_1=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/result';
                document.cookie = 'contest_played_1=; expires=Thu, 01 Jan 1970 00:00:00 GMT;';
                
                // Méthode 1: Suppression classique
                for (let i = 0; i < cookies.length; i++) {
                    const cookie = cookies[i];
                    const eqPos = cookie.indexOf('=');
                    const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
                    console.log('Suppression du cookie:', name);
                    
                    // Supprimer avec différentes combinaisons de path et domain
                    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=' + window.location.hostname;
                    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=.' + window.location.hostname;
                    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT;';
                }
                
                // Méthode 2: Suppression de cookies spécifiques connus dans l'application
                document.cookie = 'XSRF-TOKEN=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                document.cookie = 'laravel_session=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                document.cookie = 'contest_played_1=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
                
                // Vérifier s'il reste des cookies
                if (document.cookie) {
                    console.warn('Des cookies existent toujours après suppression:', document.cookie);
                    console.log('DÉTAIL DES COOKIES RESTANTS:');
                    document.cookie.split(';').forEach(function(cookie) {
                        console.log('  - ' + cookie.trim());
                    });
                    
                    // Méthode 3: Dernière tentative avec une approche plus agressive
                    const remainingCookies = document.cookie.split(';');
                    for (let i = 0; i < remainingCookies.length; i++) {
                        const cookie = remainingCookies[i];
                        const eqPos = cookie.indexOf('=');
                        const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
                        
                        // Essayer des combinaisons supplémentaires
                        const domains = [window.location.hostname, '.' + window.location.hostname, ''];
                        const paths = ['/', '', '/home', '/spin', '/register', '/result'];
                        
                        domains.forEach(domain => {
                            paths.forEach(path => {
                                const expireString = '=; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                                const cookieString = domain ? 
                                    `${name}${expireString}; path=${path}; domain=${domain}` : 
                                    `${name}${expireString}; path=${path}`;
                                document.cookie = cookieString;
                            });
                        });
                    }
                }
                
                // Vérification finale - ajouter un délai pour voir si les cookies sont vraiment supprimés
                setTimeout(function() {
                    console.log('VÉRIFICATION FINALE DES COOKIES (après délai):', document.cookie);
                    if (document.cookie) {
                        console.error('ÉCHEC: Des cookies existent toujours après toutes les tentatives de nettoyage');
                        console.log('LISTE DES COOKIES RESTANTS:');
                        document.cookie.split(';').forEach(function(cookie) {
                            if (cookie.trim()) {
                                console.log('  → ' + cookie.trim());
                            }
                        });
                    } else {
                        console.log('SUCCÈS: Tous les cookies ont été supprimés');
                    }
                }, 500);
                
                console.log('Cookies nettoyés avec succès');
            } catch (e) {
                console.error('Erreur lors du nettoyage des cookies:', e);
            }
            
            // Vérification finale
            console.log('Vérification après nettoyage:');
            console.log('Cookies restants:', document.cookie);
            console.log('LocalStorage restant:', Object.keys(localStorage));
            console.log('====== FIN DU NETTOYAGE ======');
            
            // Définir un indicateur dans sessionStorage (qui sera conservé pendant la navigation)
            // Ce flag indiquera à la page d'accueil de ne pas recréer le localStorage
            try {
                sessionStorage.setItem('prevent_localstorage_recreation', 'true');
                console.log('Flag de prévention défini dans sessionStorage');
            } catch (e) {
                console.error('Impossible de définir le flag dans sessionStorage:', e);
            }
            
            // Attendre un peu avant de rediriger pour s'assurer que tout est nettoyé
            console.log('Délai avant redirection...');
            setTimeout(function() {
                console.log('Redirection vers le nettoyeur de cookies côté serveur...');
                // Utiliser la route serveur pour supprimer les cookies de manière fiable
                window.location.href = '{{ route('clear.cookies') }}';
            }, 2000); // Augmenter le délai à 2 secondes pour avoir le temps de voir les logs
        }
    </script>
    <style>
        .test-mode-banner {
            background-color: #FF9800;
            color: white;
            text-align: center;
            padding: 12px;
            font-size: 16px;
            position: relative;
            z-index: 1000;
        }
        
        .test-mode-button {
            display: inline-block;
            background-color: white;
            color: #FF9800;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            margin-left: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .test-mode-button:hover {
            background-color: #f1f1f1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .min-h-screen {
            min-height: calc(100vh - 40px); /* Ajuster pour la hauteur de la bannière */
        }
    </style>
    @endif

    <div class="min-h-screen">
        <header class="py-2">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <a href="{{ route('home') }}" class="navbar-brand">
                            <img src="/assets/images/logo.jpg" alt="Roue de la Fortune Logo" class="img-fluid hide hidden">
                            <span class="ms-2">Roue de la Fortune</span>
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

    @if(session('cookie_cleanup') || session('cookies_being_cleared'))
    <script>
        // Stocker dans sessionStorage que nous avons effectué un nettoyage
        // Cette valeur sera conservée uniquement pour cette session de navigation
        sessionStorage.setItem('cookies_cleared_recently', 'true');
        
        // Fonction qui empêche la création des cookies problématiques
        (function() {
            console.log('>>> MODE PRÉVENTION DE COOKIES ACTIVÉ <<<');
            
            // Observer les changements de cookies
            const originalCookie = document.cookie;
            
            // Remplacer la propriété cookie par une version qui bloque certains cookies
            Object.defineProperty(document, 'cookie', {
                set: function(val) {
                    console.log('Tentative de création de cookie:', val);
                    
                    // Laisser passer seulement les cookies nécessaires au fonctionnement
                    if (val.indexOf('70_ans_dinor_session') !== -1 || 
                        val.indexOf('contest_played_1') !== -1) {
                        console.log('BLOQUÉ: Cookie interdit en mode nettoyage:', val);
                        return originalCookie;
                    }
                    
                    // Laisser passer les autres cookies
                    console.log('AUTORISÉ: Cookie autorisé:', val);
                    return originalCookie = val;
                },
                get: function() {
                    return originalCookie;
                }
            });
        })();
    </script>
    @endif

</body>
</html>
