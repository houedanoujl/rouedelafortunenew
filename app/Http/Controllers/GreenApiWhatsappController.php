<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class GreenApiWhatsappController extends Controller
{
    public function showForm()
    {
        return view('green-api-whatsapp-test');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        $status = null;
        try {
            // Formater le numéro de téléphone (supprimer les espaces et le signe + si présent)
            $phoneNumber = $request->input('to');
            $phoneNumber = preg_replace('/\s+/', '', $phoneNumber);
            $phoneNumber = ltrim($phoneNumber, '+');
            
            // Récupération des informations d'API
            $idInstance = config('services.greenapi.id_instance');
            $apiTokenInstance = config('services.greenapi.api_token');
            $apiUrl = config('services.greenapi.api_url');
            
            // Construction de l'URL pour l'API Green API - Méthode sendMessage
            $url = "{$apiUrl}/waInstance{$idInstance}/sendMessage/{$apiTokenInstance}";
            
            $client = new Client();
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'chatId' => $phoneNumber . '@c.us',
                    'message' => $request->input('message')
                ],
            ]);
            
            $body = json_decode($response->getBody(), true);
            $status = 'Message envoyé via Green API ! Réponse : ' . json_encode($body);
            
            // Log pour débogage
            Log::info('Réponse Green API: ' . json_encode($body));
        } catch (\Exception $e) {
            Log::error('Erreur envoi WhatsApp Green API: ' . $e->getMessage());
            $status = 'Erreur lors de l\'envoi via Green API : ' . $e->getMessage();
        }
        
        // Informations de débogage
        $debugInfo = [
            'api_url' => config('services.greenapi.api_url'),
            'media_url' => config('services.greenapi.media_url'),
            'id_instance' => config('services.greenapi.id_instance'),
            'api_token' => '********' . substr(config('services.greenapi.api_token', ''), -4), // Masquer pour la sécurité
            'to_number' => $phoneNumber,
            'formatted_chat_id' => $phoneNumber . '@c.us',
        ];
        
        return view('green-api-whatsapp-test', [
            'status' => $status,
            'debug' => $debugInfo
        ]);
    }
}
