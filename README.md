# Roue de la Fortune DINOR

Application de jeu concours "Roue de la Fortune" pour les produits DINOR. Cette application est développée avec Nuxt 3 et utilise PostgreSQL comme base de données.

## Fonctionnalités

- Formulaire d'inscription pour les participants (nom, prénom, téléphone)
- Roue de la fortune interactive avec 12 secteurs (gagné/perdu)
- Affichage du résultat après la rotation de la roue
- Génération de QR code pour les gagnants
- Notification par SMS pour les gagnants
- API sécurisées côté serveur pour toutes les opérations de base de données
- Interface d'administration pour la gestion des prix
- Architecture client-serveur optimisée avec PostgreSQL

## Configuration requise

- Docker et Docker Compose (recommandé)
- Node.js (v16+) pour le développement local sans Docker

## Installation

### Méthode avec Docker (recommandée)

1. Cloner le dépôt
```bash
git clone <repository-url>
cd rouedelafortune
```

2. Lancer l'application avec Docker Compose
```bash
docker compose up -d
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
DATABASE_URL=postgres://postgres:postgres@localhost:5432/rouedelafortune
USE_POSTGRES=true
MOCK_MODE=false
POSTGRES_HOST=localhost
POSTGRES_USER=postgres
POSTGRES_PASSWORD=postgres
POSTGRES_DATABASE=rouedelafortune
```

4. Initialiser la base de données
```bash
# Exécuter les scripts SQL d'initialisation
psql -U postgres -h localhost -d rouedelafortune -f ./db/init-postgres.sql
psql -U postgres -h localhost -d rouedelafortune -f ./db/init-postgres-admin.sql
```

5. Lancer le serveur de développement
```bash
npm run dev
```

6. Construire pour la production
```bash
npm run build
```

## Gestion de la base de données

### Avec pgAdmin

L'application est configurée avec pgAdmin pour faciliter la gestion de la base de données PostgreSQL :

- **URL d'accès** : http://localhost:8080
- **Email** : admin@example.com
- **Mot de passe** : admin

Pour configurer la connexion à la base de données dans pgAdmin :
1. Cliquez sur "Add New Server"
2. Nom : rouedelafortune
3. Onglet Connection :
   - Host : postgres
   - Port : 5432
   - Database : rouedelafortune
   - Username : postgres
   - Password : postgres

### Informations de connexion PostgreSQL

- **Hôte** : postgres (dans Docker) ou localhost (sans Docker)
- **Port** : 5432
- **Base de données** : rouedelafortune
- **Utilisateur** : postgres
- **Mot de passe** : postgres

### Accès à l'interface d'administration

- **URL d'accès** : http://localhost:8888/admin
- **Utilisateur** : houedanou
- **Mot de passe** : admin123

## Architecture

L'application utilise une architecture client-serveur sécurisée :

- **Frontend** : Vue.js/Nuxt.js pour l'interface utilisateur
- **Backend** : API Nuxt pour toutes les opérations de base de données
- **Base de données** : PostgreSQL pour le stockage persistant

Toutes les opérations de base de données sont effectuées via des API côté serveur, jamais directement depuis le navigateur. Cette architecture garantit la sécurité et la performance de l'application.

## Structure du projet

Le projet est organisé selon l'architecture Nuxt 3 :

- `/components` : Composants Vue réutilisables
- `/composables` : Composables pour la logique partagée et l'accès à la base de données
- `/pages` : Pages de l'application
- `/server/api` : Endpoints API pour les opérations de base de données
- `/server/utils` : Utilitaires côté serveur, dont la configuration PostgreSQL
- `/db` : Scripts SQL pour l'initialisation de la base de données

## Structure Docker

Le projet est configuré avec Docker pour faciliter le développement et le déploiement :

- **postgres** : Base de données PostgreSQL (port 5432)
- **pgadmin** : Interface d'administration PostgreSQL (port 8080)
- **app** : Application Nuxt.js (port 8888)
- **db-migrations** : Service de migration de base de données (s'exécute automatiquement au démarrage)

## Migrations de base de données

Le système de migrations de base de données vérifie automatiquement si les tables existent déjà avant d'exécuter les scripts SQL. Cela garantit que la base de données est toujours dans un état cohérent, même après des redémarrages ou des mises à jour.

## Licence

Tous droits réservés DINOR
