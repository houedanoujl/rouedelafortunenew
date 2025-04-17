<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class TestMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est en mode test
        if (session('is_test_account')) {
            // Supprimer les cookies problématiques à chaque requête
            Cookie::queue(Cookie::forget('70_ans_dinor_session'));
            Cookie::queue(Cookie::forget('contest_played_1'));
            
            // Ajouter les données du mode test à la réponse
            $response = $next($request);
            
            // Supprimer à nouveau dans la réponse pour être sûr
            $response->withoutCookie('70_ans_dinor_session');
            $response->withoutCookie('contest_played_1');
            
            return $response;
        }

        return $next($request);
    }
}
