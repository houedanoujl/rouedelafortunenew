# Guide d'intégration de l'API WhatsApp d'Infobip

Ce guide explique comment utiliser la page de test WhatsApp Infobip pour envoyer des messages depuis votre application.

## Étape 1 : Configuration des variables d'environnement

Assurez-vous que les variables suivantes sont définies dans votre fichier `.env` :

```
# Variables Infobip WhatsApp
INFOBIP_API_KEY=votre_clé_api_infobip
INFOBIP_BASE_URL=https://api.infobip.com
INFOBIP_WHATSAPP_NUMBER=votre_numéro_whatsapp_infobip
```

Vous avez déjà défini `INFOBIP_API_KEY` et `INFOBIP_FROM_NUMBER` dans votre `.env`, mais il manque `INFOBIP_BASE_URL` et `INFOBIP_WHATSAPP_NUMBER` (qui peut être le même que `INFOBIP_FROM_NUMBER`).

## Étape 2 : Accès à la page de test

Pour accéder à la page de test, visitez l'URL suivante :

```
http://localhost:8888/whatsapp-test
```

## Étape 3 : Utilisation de la page de test

1. Entrez un numéro de téléphone au format international, sans espaces ni caractères spéciaux. 
   Exemple : `2250700000000` (pour un numéro ivoirien)

2. Saisissez le message à envoyer

3. Cliquez sur "Envoyer"

## Étape 4 : Interprétation des résultats

- En cas de succès, vous verrez la réponse de l'API Infobip avec les détails du message envoyé.
- En cas d'échec, l'erreur sera affichée.

## Dépannage courant

1. **Erreur d'authentification** : Vérifiez que votre `INFOBIP_API_KEY` est valide.
2. **Numéro non autorisé** : En environnement sandbox, seuls les numéros enregistrés comme testeurs peuvent recevoir des messages.
3. **Erreur de format de numéro** : Assurez-vous que le numéro est au format international correct.

## Intégration dans votre code

Pour utiliser cette fonctionnalité dans votre application, vous pouvez adapter le code du contrôleur `WhatsappTestController` :

```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->post(env('INFOBIP_BASE_URL') . '/whatsapp/1/message/text', [
    'headers' => [
        'Authorization' => 'App ' . env('INFOBIP_API_KEY'),
        'Content-Type'  => 'application/json',
    ],
    'json' => [
        'from' => env('INFOBIP_WHATSAPP_NUMBER'),
        'to' => $phoneNumber,
        'content' => [
            'text' => $message,
        ],
    ],
]);
```

Ce code peut être intégré partout où vous avez besoin d'envoyer des messages WhatsApp.
