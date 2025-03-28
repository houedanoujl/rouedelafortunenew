#!/bin/bash

echo "Arrêt des conteneurs Docker..."
docker compose down

echo "Nettoyage des volumes pour repartir de zéro (facultatif)"
read -p "Souhaitez-vous nettoyer complètement les volumes de données? [y/N] " clean_volumes
if [[ "$clean_volumes" =~ ^[Yy]$ ]]; then
  echo "Suppression des volumes..."
  docker volume rm rouedelafortune_mysql-data
  docker volume rm rouedelafortune_supabase-db-data
  docker volume rm rouedelafortune_supabase-storage-data
  echo "Volumes supprimés."
fi

echo "Démarrage des services..."
docker compose up -d

echo "Les services sont en cours de démarrage..."
echo "Interfaces disponibles :"
echo "- Application principale : http://localhost:8888"
echo "- PhpMyAdmin (MySQL) : http://localhost:8080"
echo "- Supabase API : http://localhost:8000"

echo "Identifiants d'administration :"
echo "- Interface admin : http://localhost:8888/admin"
echo "- Nom d'utilisateur : houedanou"
echo "- Mot de passe : admin123"

echo "Configuration terminée !"
