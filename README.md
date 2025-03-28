# Roue de la Fortune DINOR

## Démarrage rapide avec Docker

1. Cloner le dépôt
```bash
git clone <repository-url>
cd rouedelafortune
```

2. Lancer l'application
```bash
docker compose up -d 
```

3. Accéder aux interfaces
- Application : http://localhost:8888
- Administration : http://localhost:8888/admin (houedanou / nouveaumdp123)
- Base de données : http://localhost:8081 (user / password ou root / root)

## Toutes les commandes utiles

### Gestion des conteneurs
```bash
# Démarrer l'application
docker compose up -d

# Démarrer avec reconstruction des images
docker compose up -d --build

# Arrêter l'application
docker compose down

# Arrêter et supprimer les volumes (réinitialise la base de données)
docker compose down -v

# Voir l'état des conteneurs
docker compose ps
```

### Logs et débogage
```bash
# Voir les logs de tous les services
docker compose logs

# Voir les logs de l'application Laravel
docker compose logs -f app

# Voir les logs de MySQL
docker compose logs -f mysql

# Voir les logs de Nginx
docker compose logs -f nginx
```

### Commandes Laravel et base de données
```bash
# Exécuter une commande Artisan
docker compose exec app php artisan <commande>

# Exemples de commandes Artisan courantes
docker compose exec app php artisan migrate        # Exécuter les migrations
docker compose exec app php artisan db:seed        # Exécuter les seeders
docker compose exec app php artisan cache:clear    # Vider le cache
docker compose exec app php artisan route:list     # Lister les routes
docker compose exec app php artisan make:controller NomController  # Créer un contrôleur

# Accéder au shell du conteneur
docker compose exec app bash

# Exécuter Composer
docker compose exec app composer <commande>
docker compose exec app composer install    # Installer les dépendances
docker compose exec app composer update     # Mettre à jour les dépendances
docker compose exec app composer require <package>  # Ajouter un package

# Accéder à MySQL en ligne de commande
docker compose exec mysql mysql -u user -ppassword rouedelafortune
```

### Maintenance
```bash
# Vérifier l'utilisation des ressources
docker stats

# Nettoyer les images, conteneurs et volumes inutilisés
docker system prune -a

# Redémarrer un service spécifique
docker compose restart app
docker compose restart nginx
docker compose restart mysql
``` 
