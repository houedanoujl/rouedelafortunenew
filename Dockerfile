FROM php:8.2-fpm

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
    npm

# Nettoyer le cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Obtenir Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer un utilisateur système pour exécuter les commandes Composer et Artisan
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers d'application existants
COPY . /var/www/html

# Changer la propriété du répertoire de l'application
RUN chown -R $user:$user /var/www/html

# Définir l'utilisateur
USER $user

# Exposer le port 9000 pour PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
