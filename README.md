# Roue de la Fortune DINOR

Application de jeu concours "Roue de la Fortune" pour les produits DINOR. Cette application est développée avec Nuxt 3 et utilise MySQL comme base de données.

## Fonctionnalités

- Formulaire d'inscription pour les participants (nom, prénom, téléphone)
- Roue de la fortune interactive avec 12 secteurs (gagné/perdu)
- Affichage du résultat après la rotation de la roue
- Génération de QR code pour les gagnants
- Notification par SMS pour les gagnants
- API sécurisées côté serveur pour toutes les opérations de base de données
- Interface d'administration pour la gestion des prix

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
DATABASE_URL=mysql://user:password@localhost:3306/rouedelafortune
USE_MYSQL=true
MOCK_MODE=false
MYSQL_HOST=localhost
MYSQL_USER=user
MYSQL_PASSWORD=password
MYSQL_DATABASE=rouedelafortune
```

4. Initialiser la base de données
```bash
# Exécuter les scripts SQL d'initialisation
mysql -u user -ppassword -h localhost rouedelafortune < ./db/init-mysql.sql
mysql -u user -ppassword -h localhost rouedelafortune < ./db/init-mysql-admin.sql
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

### Avec phpMyAdmin

L'application est configurée avec phpMyAdmin pour faciliter la gestion de la base de données MySQL :

- **URL d'accès** : http://localhost:8080
- **Serveur** : mysql
- **Utilisateur** : root
- **Mot de passe** : root

### Informations de connexion MySQL

- **Hôte** : mysql (dans Docker) ou localhost (sans Docker)
- **Port** : 3306
- **Base de données** : rouedelafortune
- **Utilisateur** : user
- **Mot de passe** : password

### Accès à l'interface d'administration

- **URL d'accès** : http://localhost:8888/admin
- **Utilisateur** : houedanou
- **Mot de passe** : nouveaumdp123

## Architecture

L'application utilise une architecture client-serveur sécurisée :

- **Frontend** : Vue.js/Nuxt.js pour l'interface utilisateur
- **Backend** : API Nuxt pour toutes les opérations de base de données
- **Base de données** : MySQL pour le stockage persistant

Toutes les opérations de base de données sont effectuées via des API côté serveur, jamais directement depuis le navigateur.

## Structure du projet

Le projet est organisé selon l'architecture Nuxt 3 :

- `/components` : Composants Vue réutilisables
- `/composables` : Composables pour la logique partagée et l'accès à la base de données
- `/pages` : Pages de l'application
- `/server/api` : Endpoints API pour les opérations de base de données
- `/db` : Scripts SQL pour l'initialisation de la base de données

## Structure Docker

Le projet est configuré avec Docker pour faciliter le développement et le déploiement :

- **mysql** : Base de données MySQL (port 3306)
- **phpmyadmin** : Interface d'administration MySQL (port 8080)
- **app** : Application Nuxt.js (port 8888)
- **db-migrations** : Service de migration de base de données (s'exécute automatiquement au démarrage)

## Migrations de base de données

Le système de migrations de base de données vérifie automatiquement si les tables existent déjà avant d'exécuter les scripts SQL. Cela garantit que la base de données est toujours dans un état cohérent, même après des redémarrages ou des mises à jour

## Licence

Tous droits réservés DINOR
