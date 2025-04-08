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
        // Assurez-vous que le numéro commence par +225
        if (!str_starts_with($phoneNumber, '+225')) {
            $phoneNumber = '+225' . ltrim($phoneNumber, '+0');
        }
        
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
                                        'placeholders' => [$name, $qrCode]
                                    ]
                                ],
                                'language' => 'fr'
                            ]
                        ]
                    ]
                ]
            ]);
            
            $responseBody = json_decode($response->getBody()->getContents(), true);
            Log::info('WhatsApp notification sent successfully', $responseBody);
            return $responseBody;
            
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
            return null;
        }
    }
}
