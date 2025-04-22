<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendQrCodeToWinner($recipientPhone, $qrCodePath) {
        $accessToken = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        
        if (!$accessToken || !$phoneNumberId) {
            Log::error('Configuration WhatsApp manquante', [
                'access_token_exists' => !empty($accessToken),
                'phone_number_id_exists' => !empty($phoneNumberId)
            ]);
            return "Erreur: Configuration WhatsApp incomplète";
        }
        
        $formattedPhone = $this->formatPhoneDisplay($recipientPhone);
        Log::info('Début de l\'envoi WhatsApp', [
            'recipient' => $formattedPhone,
            'qrcode_path' => $qrCodePath,
            'phone_number_id' => $phoneNumberId
        ]);
        
        if (!file_exists($qrCodePath)) {
            Log::error('QR code introuvable', ['path' => $qrCodePath]);
            return "Erreur: Fichier QR code non trouvé";
        }
        
        try {
            // Formater le numéro de téléphone
            $recipientPhone = $this->formatPhoneNumber($recipientPhone);
            $formattedPhone = $this->formatPhoneDisplay($recipientPhone);
            
            // 1. Upload de l'image du QR code
            Log::debug('Téléchargement du média WhatsApp', ['path' => $qrCodePath]);
            
            $uploadResponse = $this->uploadMedia($accessToken, $phoneNumberId, $qrCodePath);
            Log::debug('Réponse de l\'upload média', ['response' => json_encode($uploadResponse)]);
            
            if (!isset($uploadResponse['id'])) {
                Log::error('Échec du téléchargement du média WhatsApp', [
                    'response' => json_encode($uploadResponse),
                    'error' => $uploadResponse['error'] ?? 'Erreur inconnue'
                ]);
                return "Erreur: échec du téléchargement du média (" . ($uploadResponse['error']['message'] ?? 'Erreur inconnue') . ")";
            }
            
            $mediaId = $uploadResponse['id'];
            
            // 2. Envoi du message WhatsApp avec l'image
            Log::debug('Envoi du message WhatsApp', [
                'recipient' => $formattedPhone,
                'media_id' => $mediaId
            ]);
            
            $sendResponse = $this->sendWhatsAppMedia($accessToken, $phoneNumberId, $recipientPhone, $mediaId);
            Log::debug('Réponse de l\'envoi du message', ['response' => json_encode($sendResponse)]);
            
            if (isset($sendResponse['error'])) {
                Log::error('Échec de l\'envoi du message WhatsApp', [
                    'response' => json_encode($sendResponse),
                    'error' => $sendResponse['error'] ?? 'Erreur inconnue'
                ]);
                return "Erreur: échec de l'envoi du message (" . ($sendResponse['error']['message'] ?? 'Erreur inconnue') . ")";
            }
            
            Log::info('Message WhatsApp envoyé avec succès', [
                'recipient' => $formattedPhone, 
                'message_id' => $sendResponse['messages'][0]['id'] ?? 'N/A'
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi WhatsApp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return "Exception: " . $e->getMessage();
        }
    }
    
    private function formatPhoneNumber($phoneNumber) {
        // Nettoyer d'abord le numéro de téléphone (enlever espaces, tirets, etc.)
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Si le numéro ne commence pas par +, ajouter le préfixe +225 (Côte d'Ivoire)
        if (substr($phone, 0, 1) !== '+') {
            // Si le numéro commence par 225, ajouter seulement le +
            if (substr($phone, 0, 3) === '225') {
                $phone = '+' . $phone;
            } else {
                // Sinon ajouter le préfixe complet +225
                $phone = '+225' . $phone;
            }
        }
        
        return $phone;
    }
    
    private function uploadMedia($accessToken, $phoneNumberId, $filePath) {
        $response = Http::withToken($accessToken)
            ->attach('file', fopen($filePath, 'r'))
            ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/media", [
                'messaging_product' => 'whatsapp',
                'type' => 'image/png'
            ]);
        
        return $response->json();
    }
    
    private function sendWhatsAppMedia($accessToken, $phoneNumberId, $recipientPhone, $mediaId) {
        $message = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'image',
            'image' => [
                'id' => $mediaId,
                'caption' => "Félicitations ! Vous avez gagné à la Roue de la Fortune. Veuillez présenter ce QR code lors de la remise de votre prix."
            ]
        ];
        
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v19.0/{$phoneNumberId}/messages", $message);
        
        return $response->json();
    }
    
    /**
     * Formate un numéro de téléphone pour l'affichage (obfuscation partielle)
     */
    private function formatPhoneDisplay($phone)
    {
        // Supprime les espaces et caractères non-numériques
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Gère différents formats
        if (strlen($phone) >= 8) {
            // Masque le milieu du numéro
            $visible = 4; // Caractères visibles à la fin
            $start = max(0, strlen($phone) - (4 + $visible));
            return substr($phone, 0, $start) . '****' . substr($phone, -$visible);
        }
        
        return $phone; // Si le numéro est trop court, le retourne tel quel
    }
}
