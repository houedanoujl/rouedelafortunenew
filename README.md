# Roue de la Fortune DINOR

Application de jeu concours "Roue de la Fortune" pour les produits DINOR. Cette application est développée avec Laravel, Filament pour l'administration et Livewire pour les composants interactifs. Elle utilise MySQL comme base de données.

## Fonctionnalités

- Formulaire d'inscription pour les participants (nom, prénom, téléphone)
- Roue de la fortune interactive avec plusieurs secteurs (gagné/perdu)
- Affichage du résultat après la rotation de la roue
- Génération de QR code pour les gagnants
- Interface d'administration complète avec Filament
- Composants interactifs avec Livewire
- Architecture MVC sécurisée avec Laravel
- Base de données MySQL

## Configuration requise

- Docker et Docker Compose (recommandé)
- PHP 8.2+ pour le développement local sans Docker
- Composer pour la gestion des dépendances PHP

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

4. Accéder à l'interface d'administration
```
http://localhost:8888/admin
```

Identifiants par défaut :
- Nom d'utilisateur : houedanou
- Mot de passe : nouveaumdp123

### Méthode traditionnelle (sans Docker)

1. Cloner le dépôt
```bash
git clone <repository-url>
cd rouedelafortune
```

2. Installer les dépendances
```bash
composer install
```

3. Copier le fichier d'environnement
```bash
cp .env.example .env
```

4. Générer la clé d'application
```bash
php artisan key:generate
```

5. Configurer la base de données dans le fichier .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rouedelafortune
DB_USERNAME=root
DB_PASSWORD=
```

6. Exécuter les migrations et les seeders
```bash
php artisan migrate --seed
```

7. Lancer le serveur de développement
```bash
php artisan serve
```

8. Accéder à l'application
```
http://localhost:8000
```

## Structure du projet

- `/app` : Modèles, contrôleurs, ressources Filament et composants Livewire
- `/database` : Migrations et seeders
- `/resources` : Vues, assets et traductions
- `/routes` : Définition des routes de l'application
- `/public` : Fichiers accessibles publiquement (CSS, JS, images)
- `/nginx` : Configuration du serveur web Nginx

## Développement

### Commandes utiles

- Créer un nouveau modèle avec migration et contrôleur :
```bash
php artisan make:model NomDuModele -mcr
```

- Créer un nouveau composant Livewire :
```bash
php artisan make:livewire NomDuComposant
```

- Créer une nouvelle ressource Filament :
```bash
php artisan make:filament-resource NomDuModele
```

## Authentification

L'authentification est gérée par Filament pour l'interface d'administration.

Identifiants par défaut :
- Nom d'utilisateur : houedanou
- Mot de passe : nouveaumdp123

## Licence

Ce projet est sous licence propriétaire. Tous droits réservés.
