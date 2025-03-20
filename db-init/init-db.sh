#!/bin/bash
set -e

# Paramètres de connexion à PostgreSQL
PG_HOST=postgres
PG_USER=postgres
PG_PASSWORD=postgres
PG_DB=postgres
INIT_FLAG_FILE="/init-data/db_initialized"

# Fonction pour vérifier si la base de données a déjà été initialisée
check_initialized() {
    if [ -f "$INIT_FLAG_FILE" ]; then
        echo "Base de données déjà initialisée. Migrations ignorées."
        return 0
    else
        echo "Première initialisation de la base de données..."
        return 1
    fi
}

# Fonction pour exécuter les migrations
run_migrations() {
    echo "Exécution des migrations..."
    
    # Attendre que PostgreSQL soit prêt
    until PGPASSWORD=$PG_PASSWORD psql -h $PG_HOST -U $PG_USER -d $PG_DB -c '\q'; do
        echo "PostgreSQL n'est pas encore prêt - attente..."
        sleep 2
    done
    
    # Lister tous les fichiers de migration par ordre alphabétique
    for migration_file in $(ls -1 /migrations/*.sql | sort); do
        echo "Exécution de la migration: $migration_file"
        PGPASSWORD=$PG_PASSWORD psql -h $PG_HOST -U $PG_USER -d $PG_DB -f "$migration_file"
        
        # Vérifier si la migration s'est bien exécutée
        if [ $? -eq 0 ]; then
            echo "Migration réussie: $migration_file"
        else
            echo "Erreur lors de l'exécution de la migration: $migration_file"
            exit 1
        fi
    done
    
    # Créer le dossier de données si nécessaire
    mkdir -p $(dirname "$INIT_FLAG_FILE")
    
    # Créer le fichier flag pour indiquer que l'initialisation a été effectuée
    echo "$(date) - Initialisation réussie" > "$INIT_FLAG_FILE"
    echo "Toutes les migrations ont été exécutées avec succès!"
}

# Programme principal
main() {
    # Vérifier si la base de données a déjà été initialisée
    if ! check_initialized; then
        # Exécuter les migrations si c'est la première initialisation
        run_migrations
    fi
}

# Lancer le programme principal
main
