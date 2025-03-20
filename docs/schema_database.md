# Documentation du Schéma de Base de Données - Roue de la Fortune

Ce document décrit en détail le schéma de la base de données PostgreSQL utilisé pour l'application Roue de la Fortune.

## Vue d'ensemble du Modèle de Données

L'application Roue de la Fortune repose sur un modèle de données relationnel structuré autour de cinq tables principales :

1. **participant** - Stocke les informations relatives aux participants
2. **prize** - Contient les détails des lots disponibles à gagner
3. **contest** - Définit les différentes sessions de concours
4. **entry** - Enregistre chaque participation et son résultat
5. **qr_codes** - Permet le suivi des codes QR associés aux lots gagnés

## Diagramme des Relations entre Tables

```
participant 1──┐
                └─n entry n──1 prize
                  │
                  │
                 1│
                  │
               contest
                  │
                  │
                 n│
                  │
              qr_codes
```

## Description Détaillée des Tables

### Table: participant

Stocke les données personnelles des participants au concours.

| Colonne    | Type                   | Description                                                  |
|------------|------------------------|--------------------------------------------------------------|
| id         | SERIAL PRIMARY KEY     | Identifiant unique auto-incrémenté pour chaque participant   |
| first_name | TEXT NOT NULL          | Prénom du participant                                        |
| last_name  | TEXT NOT NULL          | Nom de famille du participant                                |
| phone      | TEXT                   | Numéro de téléphone pour contacter le participant            |
| email      | TEXT                   | Adresse email optionnelle du participant                     |
| created_at | TIMESTAMP WITH TIMEZONE| Date et heure de création de l'enregistrement               |
| updated_at | TIMESTAMP WITH TIMEZONE| Date et heure de la dernière modification des informations  |

### Table: prize (lots)

Contient les informations sur les différents lots disponibles dans le concours.

| Colonne        | Type                   | Description                                              |
|----------------|------------------------|----------------------------------------------------------|
| id             | SERIAL PRIMARY KEY     | Identifiant unique auto-incrémenté pour chaque lot       |
| name           | TEXT NOT NULL          | Nom du lot affiché aux participants                      |
| description    | TEXT                   | Description détaillée du lot et de ses caractéristiques  |
| total_quantity | INTEGER DEFAULT 1      | Nombre total d'exemplaires disponibles de ce lot         |
| created_at     | TIMESTAMP WITH TIMEZONE| Date et heure de création de l'enregistrement           |
| updated_at     | TIMESTAMP WITH TIMEZONE| Date et heure de la dernière modification               |

### Table: contest (concours)

Définit les différentes sessions ou périodes du concours de la Roue de la Fortune.

| Colonne     | Type                   | Description                                                |
|-------------|------------------------|------------------------------------------------------------|
| id          | SERIAL PRIMARY KEY     | Identifiant unique auto-incrémenté pour chaque concours    |
| name        | TEXT NOT NULL          | Nom du concours ou de la session                           |
| description | TEXT                   | Description du concours, règles spécifiques                |
| start_date  | TIMESTAMP WITH TIMEZONE| Date et heure de début du concours                         |
| end_date    | TIMESTAMP WITH TIMEZONE| Date et heure de fin du concours                           |
| created_at  | TIMESTAMP WITH TIMEZONE| Date et heure de création de l'enregistrement             |
| updated_at  | TIMESTAMP WITH TIMEZONE| Date et heure de la dernière modification                 |
| status      | TEXT DEFAULT 'ACTIVE'  | Statut actuel du concours (ACTIVE, CLOSED, PENDING, etc.) |

### Table: entry (participation)

Enregistre chaque participation d'un participant à un concours.

| Colonne        | Type                   | Description                                                         |
|----------------|------------------------|---------------------------------------------------------------------|
| id             | SERIAL PRIMARY KEY     | Identifiant unique auto-incrémenté pour chaque participation        |
| participant_id | INTEGER REFERENCES     | Référence au participant (clé étrangère vers participant.id)        |
| contest_id     | INTEGER REFERENCES     | Référence au concours (clé étrangère vers contest.id)               |
| entry_date     | TIMESTAMP WITH TIMEZONE| Date et heure exactes de la participation                           |
| result         | TEXT DEFAULT 'EN ATTENTE'| Résultat de la participation (EN ATTENTE, GAGNÉ, PERDU, etc.)      |
| prize_id       | INTEGER REFERENCES     | Référence au lot gagné, si applicable (clé étrangère vers prize.id) |
| created_at     | TIMESTAMP WITH TIMEZONE| Date et heure de création de l'enregistrement                      |

### Table: qr_codes

Stocke les informations sur les codes QR générés pour les lots gagnés.

| Colonne        | Type                   | Description                                                         |
|----------------|------------------------|---------------------------------------------------------------------|
| id             | SERIAL PRIMARY KEY     | Identifiant unique auto-incrémenté pour chaque code QR              |
| tracking_id    | TEXT UNIQUE NOT NULL   | Identifiant unique de suivi associé au code QR                      |
| participant_id | INTEGER REFERENCES     | Référence au participant (clé étrangère vers participant.id)        |
| prize_id       | INTEGER REFERENCES     | Référence au lot associé (clé étrangère vers prize.id)              |
| created_at     | TIMESTAMP WITH TIMEZONE| Date et heure de création du code QR                                |
| scan_count     | INTEGER DEFAULT 0      | Nombre total de fois que ce code QR a été scanné                    |
| last_scanned   | TIMESTAMP WITH TIMEZONE| Date et heure du dernier scan de ce code QR                         |
| scan_history   | JSONB DEFAULT '[]'     | Historique complet des scans au format JSON                         |

## Index

Pour améliorer les performances des requêtes, les index suivants ont été créés :

| Nom                     | Table    | Colonne(s)       | Description                                               |
|-------------------------|----------|------------------|-----------------------------------------------------------|
| idx_entry_participant_id| entry    | participant_id   | Accélère les recherches par identifiant de participant    |
| idx_entry_contest_id    | entry    | contest_id       | Accélère les recherches par identifiant de concours       |
| idx_entry_prize_id      | entry    | prize_id         | Accélère les recherches par identifiant de lot            |
| idx_entry_result        | entry    | result           | Accélère les recherches par résultat                      |
| idx_qr_codes_tracking_id| qr_codes | tracking_id      | Accélère les recherches par identifiant de suivi          |

## Déclencheurs (Triggers)

### qr_code_scan_trigger
- **Table**: qr_codes
- **Événement**: BEFORE UPDATE OF scan_count
- **Fonction**: increment_scan_count()
- **Description**: Ce déclencheur s'active avant la mise à jour du compteur de scan d'un code QR. Il incrémente automatiquement le compteur, met à jour la date du dernier scan et enrichit l'historique des scans avec les détails du scan actuel (horodatage, adresse IP, agent utilisateur).

## Maintenance du Schéma

Les migrations sont numérotées séquentiellement pour assurer une application correcte des changements :

1. 00_create_roles.sql - Création des rôles de sécurité Supabase
2. 01_base_schema.sql - Création des tables principales
3. 02_add_remaining_won_date.sql - Ajout de champs supplémentaires
4. 02_utility_functions.sql - Fonctions utilitaires SQL
5. 03_qr_code_tracking.sql - Système de suivi des codes QR
6. 04_admin_views.sql - Vues pour l'administration
7. 05_utility_functions.sql - Fonctions utilitaires supplémentaires
8. 06_sample_data.sql - Données exemple pour le développement
9. 07_clean_up.sql - Nettoyage et optimisations
10. 08_table_descriptions.sql - Documentation détaillée des tables dans la base de données
