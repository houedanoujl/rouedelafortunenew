#!/bin/bash

# Créer un nouveau projet Laravel
composer create-project laravel/laravel .

# Installer Filament pour le dashboard administratif
composer require filament/filament:"^3.0" -W

# Installer Livewire pour le frontend
composer require livewire/livewire:"^3.0" -W

# Installer d'autres packages utiles
composer require spatie/laravel-permission # Pour la gestion des rôles et permissions
composer require simplesoftwareio/simple-qrcode # Pour la génération de QR codes
composer require barryvdh/laravel-dompdf # Pour la génération de PDF

# Publier les assets de Filament
php artisan filament:install --panels

# Créer les modèles, migrations et controllers pour notre application
php artisan make:model Contest -m
php artisan make:model Prize -m
php artisan make:model PrizeDistribution -m
php artisan make:model Participant -m
php artisan make:model Entry -m
php artisan make:model QrCode -m

# Créer les ressources Filament pour le dashboard administratif
php artisan make:filament-resource Contest --generate
php artisan make:filament-resource Prize --generate
php artisan make:filament-resource PrizeDistribution --generate
php artisan make:filament-resource Participant --generate
php artisan make:filament-resource Entry --generate
php artisan make:filament-resource QrCode --generate

# Créer les composants Livewire pour le frontend
php artisan make:livewire FortuneWheel
php artisan make:livewire RegistrationForm
php artisan make:livewire QrCodeGenerator
php artisan make:livewire Admin/DashboardSummary
php artisan make:livewire Admin/PrizesManager
php artisan make:livewire Admin/ParticipantsManager
php artisan make:livewire Admin/DistributionsManager
php artisan make:livewire Admin/LoginForm

# Créer les contrôleurs pour les routes principales
php artisan make:controller HomeController
php artisan make:controller ScanController
php artisan make:controller AdminController

# Installer les dépendances npm
npm install

# Compiler les assets
npm run build

# Définir les permissions correctes
chmod -R 777 storage bootstrap/cache

echo "Installation terminée ! Vous pouvez maintenant démarrer l'application avec 'docker-compose -f docker-compose-laravel.yml up -d'"
