<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use App\Helpers\TestAccountHelper;

class CheckOnePlayPerWeek
{
    /**
     * Gère le cookie de participation hebdomadaire
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Récupérer l'email de l'utilisateur à partir de différentes sources
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
        
        // Vérifier si c'est un compte de test en utilisant notre helper
        $isTestAccount = TestAccountHelper::isTestAccount($userEmail);
        
        // Si c'est un compte de test, stocker les informations dans la session et autoriser sans restriction
        if ($isTestAccount) {
            // Stocker des informations dans la session pour l'affichage de la bannière
            $companyName = TestAccountHelper::getCompanyName($userEmail);
            $request->session()->put('is_test_account', true);
            $request->session()->put('test_account_company', $companyName);
            
            // Ne rien stocker pour les utilisateurs de test, juste passer au middleware suivant
            $response = $next($request);
            
            // S'assurer qu'aucun cookie n'est mis dans la réponse pour cet utilisateur
            if (method_exists($response, 'headers') && $response->headers->has('Set-Cookie')) {
                // Récupérer tous les noms de cookies définis dans la réponse
                $cookieNames = [];
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
            
            // Ajouter un script pour supprimer localStorage si c'est une réponse HTML
            if ($response instanceof Response || $response instanceof \Illuminate\Http\Response) {
                $content = $response->getContent();
                
                // Vérifier si c'est du HTML et qu'il contient une balise body
                if (is_string($content) && strpos($content, '</body>') !== false) {
                    // Ajouter un script pour effacer localStorage à la fin du body
                    $script = '<script>
                        // Effacer localStorage pour les comptes de test
                        localStorage.removeItem("contest_played_1");
                        localStorage.removeItem("played_this_week");
                        
                        // Supprimer tous les cookies via JavaScript
                        document.cookie.split(";").forEach(function(c) { 
                            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
                        });
                        
                        console.log("Mode test: localStorage et cookies nettoyés");
                    </script>';
                    
                    $content = str_replace('</body>', $script . '</body>', $content);
                    $response->setContent($content);
                }
            }
            
            return $response;
        }
        
        // Pour les autres utilisateurs, appliquer la restriction normale
        $cookieName = 'played_this_week';
        
        // Si le cookie existe, calculer les jours restants
        if ($request->cookie($cookieName)) {
            $playedDate = \DateTime::createFromFormat('Y-m-d', $request->cookie($cookieName));
            $now = new \DateTime();
            $interval = $playedDate->diff($now);
            $daysRemaining = max(0, 7 - $interval->days);
            
            // Si le délai n'est pas encore écoulé, rediriger avec message
            if ($daysRemaining > 0) {
                return redirect()->route('home')->with([
                    'warning' => 'Vous avez déjà participé cette semaine.',
                    'days_remaining' => $daysRemaining,
                    'next_play_date' => $playedDate->modify('+7 days')->format('d/m/Y')
                ]);
            } else {
                // Si le délai est écoulé, supprimer le cookie pour permettre une nouvelle participation
                Cookie::queue(Cookie::forget($cookieName));
            }
        }
        
        return $next($request);
    }
}
