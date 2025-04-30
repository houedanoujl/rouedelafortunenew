#!/bin/bash
set -e

# Fonction pour attendre que MySQL soit prêt
wait_for_mysql() {
  echo "Attente de la disponibilité de MySQL..."
  
  # Attendre que le service MySQL soit prêt en tentant de se connecter
  # Cette méthode ne nécessite aucun outil supplémentaire
  for i in {1..30}; do
    if (echo > /dev/tcp/mysql/3306) >/dev/null 2>&1; then
      echo "MySQL est accessible sur le port 3306 !"
      # Attendre un peu plus pour que MySQL soit complètement initialisé
      sleep 5
      return 0
    fi
    echo "Tentative $i/30 - En attente de MySQL..."
    sleep 3
  done
  
  echo "ERREUR: Impossible de se connecter à MySQL après 30 tentatives."
  return 1
}

# Vérifier si nous sommes dans un répertoire vide ou presque
if [ ! -f "artisan" ]; then
  echo "Création d'un nouveau projet Laravel..."
  # Créer un répertoire temporaire
  mkdir -p /tmp/laravel-temp
  cd /tmp/laravel-temp
  
  # Créer un nouveau projet Laravel
  composer create-project --prefer-dist laravel/laravel . --ignore-platform-reqs
  
  # Copier tous les fichiers vers le répertoire de travail
  cp -r * /var/www/html/
  cp -r .* /var/www/html/ 2>/dev/null || true
  
  # Retourner au répertoire de travail
  cd /var/www/html
  
  # Installation des packages supplémentaires
  echo "Installation de Filament et autres packages..."
  composer require filament/filament:^3.0 livewire/livewire:^3.0 simplesoftwareio/simple-qrcode:^4.2 --ignore-platform-reqs
elif [ ! -f "vendor/autoload.php" ]; then
  echo "Installation des dépendances Laravel..."
  composer install --no-interaction --no-progress --ignore-platform-reqs
fi

# Vérifier si le fichier .env existe
if [ ! -f ".env" ]; then
  echo "Création du fichier .env..."
  cp .env.example .env 2>/dev/null || echo "Création d'un fichier .env par défaut"
  
  # Si le fichier .env n'existe pas, créer un fichier par défaut
  if [ ! -f ".env" ]; then
    cat > .env << EOF
APP_NAME="Jeu dinor 70 ans"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8888

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=rouedelafortune
DB_USERNAME=user
DB_PASSWORD=password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
  fi

  # Générer la clé d'application
  php artisan key:generate
fi

# Attendre que MySQL soit prêt
wait_for_mysql

# Créer les répertoires nécessaires
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Vérifier si les migrations ont été exécutées
if [ ! -f "storage/app/migrations_run" ]; then
  echo "Exécution des migrations..."
  php artisan migrate --force
  
  echo "Exécution des seeders..."
  php artisan db:seed --force
  
  # Créer un fichier pour indiquer que les migrations ont été exécutées
  mkdir -p storage/app
  touch storage/app/migrations_run
fi

# Créer l'utilisateur admin par défaut
echo "Création de l'utilisateur administrateur..."
php create-admin-user.php

# Définir les permissions correctes
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Démarrer PHP-FPM
exec php-fpm
