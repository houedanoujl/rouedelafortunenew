<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;
use Illuminate\Http\Request;

class CustomFilamentAuthenticate extends FilamentAuthenticate
{
    protected function authenticate($request, array $guards): void
    {
        auth()->shouldUse('web');

        if (auth()->guest()) {
            $this->unauthenticated($request, $guards);
        }
    }
}
