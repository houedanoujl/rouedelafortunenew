# Guide d'intégration WhatsApp via Green API

Ce guide explique comment utiliser Green API pour envoyer des messages WhatsApp depuis l'application "Roue de la Fortune" sans les limitations des API officielles.

## Configuration des variables d'environnement

Ajoutez les variables suivantes à votre fichier `.env` :

```
# Configuration Green API WhatsApp
GREENAPI_API_URL=https://7105.api.greenapi.com
GREENAPI_MEDIA_URL=https://7105.media.greenapi.com
GREENAPI_ID_INSTANCE=7105222328
GREENAPI_API_TOKEN=094a4edc1a0146279d051bb1fce10af462886c767ea54dd9a4
```

Ces valeurs sont déjà configurées par défaut dans le code, mais il est recommandé dus ajouter au fichier `.env` pour une meilleure gestion.

## Avantages de Green API par rapport aux autres solutions

### 1. Par rapport à l'API WhatsApp officielle (Meta)
- Pas de besoin d'approbation commerciale
- Pas de restriction d'envoi aux utilisateurs ayant initié une conversation
- Pas de limite de 24h pour l'envoi de messages
- Pas besoin de webhook HTTPS en développement

### 2. Par rapport à Infobip
- Solution plus économique
- Pas besoin d'enregistrer les numéros de test
- Plus simple à intégrer
- Meilleure compatibilité avec les numéros internationaux

## Fonctionnement technique

Green API fonctionne en connectant une session WhatsApp Web à votre application. Pour l'utiliser :

1. Créez un compte sur [Green API](https://green-api.com/)
2. Scannez le QR code avec l'appareil mobile où WhatsApp est installé
3. Utilisez l'API pour envoyer des messages depuis cette session

## Intégration avec la Roue de la Fortune

Pour envoyer automatiquement un message aux gagnants de la roue, vous pouvez modifier le contrôleur `SpinResultController` en ajoutant l'envoi d'un message WhatsApp après qu'un utilisateur a gagné.

### Exemple d'implémentation dans le code existant

```php
// Dans SpinResultController ou ParticipantController
protected function sendWhatsAppNotification($participant, $prize)
{
    try {
        $client = new \GuzzleHttp\Client();
        $idInstance = env('GREENAPI_ID_INSTANCE', '7105222328');
        $apiTokenInstance = env('GREENAPI_API_TOKEN', '094a4edc1a0146279d051bb1fce10af462886c767ea54dd9a4');
        $apiUrl = env('GREENAPI_API_URL', 'https://7105.api.greenapi.com');
        
        // Formater le numéro de téléphone
        $phoneNumber = preg_replace('/\s+/', '', $participant->phone);
        $phoneNumber = ltrim($phoneNumber, '+');
        
        // Message personnalisé
        $message = "Félicitations {$participant->first_name} ! Vous avez gagné {$prize->name}. Présentez votre code QR pour réclamer votre prix.";
        
        $client->post("{$apiUrl}/waInstance{$idInstance}/sendMessage/{$apiTokenInstance}", [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'chatId' => $phoneNumber . '@c.us',
                'message' => $message
            ]
        ]);
        
        return true;
    } catch (\Exception $e) {
        \Log::error('Erreur envoi WhatsApp: ' . $e->getMessage());
        return false;
    }
}
```

## Compatibilité avec le mode test

Cette implémentation est compatible avec le système de mode test existant. Pour les comptes de test (@sifca.ci, @bigfiveabidjan.com, @bigfivesoutions.com), les messages WhatsApp seront également envoyés si un numéro de téléphone est associé au compte.
