<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Http\Request;

class CustomFilamentAuthenticate extends FilamentAuthenticate
{
    protected function authenticate($request, array $guards): void
    {
        // Utiliser le garde web par défaut
        $guard = config('filament.auth.guard', 'web');
        auth()->shouldUse($guard);

        // Vérifier si l'utilisateur est authentifié
        if (auth()->check()) {
            return; // L'utilisateur est authentifié, continuer
        }

        // Si l'utilisateur n'est pas authentifié, rediriger vers la page de connexion
        $this->redirectTo($request);
    }

    // La signature de cette méthode doit correspondre exactement à celle de la classe parente
    protected function redirectTo($request): ?string
    {
        return route('filament.admin.auth.login');
    }
}
