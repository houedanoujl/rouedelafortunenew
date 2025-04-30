<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Entry;

class VerifySpinAuthenticity
{
    /**
     * Vérifie que l'accès à la page de résultat est autorisé pour cette session
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Récupérer l'entrée depuis la route
        $entry = $request->route('entry');
        
        if (!$entry || !($entry instanceof Entry)) {
            Log::warning('Tentative d\'accès à /spin/result sans entrée valide');
            return redirect()->route('home')->with('error', 'Accès non autorisé.');
        }
        
        // 1. Vérifier la session pour confirmer que cette entrée a été jouée pendant cette session
        $sessionKey = 'played_entry_' . $entry->id;
        $entryPlayed = $request->session()->has($sessionKey);
        
        // 2. Vérifier le token d'authentification unique pour cette entrée
        $authToken = $request->session()->get('entry_auth_token_' . $entry->id);
        $requestToken = $request->query('token');
        
        // 3. Vérifier l'empreinte de l'appareil si disponible
        $deviceFingerprint = $request->cookie('device_fingerprint');
        $storedFingerprint = $request->session()->get('device_fingerprint');
        
        $fingerprintValid = ($deviceFingerprint && $storedFingerprint && $deviceFingerprint === $storedFingerprint);
        $tokenValid = ($authToken && $requestToken && $authToken === $requestToken);
        
        // Si l'entrée a été jouée et qu'au moins l'un des mécanismes d'authentification est valide
        if ($entryPlayed && ($tokenValid || $fingerprintValid)) {
            return $next($request);
        }
        
        // Si l'entrée a été jouée mais que l'authentification échoue, enregistrer la tentative
        if ($entryPlayed) {
            Log::warning('Tentative d\'accès non autorisé à un résultat de spin', [
                'entry_id' => $entry->id,
                'token_valid' => $tokenValid,
                'fingerprint_valid' => $fingerprintValid,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }
        
        // Rediriger vers la page d'accueil avec un message d'erreur
        return redirect()->route('home')->with('error', 'Accès non autorisé. Cette URL est personnelle et ne peut pas être partagée.');
    }
}
