#!/bin/bash

# Script d'initialisation pour PostgreSQL
# Ce script initialise la base de données PostgreSQL pour l'application Roue de la Fortune

echo "Initialisation de la base de données PostgreSQL..."

# Attendre que PostgreSQL soit prêt
echo "Attente que PostgreSQL soit prêt..."
until pg_isready -h postgres -p 5432 -U postgres; do
  echo "En attente de PostgreSQL..."
  sleep 2
done

echo "PostgreSQL est prêt!"

# Vérifier si la base de données existe déjà
DB_EXISTS=$(psql -h postgres -U postgres -tAc "SELECT 1 FROM pg_database WHERE datname='rouedelafortune'")

if [ "$DB_EXISTS" != "1" ]; then
  echo "Création de la base de données rouedelafortune..."
  psql -h postgres -U postgres -c "CREATE DATABASE rouedelafortune"
else
  echo "La base de données rouedelafortune existe déjà."
fi

# Créer la fonction update_updated_at_column si elle n'existe pas
echo "Création de la fonction de mise à jour automatique..."
psql -h postgres -U postgres -d rouedelafortune -c "
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS \$\$
BEGIN
   NEW.updated_at = now(); 
   RETURN NEW;
END;
\$\$ language 'plpgsql';"

# Exécuter les scripts d'initialisation
echo "Exécution des scripts d'initialisation..."
psql -h postgres -U postgres -d rouedelafortune -f /app/db/init-postgres.sql
psql -h postgres -U postgres -d rouedelafortune -f /app/db/init-postgres-admin.sql

echo "Initialisation terminée avec succès!"
