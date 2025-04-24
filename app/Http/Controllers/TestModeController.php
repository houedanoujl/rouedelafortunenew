<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class TestModeController extends Controller
{
    /**
     * Nettoie tous les cookies et redirige vers la page d'accueil.
     * Cette méthode utilise une approche plus directe et agressive
     * pour supprimer les cookies persistants comme 70_ans_dinor_session
     * et contest_played_1.
     */
    public function clearAllCookies(Request $request)
    {
        // Récupérer tous les cookies de la requête
        $cookies = $request->cookies->all();
        
        // Préparer une réponse pour la redirection
        $response = redirect()->route('home')->with('cookies_cleared', true);
        
        // Supprimer tous les cookies standards
        foreach ($cookies as $name => $value) {
            Cookie::queue(Cookie::forget($name));
            $response->withoutCookie($name);
        }
        
        // Supprimer spécifiquement les cookies problématiques
        $problemCookies = [
            '70_ans_dinor_session',
            'contest_played_1',
            'XSRF-TOKEN',
            'laravel_session'
        ];
        
        $paths = ['/', '/home', '/spin', '/register', '/result', ''];
        
        foreach ($problemCookies as $cookieName) {
            // Méthode standard Laravel
            Cookie::queue(Cookie::forget($cookieName));
            $response->withoutCookie($cookieName);
            
            // Approche plus agressive avec différents chemins
            foreach ($paths as $path) {
                // Créer un cookie expiré avec le même nom
                $expiredCookie = cookie($cookieName, '', -1, $path);
                Cookie::queue($expiredCookie);
                $response->withCookie($expiredCookie);
                
                // Utiliser également la méthode withoutCookie
                $response->withoutCookie($cookieName, $path);
            }
        }
        
        // Ajouter un indicateur pour déboguer
        $response->header('X-Cookies-Cleared', implode(',', array_keys($cookies)));
        
        // Ajouter un paramètre à l'URL pour débogage
        return $response->with('message', 'Tous les cookies ont été nettoyés. Mode test activé.');
    }

    /**
     * Désactive le mode test et nettoie toutes les données de session, cookies et localStorage.
     * Cette méthode est conçue pour être utilisée lorsqu'on veut quitter le mode test.
     */
    public function exitTestMode(Request $request)
    {
        // Supprimer la variable de session is_test_account
        $request->session()->forget('is_test_account');
        
        // Supprimer tous les cookies
        $cookies = $request->cookies->all();
        $response = redirect()->route('home');
        
        // Liste étendue des cookies à supprimer
        $problemCookies = [
            '70_ans_dinor_session',
            'contest_played_1',
            'XSRF-TOKEN',
            'laravel_session',
            'played_this_week',
            'already_participated'
        ];
        
        foreach ($problemCookies as $cookieName) {
            Cookie::queue(Cookie::forget($cookieName));
            $response->withoutCookie($cookieName);
        }
        
        // Ajouter des instructions JavaScript pour nettoyer localStorage
        $clearLocalStorage = "
            <script>
                // Nettoyer tout le localStorage
                localStorage.clear();
                
                // Nettoyer spécifiquement les clés problématiques
                const keysToRemove = [
                    'contest_played_1',
                    'played_this_week',
                    'age_verified',
                    'prevent_localstorage_recreation'
                ];
                
                keysToRemove.forEach(key => localStorage.removeItem(key));
                
                // Si c'est la redirection après sortie du mode test, ne pas recréer le localStorage
                if (window.location.href.includes('test_mode_exited=true')) {
                    console.log('Mode test désactivé, localStorage nettoyé.');
                }
                
                // Rediriger vers la page d'accueil sans paramètres
                setTimeout(() => {
                    window.location.href = '/home';
                }, 1000);
            </script>
        ";
        
        // Retourner une page intermédiaire pour exécuter le script JavaScript
        return response()->view('test-mode.exited', [
            'clearScript' => $clearLocalStorage
        ]);
    }
}
