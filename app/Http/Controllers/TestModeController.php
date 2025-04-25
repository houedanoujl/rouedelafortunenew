<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class TestModeController extends Controller
{
    /**
     * Nettoie tous les cookies et redirige vers la page d'accueil.
     * Version simplifiée et plus stable pour éviter les erreurs 502.
     */
    public function clearAllCookies(Request $request)
    {
        // Cookies spécifiques à supprimer
        $cookiesToClear = [
            'contest_played_1',
            'played_this_week',
            'already_participated'
        ];
        
        // Préparer la réponse
        $response = redirect()->route('home')
            ->with('message', 'Cookies nettoyés. Mode test activé.');
        
        // Supprimer chaque cookie de manière simple
        foreach ($cookiesToClear as $name) {
            $response->withCookie(cookie($name, '', -1));
        }
        
        // Ne pas toucher aux cookies de session Laravel essentiels
        // Cela évite les problèmes avec le middleware
        
        return $response;
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
