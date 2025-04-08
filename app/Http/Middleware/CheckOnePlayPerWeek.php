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
