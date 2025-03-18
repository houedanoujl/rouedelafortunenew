# Roue de la Fortune DINOR

Application de jeu concours "Roue de la Fortune" pour les produits DINOR. Cette application est développée avec Nuxt 3 et utilise Supabase comme backend.

## Fonctionnalités

- Formulaire d'inscription pour les participants (nom, prénom, téléphone)
- Roue de la fortune interactive avec 12 secteurs (gagné/perdu)
- Affichage du résultat après la rotation de la roue
- Génération de QR code pour les gagnants
- Notification par SMS pour les gagnants

## Configuration requise

- Node.js (v16+)
- Base de données Supabase

## Installation

1. Cloner le dépôt
```bash
git clone <repository-url>
cd rouedelafortune
```

2. Installer les dépendances
```bash
npm install
```

3. Configurer les variables d'environnement
Créez un fichier `.env` à la racine du projet avec les informations suivantes :
```
SUPABASE_URL=votre_url_supabase
SUPABASE_KEY=votre_clé_api_supabase
```

4. Lancer le serveur de développement
```bash
npm run dev
```

5. Construire pour la production
```bash
npm run build
```

## Base de données

Le projet utilise Supabase comme backend avec les tables suivantes :
- contest : gestion des périodes de concours
- participant : informations sur les participants
- entry : entrées de jeu (résultats gagné/perdu)
- prize : lots disponibles
- prize_distribution : répartition des lots par semaine

## Licence

Tous droits réservés DINOR
