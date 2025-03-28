#!/bin/bash
set -e

echo "=== Démarrage de l'application Roue de la Fortune ==="
echo "Lancement des conteneurs Docker..."
docker compose up -d

echo "=== Les conteneurs sont démarrés ! ==="
echo "L'application sera automatiquement configurée dans le conteneur."
echo ""
echo "Vous pouvez accéder à l'application aux adresses suivantes :"
echo "- Application : http://localhost:8888"
echo "- Administration : http://localhost:8888/admin"
echo "  Identifiants : houedanou / nouveaumdp123"
echo "- phpMyAdmin : http://localhost:8081"
echo "  Identifiants : user / password (ou root / root)"
echo ""
echo "Pour voir les logs de l'application, exécutez :"
echo "docker compose logs -f app"
echo ""
echo "Pour arrêter l'application, exécutez :"
echo "docker compose down"
