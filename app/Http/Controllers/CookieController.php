<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CookieController extends Controller
{
    /**
     * Supprime les cookies spécifiques et redirige vers l'accueil
     */
    public function clearCookies(Request $request)
    {
        // Créer une réponse minimale avec JavaScript pour supprimer les cookies
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Suppression des cookies</title>
            <style>
                body { font-family: Arial; text-align: center; padding: 50px; }
                .container { max-width: 500px; margin: 0 auto; padding: 20px; background: #f7f7f7; border-radius: 5px; }
                h1 { color: #0079B2; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Suppression des cookies en cours...</h1>
                <p>Vous allez être redirigé automatiquement.</p>
            </div>
            
            <script>
                // Supprimer les cookies côté client
                document.cookie = "70_ans_dinor_session=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
                document.cookie = "contest_played_1=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
                
                // Vider localStorage
                localStorage.removeItem("contest_played_1");
                localStorage.clear();
                
                // Rediriger après un court délai
                setTimeout(function() {
                    window.location.href = "/";
                }, 1000);
            </script>
        </body>
        </html>';
        
        // Retourner directement la page HTML
        return response($html);
    }
}
