# Guide de Configuration de Supabase Local pour Roue de la Fortune

Ce guide explique comment configurer une instance locale de Supabase pour le développement et tester l'application Roue de la Fortune.

## Prérequis

- Docker Desktop installé et fonctionnel
- Node.js installé (pour les scripts d'import/export)

## Démarrer Supabase Local

1. Ouvrez une invite de commande et naviguez vers ce répertoire:
   ```
   cd supabase-local
   ```

2. Démarrez les conteneurs Docker:
   ```
   docker-compose up -d
   ```

3. Vérifiez que tous les services sont démarrés:
   ```
   docker-compose ps
   ```

4. Accédez à Supabase Studio dans votre navigateur:
   http://localhost:3000

## Importer les Données depuis Supabase Distance

1. Assurez-vous que le répertoire `data` existe dans ce dossier (supabase-local).

2. Exécutez le script d'exportation pour récupérer les données de Supabase:
   ```
   cd ..
   node scripts/export-supabase-data.js
   ```

3. Exécutez le script d'importation pour charger les données dans Supabase local:
   ```
   node scripts/import-supabase-data.js
   ```

## Configuration de l'Application

Pour que l'application utilise Supabase local au lieu de la version distante, assurez-vous que les variables d'environnement dans `.env.local` sont correctement configurées:

```
SUPABASE_URL=http://localhost:8000
SUPABASE_KEY=your-super-secret-jwt-token-with-at-least-32-characters
```

## Arrêter Supabase Local

Pour arrêter les conteneurs:
```
cd supabase-local
docker-compose down
```

Pour supprimer également les volumes (données):
```
docker-compose down -v
```

## Dépannage

1. Si vous rencontrez des problèmes de connexion à Supabase local:
   - Vérifiez que tous les conteneurs sont en cours d'exécution: `docker-compose ps`
   - Consultez les journaux: `docker-compose logs -f`

2. Problèmes d'importation de données:
   - Assurez-vous que les tables existent dans la base de données locale
   - Vérifiez que l'ordre d'importation dans le script respecte les contraintes de clé étrangère
