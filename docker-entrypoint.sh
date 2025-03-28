#!/bin/bash
set -e

# Fonction pour attendre que MySQL soit prêt
wait_for_mysql() {
  echo "Attente de la disponibilité de MySQL..."
  while ! mysql -h mysql -u user -ppassword -e "SELECT 1" >/dev/null 2>&1; do
    sleep 1
  done
  echo "MySQL est prêt !"
}

# Vérifier si le projet Laravel est déjà installé
if [ ! -f "vendor/autoload.php" ]; then
  echo "Installation des dépendances Laravel..."
  composer install --no-interaction --no-progress
fi

# Vérifier si le fichier .env existe
if [ ! -f ".env" ]; then
  echo "Création du fichier .env..."
  cp .env.example .env 2>/dev/null || echo "Création d'un fichier .env par défaut"
  
  # Si le fichier .env n'existe pas, créer un fichier par défaut
  if [ ! -f ".env" ]; then
    cat > .env << EOF
APP_NAME="Roue de la Fortune"
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

# Vérifier si les migrations ont été exécutées
if [ ! -f "storage/app/migrations_run" ]; then
  # Attendre que MySQL soit prêt avant d'exécuter les migrations
  wait_for_mysql
  
  echo "Exécution des migrations..."
  php artisan migrate --force
  
  echo "Exécution des seeders..."
  php artisan db:seed --force
  
  # Créer un fichier pour indiquer que les migrations ont été exécutées
  mkdir -p storage/app
  touch storage/app/migrations_run
fi

# Optimiser l'application pour la production
if [ "$APP_ENV" = "production" ]; then
  echo "Optimisation de l'application pour la production..."
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

# Créer l'utilisateur admin par défaut
php artisan tinker --execute="
    try {
        \$user = \App\Models\User::where('email', 'houedanou@example.com')->first();
        if (!\$user) {
            \App\Models\User::create([
                'name' => 'houedanou',
                'email' => 'houedanou@example.com',
                'password' => bcrypt('nouveaumdp123')
            ]);
            echo 'Utilisateur admin créé avec succès!';
        } else {
            echo 'L\'utilisateur admin existe déjà.';
        }
    } catch (\Exception \$e) {
        echo 'Erreur lors de la création de l\'utilisateur admin: ' . \$e->getMessage();
    }
"

# Définir les permissions correctes
echo "Configuration des permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Exécuter la commande passée en argument
echo "Démarrage de l'application..."
exec "$@"
