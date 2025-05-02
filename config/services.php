<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    'greenapi' => [
        'api_url' => env('GREENAPI_API_URL'),
        'media_url' => env('GREENAPI_MEDIA_URL'),
        'id_instance' => env('GREENAPI_ID_INSTANCE'),
        'api_token' => env('GREENAPI_API_TOKEN'),
        'contact_number' => env('GREENAPI_CONTACT_NUMBER', '0719048728'),
        'default_message' => env('GREENAPI_DEFAULT_MESSAGE', "Félicitations ! Vous avez gagné ce prix : *{prize}*. Voici votre QR code pour récupérer votre gain.\n\n*Prière de ne pas répondre à ce message.*\n\nPour toute information, veuillez appeler le *+225 07 19 04 87 28*."),
    ],

    'google' => [
        'analytics' => [
            'tracking_id' => env('GOOGLE_ANALYTICS_ID'),
            'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID', env('GOOGLE_ANALYTICS_ID')),
            'view_id' => env('GOOGLE_ANALYTICS_VIEW_ID'),
            'service_account_credentials_json' => env('GOOGLE_ANALYTICS_CREDENTIALS_JSON', storage_path('app/google-analytics/service-account-credentials.json')),
            'version' => 'ga4',
        ],
    ],

];
