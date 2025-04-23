<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class GreenWhatsAppService
{
    /**
     * Envoie un QR code à un gagnant via Green API WhatsApp
     *
     * @param string $recipientPhone Le numéro du destinataire
     * @param string $qrCodePath Le chemin vers le fichier QR code
     * @param string|null $message Message à envoyer avec le QR code
     * @return mixed True si succès, message d'erreur sinon
     */
    public function sendQrCodeToWinner($recipientPhone, $qrCodePath, $message = null)
    {
        // Récupérer les informations d'API depuis la configuration
        $idInstance = env('GREENAPI_ID_INSTANCE', '7105222328');
        $apiTokenInstance = env('GREENAPI_API_TOKEN', '094a4edc1a0146279d051bb1fce10af462886c767ea54dd9a4');
        $apiUrl = env('GREENAPI_API_URL', 'https://7105.api.greenapi.com');
        
        if (!$idInstance || !$apiTokenInstance || !$apiUrl) {
            Log::error('Configuration Green API manquante', [
                'id_instance_exists' => !empty($idInstance),
                'api_token_exists' => !empty($apiTokenInstance),
                'api_url_exists' => !empty($apiUrl)
            ]);
            return "Erreur: Configuration Green API incomplète";
        }
        
        // Formater le numéro de téléphone (supprimer + et espaces)
        $recipientPhone = $this->formatPhoneNumber($recipientPhone);
        $formattedChatId = $recipientPhone . '@c.us';
        
        Log::info('Début de l\'envoi WhatsApp via Green API', [
            'recipient' => $recipientPhone,
            'qrcode_path' => $qrCodePath,
            'id_instance' => $idInstance
        ]);
        
        if (!file_exists($qrCodePath)) {
            Log::error('QR code introuvable', ['path' => $qrCodePath]);
            return "Erreur: Fichier QR code non trouvé";
        }
        
        try {
            // 1. Envoyer un message texte avant l'image
            if ($message === null) {
                $prize = session('prize_name') ?? 'un prix';
                $message = "Félicitations ! Vous avez gagné {$prize}. Voici votre QR code pour récupérer votre gain.";
            }
            
            // Appel de l'API pour envoyer le message texte
            $sendTextUrl = "{$apiUrl}/waInstance{$idInstance}/sendMessage/{$apiTokenInstance}";
            $client = new Client();
            $textResponse = $client->post($sendTextUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'chatId' => $formattedChatId,
                    'message' => $message
                ],
            ]);
            
            $textResult = json_decode($textResponse->getBody(), true);
            Log::debug('Réponse envoi texte', ['response' => json_encode($textResult)]);
            
            // 2. Envoyer l'image du QR code
            $sendFileUrl = "{$apiUrl}/waInstance{$idInstance}/sendFileByUpload/{$apiTokenInstance}";
            
            // Lire le fichier image
            $fileContents = file_get_contents($qrCodePath);
            $filename = basename($qrCodePath);
            
            // Multipart request pour l'envoi du fichier
            $multipart = [
                [
                    'name' => 'chatId',
                    'contents' => $formattedChatId
                ],
                [
                    'name' => 'caption',
                    'contents' => 'Votre QR code personnel'
                ],
                [
                    'name' => 'file',
                    'contents' => $fileContents,
                    'filename' => $filename,
                    'headers' => [
                        'Content-Type' => 'image/png',
                    ]
                ]
            ];
            
            $fileResponse = $client->post($sendFileUrl, [
                'multipart' => $multipart
            ]);
            
            $fileResult = json_decode($fileResponse->getBody(), true);
            Log::debug('Réponse envoi fichier', ['response' => json_encode($fileResult)]);
            
            // Vérifier si l'envoi a réussi
            if (isset($fileResult['idMessage'])) {
                Log::info('Message WhatsApp et QR code envoyés avec succès via Green API', [
                    'recipient' => $recipientPhone, 
                    'message_id' => $fileResult['idMessage']
                ]);
                return true;
            } else {
                Log::error('Échec de l\'envoi du QR code via Green API', [
                    'response' => json_encode($fileResult)
                ]);
                return "Erreur: échec de l'envoi du QR code";
            }
            
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi WhatsApp via Green API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return "Exception: " . $e->getMessage();
        }
    }
    
    /**
     * Formate un numéro de téléphone pour Green API
     * 
     * @param string $phoneNumber Numéro à formater
     * @return string Numéro formaté
     */
    private function formatPhoneNumber($phoneNumber)
    {
        // Supprimer tout sauf les chiffres
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Si le numéro commence par 225, on le garde tel quel
        if (substr($phone, 0, 3) === '225') {
            return $phone;
        }
        
        // Sinon, on ajoute 225 (pour la Côte d'Ivoire)
        return '225' . $phone;
    }
}
