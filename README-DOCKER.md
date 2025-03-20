# Configuration Docker pour Roue de la Fortune

Ce projet est configuré pour fonctionner entièrement dans Docker, incluant l'application frontend, Supabase et la base de données PostgreSQL.

## Structure

- `docker-compose.yml` - Configuration principale de tous les services
- `db-init/` - Service d'initialisation qui exécute les migrations au premier démarrage uniquement
- `supabase-local/migrations/` - Fichiers SQL de migration pour initialiser la base de données
- `Dockerfile.app` - Configuration Docker pour l'application frontend

## Démarrage

1. **Premier démarrage (avec initialisation de la base de données)**:
   ```bash
   docker-compose up -d
   ```
   Cette commande va:
   - Démarrer tous les services (PostgreSQL, Supabase Studio, API, etc.)
   - Exécuter les migrations SQL pour initialiser la base de données
   - Démarrer l'application frontend

2. **Démarrages suivants**:
   ```bash
   docker-compose up -d
   ```
   Les démarrages suivants ne réexécuteront pas les migrations grâce au mécanisme de détection de première initialisation.

## Accès aux services

- **Application frontend**: http://localhost:8888
- **Supabase Studio**: http://localhost:3000
- **API Supabase**: http://localhost:8000
- **Base de données PostgreSQL**: localhost:5432 (accessible avec les identifiants par défaut)

## Réinitialisation complète

Si vous souhaitez réinitialiser entièrement la base de données et refaire les migrations:

```bash
# Arrêter tous les services
docker-compose down

# Supprimer les volumes pour effacer toutes les données
docker-compose down -v

# Redémarrer tout
docker-compose up -d
```

## Logs et débogage

Pour voir les logs en temps réel:
```bash
# Tous les services
docker-compose logs -f

# Un service spécifique (par ex. la base de données)
docker-compose logs -f postgres
```

## Développement

Si vous souhaitez développer en local tout en utilisant Supabase dans Docker:

1. Arrêtez uniquement le service frontend:
   ```bash
   docker-compose stop app
   ```

2. Démarrez l'application en local:
   ```bash
   npm run dev
   ```

3. Configurez vos variables d'environnement dans `.env.local`:
   ```
   SUPABASE_URL=http://localhost:8000
   SUPABASE_KEY=your-super-secret-jwt-token-with-at-least-32-characters
   ```
