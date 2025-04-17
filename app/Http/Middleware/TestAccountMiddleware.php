<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use App\Helpers\TestAccountHelper;

class TestAccountMiddleware
{
    /**
     * Gère les comptes de test pour empêcher la création de cookies
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Exécuter la requête normalement
        $response = $next($request);
        
        // Obtenir l'email de l'utilisateur
        $userEmail = null;
        
        // Vérifier l'utilisateur connecté
        if (auth()->check()) {
            $userEmail = auth()->user()->email;
        }
        
        // Si un formulaire d'inscription a été soumis, récupérer l'email
        if ($request->has('email')) {
            $userEmail = $request->email;
        }
        
        // Vérifier via un participant existant dans la session
        if (!$userEmail && $request->session()->has('participant_id')) {
            $participant = \App\Models\Participant::find($request->session()->get('participant_id'));
            if ($participant) {
                $userEmail = $participant->email;
            }
        }
        
        // Si c'est un compte de test, supprimer tous les cookies de la réponse
        if ($userEmail && TestAccountHelper::isTestAccount($userEmail)) {
            // Marquer comme compte de test dans la session (sans cookie)
            if (!$request->session()->has('is_test_account')) {
                $companyName = TestAccountHelper::getCompanyName($userEmail);
                $request->session()->put('is_test_account', true);
                $request->session()->put('test_account_company', $companyName);
            }
            
            // S'assurer que le cookie de session est configuré pour expirer immédiatement
            config(['session.expire_on_close' => true]);
            
            // Si c'est une réponse avec des cookies, les supprimer tous
            if (method_exists($response, 'headers') && $response->headers->has('Set-Cookie')) {
                $cookieNames = [];
                
                // Récupérer tous les noms de cookies définis dans la réponse
                foreach ($response->headers->getCookies() as $cookie) {
                    $cookieNames[] = $cookie->getName();
                }
                
                // Supprimer chaque cookie
                foreach ($cookieNames as $cookieName) {
                    $response->headers->removeCookie($cookieName);
                }
                
                // Ajouter un en-tête de débogage
                $response->headers->set('X-Test-Account', 'Cookies-Removed');
            }
            
            // Effacer localStorage via JavaScript
            if ($response instanceof Response || $response instanceof \Illuminate\Http\Response) {
                $content = $response->getContent();
                
                // Vérifier si c'est du HTML
                if (strpos($content, '</body>') !== false) {
                    // Ajouter un script pour effacer localStorage à la fin du body
                    $script = '<script>
                        // Effacer localStorage pour les comptes de test
                        localStorage.removeItem("contest_played_1");
                        localStorage.removeItem("played_this_week");
                        console.log("Mode test: localStorage nettoyé");
                    </script>';
                    
                    $content = str_replace('</body>', $script . '</body>', $content);
                    $response->setContent($content);
                }
            }
        }
        
        return $response;
    }
}
