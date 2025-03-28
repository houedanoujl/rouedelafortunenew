# Roue de la Fortune - Application Laravel avec Docker

Cette application Laravel "Roue de la Fortune" est configurée pour fonctionner avec Docker, ce qui facilite le déploiement et le développement.

## Prérequis

- Docker et Docker Compose installés sur votre machine

## Installation

1. Clonez ce dépôt :
   ```bash
   git clone https://github.com/jhouedanou/rouedelafortune.git
   cd rouedelafortune
   ```

2. Lancez l'application avec Docker Compose :
   ```bash
   docker compose up -d
   ```

3. L'application sera accessible aux adresses suivantes :
   - Application principale : http://localhost:8888
   - Interface d'administration : http://localhost:8888/admin
   - Interface phpMyAdmin : http://localhost:8081

## Identifiants par défaut

### Interface d'administration
- Nom d'utilisateur : houedanou
- Mot de passe : nouveaumdp123

### Base de données (via phpMyAdmin)
- Nom d'utilisateur : user
- Mot de passe : password
- ou
- Nom d'utilisateur : root
- Mot de passe : root

## Structure de l'application

L'application "Roue de la Fortune" est basée sur Laravel et utilise Filament pour l'interface d'administration. Elle comprend les modèles suivants :

- **Contest** : Gestion des concours
- **Prize** : Gestion des prix
- **Participant** : Gestion des participants
- **Entry** : Gestion des participations
- **QrCode** : Gestion des codes QR
- **PrizeDistribution** : Gestion de la distribution des prix

## Développement

### Création des ressources Filament

Si vous souhaitez créer ou recréer les ressources Filament pour les modèles existants, vous pouvez utiliser les commandes suivantes :

```bash
# Installation de Filament (si ce n'est pas déjà fait)
docker compose exec app php artisan filament:install --panels

# Création des ressources pour chaque modèle
docker compose exec app php artisan make:filament-resource Contest
docker compose exec app php artisan make:filament-resource Prize
docker compose exec app php artisan make:filament-resource Participant
docker compose exec app php artisan make:filament-resource Entry
docker compose exec app php artisan make:filament-resource QrCode
docker compose exec app php artisan make:filament-resource PrizeDistribution
```

### Création de nouveaux modèles

Si vous souhaitez créer de nouveaux modèles avec leurs migrations :

```bash
docker compose exec app php artisan make:model NomDuModele -m
```

Puis créez la ressource Filament correspondante :

```bash
docker compose exec app php artisan make:filament-resource NomDuModele
```

### Exécution des migrations

Pour exécuter les migrations de la base de données :

```bash
docker compose exec app php artisan migrate
```

### Exécution des seeders

Pour remplir la base de données avec des données de test :

```bash
docker compose exec app php artisan db:seed
```

## Configuration Docker

L'application utilise plusieurs services Docker :

- **app** : Application Laravel avec PHP 8.3
- **mysql** : Base de données MySQL 8.0
- **nginx** : Serveur web Nginx
- **phpmyadmin** : Interface d'administration pour MySQL

## Personnalisation

Pour personnaliser la page d'accueil de l'application, modifiez le fichier `routes/web.php` et créez un contrôleur et une vue correspondants.

## Licence

Ce projet est sous licence MIT.
