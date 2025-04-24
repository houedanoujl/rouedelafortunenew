<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiTokenMiddleware
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
        $token = $request->bearerToken();
        $apiToken = env('API_TOKEN');

        if (!$token || $token !== $apiToken) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé. Token API invalide.'
            ], 401);
        }

        return $next($request);
    }
}
