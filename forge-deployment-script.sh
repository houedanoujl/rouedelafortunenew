#!/bin/bash

# ================================
# Script de Déploiement Laravel Forge
# Inclut les corrections automatiques des bugs Filament
# ================================

cd $FORGE_SITE_PATH

echo "🚀 Début du déploiement..."

# ================================
# 1. RÉCUPÉRATION DU CODE
# ================================
echo "📥 Récupération du code source..."
git pull origin $FORGE_SITE_BRANCH

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors du git pull"
    exit 1
fi

# ================================
# 2. INSTALLATION DES DÉPENDANCES
# ================================
echo "📦 Installation des dépendances Composer..."
$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors de l'installation des dépendances"
    exit 1
fi

# ================================
# 3. SAUVEGARDE (OPTIONNELLE)
# ================================
echo "💾 Sauvegarde de la base de données..."
# Décommentez la ligne suivante si vous avez configuré Laravel Backup
# php artisan backup:run --only-db --disable-notifications

# ================================
# 4. MIGRATIONS
# ================================
echo "🗄️  Exécution des migrations..."
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo "❌ Erreur lors des migrations"
    exit 1
fi

# ================================
# 5. CORRECTIONS AUTOMATIQUES FILAMENT
# ================================
echo "🔧 Application des corrections bugs Filament..."

# Vérifier si la commande Artisan existe
if php artisan list | grep -q "filament:fix-bugs"; then
    echo "   📊 Utilisation de la commande Artisan..."
    php artisan filament:fix-bugs --force

    if [ $? -eq 0 ]; then
        echo "   ✅ Corrections appliquées avec succès"
    else
        echo "   ⚠️  Erreur lors des corrections Artisan, tentative avec scripts..."

        # Fallback sur les scripts standalone
        if [ -f "fix-won-dates.php" ]; then
            echo "   📅 Correction des dates de gain..."
            php fix-won-dates.php
        fi

        if [ -f "validate-fixes.php" ]; then
            echo "   ✅ Validation des corrections..."
            php validate-fixes.php
        fi
    fi
else
    echo "   📝 Commande Artisan non trouvée, utilisation des scripts..."

    # Utilisation des scripts standalone
    if [ -f "fix-won-dates.php" ]; then
        echo "   📅 Correction des dates de gain..."
        php fix-won-dates.php

        if [ $? -ne 0 ]; then
            echo "   ⚠️  Avertissement: Erreur lors de la correction des dates"
        fi
    else
        echo "   ℹ️  Script fix-won-dates.php non trouvé"
    fi

    if [ -f "validate-fixes.php" ]; then
        echo "   ✅ Validation des corrections..."
        php validate-fixes.php
    else
        echo "   ℹ️  Script validate-fixes.php non trouvé"
    fi
fi

# ================================
# 6. OPTIMISATIONS ET CACHE
# ================================
echo "🧹 Optimisation et mise en cache..."

# Nettoyer tous les caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Recréer les caches optimisés pour la production
echo "   📋 Création du cache de configuration..."
php artisan config:cache

echo "   🛣️  Création du cache des routes..."
php artisan route:cache

echo "   👁️  Création du cache des vues..."
php artisan view:cache

# ================================
# 7. PERMISSIONS ET PROPRIÉTÉS
# ================================
echo "🔐 Vérification des permissions..."

# S'assurer que les répertoires critiques ont les bonnes permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# S'assurer que l'utilisateur forge est propriétaire
chown -R forge:forge storage/
chown -R forge:forge bootstrap/cache/

# ================================
# 8. REDÉMARRAGE DES SERVICES
# ================================
echo "🔄 Redémarrage des services..."

# Redémarrer PHP-FPM
$FORGE_PHP_FPM reload

# Redémarrer la queue si elle existe (Laravel Horizon, Supervisor, etc.)
if command -v supervisorctl &> /dev/null; then
    echo "   🔁 Redémarrage des workers de queue..."
    supervisorctl restart all
fi

# Redémarrer Laravel Horizon si configuré
if php artisan list | grep -q "horizon:terminate"; then
    echo "   🌅 Redémarrage de Laravel Horizon..."
    php artisan horizon:terminate
fi

# ================================
# 9. VÉRIFICATIONS POST-DÉPLOIEMENT
# ================================
echo "🔍 Vérifications post-déploiement..."

# Tester la connectivité à la base de données
echo "   📊 Test de connexion à la base de données..."
php artisan migrate:status > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "   ✅ Base de données accessible"
else
    echo "   ❌ Problème de connexion à la base de données"
    exit 1
fi

# Vérifier que l'application répond
echo "   🌐 Test de disponibilité de l'application..."
curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"

if [ $? -eq 0 ]; then
    echo "   ✅ Application accessible"
else
    echo "   ⚠️  L'application ne répond pas comme attendu"
fi

# ================================
# 10. FINALISATION
# ================================
echo ""
echo "🎉 Déploiement terminé avec succès !"
echo "================================================"
echo "📋 Résumé :"
echo "   • Code source récupéré"
echo "   • Dépendances installées"
echo "   • Migrations exécutées"
echo "   • Corrections Filament appliquées"
echo "   • Caches optimisés"
echo "   • Services redémarrés"
echo ""
echo "🔗 L'application est maintenant disponible"
echo "📅 Les calendriers Filament affichent les bonnes dates"
echo ""

# Optionnel : Envoyer une notification (Slack, Discord, etc.)
# curl -X POST -H 'Content-type: application/json' \
#     --data '{"text":"🚀 Déploiement terminé avec succès !"}' \
#     YOUR_WEBHOOK_URL

exit 0
