<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LogSessionSeparator
{
    /**
     * Ajoute une ligne de séparation dans les logs pour séparer les sessions
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('================================================================');
        Log::info('================ DÉBUT DE SESSION ' . date('Y-m-d H:i:s') . ' ================');
        Log::info('================================================================');
        
        return $next($request);
    }

    /**
     * Ajoute une ligne de séparation à la fin de la session
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        Log::info('================================================================');
        Log::info('================= FIN DE SESSION ' . date('Y-m-d H:i:s') . ' =================');
        Log::info('================================================================');
    }
}
