<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Http\Request;

class FilamentAuthenticate extends FilamentAuthenticate
{
    protected function authenticate(, array ): void
    {
        ->auth->shouldUse('web');

        if (->auth->guest()) {
            ->unauthenticated(, );
        }
    }
}
