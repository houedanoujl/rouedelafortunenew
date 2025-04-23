<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappTestController extends Controller
{
    public function showForm()
    {
        return view('whatsapp-test');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        $status = null;
        try {
            // Utiliser une URL par défaut si INFOBIP_BASE_URL n'est pas définie
            $baseUrl = env('INFOBIP_BASE_URL', 'https://api.infobip.com');
            
            // Utiliser INFOBIP_FROM_NUMBER si INFOBIP_WHATSAPP_NUMBER n'est pas définie
            $fromNumber = env('INFOBIP_WHATSAPP_NUMBER', env('INFOBIP_FROM_NUMBER'));
            
            $client = new \GuzzleHttp\Client();
            $response = $client->post($baseUrl . '/whatsapp/1/message/text', [
                'headers' => [
                    'Authorization' => 'App ' . env('INFOBIP_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'from' => $fromNumber,
                    'to' => $request->input('to'),
                    'content' => [
                        'text' => $request->input('message'),
                    ],
                ],
            ]);
            $body = json_decode($response->getBody(), true);
            $status = 'Message envoyé ! Réponse : ' . json_encode($body);
        } catch (\Exception $e) {
            Log::error('Erreur envoi WhatsApp test: ' . $e->getMessage());
            $status = 'Erreur lors de l\'envoi : ' . $e->getMessage();
        }
        
        // Ajouter des informations de débogage
        $debugInfo = [
            'api_key' => env('INFOBIP_API_KEY') ? 'Définie' : 'Non définie',
            'base_url' => env('INFOBIP_BASE_URL', 'https://api.infobip.com'),
            'from_number' => env('INFOBIP_WHATSAPP_NUMBER', env('INFOBIP_FROM_NUMBER')),
            'to_number' => $request->input('to')
        ];
        
        return view('whatsapp-test', [
            'status' => $status,
            'debug' => $debugInfo
        ]);
    }
}
