# Roue de la Fortune - Application Laravel avec Docker

Cette application Laravel "Roue de la Fortune" est configurée pour fonctionner avec Docker, ce qui facilite le déploiement et le développement.

## Prérequis

- Docker et Docker Compose installés sur votre machine

## Installation et démarrage

1. Clonez ce dépôt :
   ```bash
   git clone https://github.com/jhouedanou/rouedelafortune.git
   cd rouedelafortune
   ```

2. Créez et configurez le fichier .env :
   ```bash
   cp .env.example .env
   ```

3. Lancez l'application avec Docker Compose :
   ```bash
   docker compose up -d
   ```

4. Installez les dépendances PHP :
   ```bash
   docker compose exec app composer install
   ```

5. Générez la clé d'application Laravel :
   ```bash
   docker compose exec app php artisan key:generate
   ```

6. Exécutez les migrations de base de données :
   ```bash
   docker compose exec app php artisan migrate
   ```

7. Remplissez la base de données avec des données de test :
   ```bash
   docker compose exec app php artisan db:seed
   ```

8. Installez les dépendances NPM :
   ```bash
   docker compose exec app npm install
   ```

9. Compilez les assets frontend :
   ```bash
   docker compose exec app npm run build
   ```

10. Définissez les permissions des répertoires de stockage :
    ```bash
    docker compose exec app chmod -R 777 storage bootstrap/cache
    ```

11. Nettoyez les caches de configuration :
    ```bash
    docker compose exec app php artisan config:clear
    docker compose exec app php artisan cache:clear
    docker compose exec app php artisan view:clear
    ```

12. L'application sera accessible aux adresses suivantes :
    - Application principale : http://localhost:8888
    - Interface d'administration : http://localhost:8888/admin
    - Interface phpMyAdmin : http://localhost:8081

## Résolution des problèmes courants

### Erreur 502 Bad Gateway

Si vous rencontrez une erreur 502 Bad Gateway, vérifiez que la configuration Nginx est correcte :

```bash
docker compose exec nginx bash -c "cat > /etc/nginx/nginx.conf << 'EOF'
events {
    worker_connections 1024;
}

http {
    include       /etc/nginx/mime.types;
    default_type  application/octet-stream;
    
    server {
        listen 8888;
        root /var/www/html/public;
        index index.php;
        
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }
        
        location ~ \.php$ {
            fastcgi_pass app:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
EOF"
```

Puis redémarrez Nginx :
```bash
docker compose restart nginx
```

### Erreur Vite Manifest Not Found

Si vous rencontrez une erreur concernant le manifeste Vite, assurez-vous d'avoir bien exécuté les commandes pour compiler les assets frontend (étapes 8 et 9).


## Commandes Docker utiles

### Afficher les logs des conteneurs
```bash
docker compose logs app
docker compose logs nginx
docker compose logs mysql
```

### Redémarrer tous les services
```bash
docker compose restart
```

### Arrêter l'application
```bash
docker compose down
```

### Reconstruire les conteneurs
```bash
docker compose build
docker compose up -d
```

### Accéder au shell du conteneur de l'application
```bash
docker compose exec app bash
```

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

## Licence

Ce projet est sous licence MIT.
