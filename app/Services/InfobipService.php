<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class InfobipService
{
    protected $client;
    protected $apiKey;
    protected $fromNumber;
    
    public function __construct()
    {
        $this->apiKey = env('INFOBIP_API_KEY');
        $this->fromNumber = env('INFOBIP_FROM_NUMBER');
        
        if (!$this->apiKey || !$this->fromNumber) {
            throw new \Exception('Les variables d\'environnement INFOBIP_API_KEY et INFOBIP_FROM_NUMBER doivent être définies dans le fichier .env');
        }
        $this->client = new Client([
            'base_uri' => 'https://api.infobip.com/',
            'headers' => [
                'Authorization' => 'App ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }
    
    public function sendWhatsAppNotification($phoneNumber, $name, $qrCode)
    {
        // Nettoyage initial du numéro (supprimer espaces, tirets, etc.)
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Formatage correct du numéro avec préfixe +225 pour la Côte d'Ivoire
        if (!str_starts_with($phoneNumber, '+225')) {
            // Si le numéro commence déjà par 225, ajouter juste le +
            if (str_starts_with($phoneNumber, '225')) {
                $phoneNumber = '+' . $phoneNumber;
            } 
            // Si c'est un numéro local à 10 chiffres sans indicatif
            else if (strlen($phoneNumber) == 10 && !str_starts_with($phoneNumber, '+')) {
                $phoneNumber = '+225' . $phoneNumber;
            }
            // Si c'est un numéro local à 8 chiffres (format court)
            else if (strlen($phoneNumber) == 8 && !str_starts_with($phoneNumber, '+')) {
                $phoneNumber = '+225' . $phoneNumber;
            }
            // Pour tout autre cas, préserver le numéro et ajouter +225 seulement s'il n'a pas déjà un autre indicatif
            else if (!str_starts_with($phoneNumber, '+')) {
                $phoneNumber = '+225' . $phoneNumber;
            }
        }
        
        // Ajouter le message de non-réponse au code QR
        $qrCodeWithMessage = $qrCode . '. Prière de ne pas répondre à ce message.';
        
        try {
            $response = $this->client->post('whatsapp/1/message/template', [
                'json' => [
                    'messages' => [
                        [
                            'from' => $this->fromNumber,
                            'to' => $phoneNumber,
                            'messageId' => uniqid(),
                            'content' => [
                                'templateName' => 'dinor_70ans_gagnant',
                                'templateData' => [
                                    'body' => [
                                        'placeholders' => [$name, $qrCodeWithMessage]
                                    ]
                                ],
                                'language' => 'fr'
                            ]
                        ]
                    ]
                ]
            ]);
            
            $responseBody = json_decode($response->getBody()->getContents(), true);
            Log::info('WhatsApp notification sent successfully', [
                'to' => $phoneNumber,
                'name' => $name,
                'qr_code' => $qrCode,
                'response' => $responseBody
            ]);
            return $responseBody;
            
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
            return null;
        }
    }
}
