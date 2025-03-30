<?php

return [
    'resources' => [
        'contest' => [
            'label' => 'Concours',
            'plural_label' => 'Concours',
            'navigation_label' => 'Concours',
            'fields' => [
                'name' => 'Nom',
                'start_date' => 'Date de début',
                'end_date' => 'Date de fin',
                'status' => 'Statut',
                'description' => 'Description',
            ],
            'status_options' => [
                'active' => 'Actif',
                'inactive' => 'Inactif',
                'completed' => 'Terminé',
            ],
        ],
        'prize' => [
            'label' => 'Prix',
            'plural_label' => 'Prix',
            'navigation_label' => 'Prix',
            'fields' => [
                'name' => 'Nom',
                'description' => 'Description',
                'type' => 'Type',
                'value' => 'Valeur',
                'image_url' => 'URL de l\'image',
                'stock' => 'Stock',
            ],
        ],
        'participant' => [
            'label' => 'Participant',
            'plural_label' => 'Participants',
            'navigation_label' => 'Participants',
            'fields' => [
                'first_name' => 'Prénom',
                'last_name' => 'Nom',
                'phone' => 'Téléphone',
                'email' => 'Email',
            ],
        ],
        'entry' => [
            'label' => 'Participation',
            'plural_label' => 'Participations',
            'navigation_label' => 'Participations',
            'fields' => [
                'participant_id' => 'Participant',
                'contest_id' => 'Concours',
                'prize_id' => 'Prix',
                'result' => 'Résultat',
                'played_at' => 'Joué le',
                'qr_code' => 'Code QR',
                'claimed' => 'Réclamé',
                'won_date' => 'Date de gain',
            ],
        ],
        'qr_code' => [
            'label' => 'Code QR',
            'plural_label' => 'Codes QR',
            'navigation_label' => 'Codes QR',
            'fields' => [
                'entry_id' => 'Participation',
                'code' => 'Code',
                'scanned' => 'Scanné',
                'scanned_at' => 'Scanné le',
                'scanned_by' => 'Scanné par',
            ],
        ],
    ],
];
