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
        $idInstance = config('services.greenapi.id_instance');
        $apiTokenInstance = config('services.greenapi.api_token');
        $apiUrl = config('services.greenapi.api_url');
        
        // Enregistrer les paramètres initiaux
        Log::debug('Green API WhatsApp - Paramètres d\'entrée', [
            'recipient_original' => $recipientPhone,
            'qrcode_path' => $qrCodePath,
            'message' => $message ?? 'Non spécifié',
            'api_config' => [
                'id_instance' => $idInstance,
                'api_url' => $apiUrl,
                'api_token_exists' => !empty($apiTokenInstance)
            ]
        ]);
        
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
        
        // Pour le chatId, enlever le + du numéro (Green API n'accepte pas le + dans le chatId)
        $chatIdNumber = ltrim($recipientPhone, '+');
        $formattedChatId = $chatIdNumber . '@c.us';
        
        Log::info('Début de l\'envoi WhatsApp via Green API', [
            'recipient' => $recipientPhone,
            'chatId' => $formattedChatId,
            'qrcode_path' => $qrCodePath,
            'id_instance' => $idInstance,
            'numéro_original' => $recipientPhone,
            'numéro_formaté_api' => $chatIdNumber,
            'format_complet_chatId' => $formattedChatId
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
        
        // Ne garder que les 8 derniers chiffres
        if (strlen($phone) > 8) {
            $phone = substr($phone, -8);
        }
        
        // Ajouter le préfixe +225 (Côte d'Ivoire)
        return '+225' . $phone;
    }
}
