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
- Docker et Docker Compose (recommandé)
- Base de données Supabase

## Installation

### Méthode avec Docker (recommandée)

1. Cloner le dépôt
```bash
git clone <repository-url>
cd rouedelafortune
```

2. Lancer l'application avec Docker Compose
```bash
docker-compose up -d
```

3. Accéder à l'application
```
http://localhost:8888
```

### Méthode traditionnelle (sans Docker)

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

## Résolution des problèmes

### Erreur "Failed to fetch" lors de l'inscription

Si vous rencontrez une erreur "Failed to fetch" lors de l'inscription, cela peut être lié à un problème de connexion entre le frontend et le backend Supabase. Pour résoudre ce problème :

1. Vérifiez que tous les services Docker sont en cours d'exécution :
```bash
docker-compose ps
```

2. Si le problème persiste, modifiez le fichier `docker-compose.yml` et remplacez :
```yaml
SUPABASE_URL: http://postgres:5432
```
par :
```yaml
SUPABASE_URL: http://localhost:5432
```

3. Redémarrez les conteneurs Docker :
```bash
docker-compose down
docker-compose up -d
```

## Structure Docker

Le projet est configuré avec Docker pour faciliter le développement et le déploiement :

- **postgres** : Base de données PostgreSQL pour Supabase (port 5432)
- **db-init** : Service d'initialisation de la base de données
- **app** : Application Nuxt.js (exposée sur le port 8888)

## Base de données

Le projet utilise Supabase comme backend avec les tables suivantes :
- contest : gestion des périodes de concours
- participant : informations sur les participants
- entry : entrées de jeu (résultats gagné/perdu)
- prize : lots disponibles
- prize_distribution : répartition des lots par semaine

## Licence

Tous droits réservés DINOR
