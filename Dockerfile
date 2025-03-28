FROM php:8.3-fpm

# Arguments définis dans docker-compose.yml
ARG user=laravel
ARG uid=1000

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm \
    default-mysql-client \
    libicu-dev

# Nettoyer le cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Obtenir Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer un utilisateur système pour exécuter les commandes Composer et Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Définir le répertoire de travail
WORKDIR /var/www/html

# Configurer Git pour qu'il accepte le répertoire comme sûr
RUN git config --global --add safe.directory /var/www/html

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

# Copier le script d'initialisation
COPY init-laravel.sh /usr/local/bin/
COPY docker-entrypoint.sh /usr/local/bin/
USER root
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/init-laravel.sh

# Définir le point d'entrée
ENTRYPOINT ["docker-entrypoint.sh"]

# Commande par défaut
CMD ["php-fpm"]
