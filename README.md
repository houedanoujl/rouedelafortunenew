# Jeu dinor 70 ans - Roue de la Fortune

[![Site Deployment Status](https://img.shields.io/badge/70%20ans%20Dinor-Déployé-success)](https://github.com/jhouedanou/rouedelafortune)

Application "Roue de la Fortune" pour les 70 ans de Dinor, utilisant Laravel avec Filament comme panneau d'administration, configurée pour fonctionner avec Docker.

## 📋 Structure de l'application

L'application est basée sur Laravel et utilise Filament pour l'interface d'administration. Elle comprend :

- **Contest** : Concours (nom, dates, statut, description)
- **Prize** : Prix (nom, description, type, valeur, image, stock)
- **Participant** : Participants (prénom, nom, téléphone, email)
- **Entry** : Participations (lien vers participant, concours, prix, résultat, date de jeu, code QR, réclamation)
- **QrCode** : Codes QR (lié à une participation, code, statut de scan)
- **PrizeDistribution** : Distribution des prix

## 🚀 Guide de déploiement local

### Prérequis

- Docker et Docker Compose installés sur votre machine
- Git pour cloner le dépôt

### Instructions de déploiement en un seul bloc

Pour déployer rapidement l'application en une seule opération :

```bash
# Cloner le dépôt (si pas déjà fait)
git clone https://github.com/jhouedanou/rouedelafortune.git
cd rouedelafortune

# Démarrer les conteneurs Docker
docker compose up -d

# Attendre que les conteneurs soient prêts (MySQL doit être accessible)
echo "Attente de l'initialisation de MySQL..."
while ! docker exec rouedelafortune-mysql mysqladmin ping -h localhost --silent; do
    sleep 2
done
echo "MySQL est prêt!"

# Exécuter les migrations et créer l'utilisateur admin
docker exec rouedelafortune-app php artisan migrate --force
docker exec rouedelafortune-app php artisan tinker --execute="\$user = \App\Models\User::where('email', 'houedanou@example.com')->first(); if(!\$user) { \App\Models\User::create(['name' => 'houedanou', 'email' => 'houedanou@example.com', 'password' => bcrypt('nouveaumdp123')]); echo 'Utilisateur admin créé avec succès!'; }"

# Installer les dépendances frontend et compiler les assets
docker exec rouedelafortune-app npm ci
docker exec rouedelafortune-app npm run build

# Nettoyage des caches et optimisations
docker exec rouedelafortune-app php artisan optimize:clear
docker exec rouedelafortune-app php artisan filament:assets
docker exec rouedelafortune-app php artisan storage:link

echo "✅ Déploiement terminé! Accédez à l'application sur http://localhost:8888"
```

### Instructions étape par étape

1. **Cloner le dépôt** :
   ```bash
   git clone https://github.com/jhouedanou/rouedelafortune.git
   cd rouedelafortune
   ```

2. **Lancer les conteneurs Docker** :
   ```bash
   docker compose up -d
   ```

3. **Exécuter les migrations de base de données** :
   ```bash
   docker exec rouedelafortune-app php artisan migrate --force
   ```

4. **Créer l'utilisateur administrateur** :
   ```bash
   docker exec rouedelafortune-app php artisan tinker --execute="\$user = \App\Models\User::where('email', 'houedanou@example.com')->first(); if(!\$user) { \App\Models\User::create(['name' => 'houedanou', 'email' => 'houedanou@example.com', 'password' => bcrypt('nouveaumdp123')]); echo 'Utilisateur admin créé avec succès!'; }"
   ```

5. **Installer les dépendances frontend** :
   ```bash
   docker exec rouedelafortune-app npm ci
   ```

6. **Compiler les assets** :
   ```bash
   docker exec rouedelafortune-app npm run build
   ```

7. **Nettoyer les caches** :
   ```bash
   docker exec rouedelafortune-app php artisan optimize:clear
   ```

8. **Publier les assets Filament** :
   ```bash
   docker exec rouedelafortune-app php artisan filament:assets
   ```

9. **Créer le lien symbolique pour le stockage** :
   ```bash
   docker exec rouedelafortune-app php artisan storage:link
   ```

## 🖥️ Accès à l'application

Une fois déployée, l'application est accessible aux adresses suivantes :

- **Application principale** : [http://localhost:8888](http://localhost:8888)
- **Interface d'administration** : [http://localhost:8888/admin](http://localhost:8888/admin)
- **Interface phpMyAdmin** : [http://localhost:8081](http://localhost:8081)

## 📱 Configuration des notifications WhatsApp

L'application utilise le service Green API pour envoyer des notifications WhatsApp aux gagnants. Les participants recevront un message contenant :
- Les félicitations personnalisées
- Le nom du prix gagné
- Le QR code à présenter
- Le numéro de contact : **07 19 04 87 28**

## 🛠️ Maintenance et dépannage

### Commandes utiles

```bash
# Voir les logs des conteneurs
docker logs -f rouedelafortune-app
docker logs -f rouedelafortune-nginx
docker logs -f rouedelafortune-mysql

# Redémarrer les services
docker compose restart

# Accéder au shell du conteneur
docker exec -it rouedelafortune-app bash

# Correction migration problématique
docker exec rouedelafortune-app php artisan tinker --execute="DB::table('migrations')->where('migration', '2025_04_03_164500_add_prize_id_to_entries_table')->delete();"
```

### Résolution des problèmes courants

#### Erreur 502 Bad Gateway
Redémarrez le conteneur Nginx :
```bash
docker compose restart nginx
```

#### Problème avec les migrations
Si vous rencontrez des problèmes avec les migrations, essayez de neutraliser la migration problématique :
```bash
docker exec rouedelafortune-app php artisan tinker --execute="DB::table('migrations')->where('migration', '2025_04_03_164500_add_prize_id_to_entries_table')->delete();"
```

#### Problème avec les assets
Si Vite Manifest n'est pas trouvé, réinstallez et recompilez les assets :
```bash
docker exec rouedelafortune-app npm ci
docker exec rouedelafortune-app npm run build
```

## 📚 Documentation supplémentaire

Pour plus d'informations sur le développement et l'extension de l'application :

### Création de ressources Filament

```bash
docker exec rouedelafortune-app php artisan make:filament-resource NomDuModele
```

### Création de nouveaux modèles

```bash
docker exec rouedelafortune-app php artisan make:model NomDuModele -m
```

## 📄 Licence

Ce projet est sous licence MIT.
