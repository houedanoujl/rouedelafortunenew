<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CheckOnePlayPerWeek
{
    /**
     * Gère le cookie de participation hebdomadaire
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est noob@saibot.com (compte de test)
        $specialTestEmail = 'noob@saibot.com';
        
        // Récupérer l'utilisateur connecté ou le participant
        $isTestUser = false;
        
        // Vérifier l'utilisateur connecté
        if (auth()->check() && auth()->user()->email === $specialTestEmail) {
            $isTestUser = true;
        }
        
        // Si un formulaire d'inscription a été soumis, vérifier l'email
        if ($request->has('email') && $request->email === $specialTestEmail) {
            $isTestUser = true;
        }
        
        // Vérifier via un participant existant dans la session
        if ($request->session()->has('participant_id')) {
            $participant = \App\Models\Participant::find($request->session()->get('participant_id'));
            if ($participant && $participant->email === $specialTestEmail) {
                $isTestUser = true;
            }
        }
        
        // Si c'est l'utilisateur de test, autoriser sans vérification et sans cookies
        if ($isTestUser) {
            // Ne rien stocker pour l'utilisateur spécial, juste passer au middleware suivant
            $response = $next($request);
            
            // S'assurer qu'aucun cookie n'est mis dans la réponse pour cet utilisateur
            if ($response->headers->has('Set-Cookie')) {
                // Supprimer tous les cookies de la réponse
                $cookieNames = ['played_this_week', 'laravel_session', 'XSRF-TOKEN'];
                foreach ($cookieNames as $cookieName) {
                    $response->headers->removeCookie($cookieName);
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
