# Guide de Déploiement et Correction sur Laravel Forge

## 🚀 Exécution des Scripts de Correction sur Forge

### **Méthode 1 : Connexion SSH Directe (Recommandée)**

1. **Connectez-vous à votre serveur via SSH :**
```bash
ssh forge@votre-serveur-ip
# Ou avec l'alias configuré dans Forge
ssh votre-site-forge
```

2. **Naviguez vers le répertoire de votre application :**
```bash
cd /home/forge/votre-domaine.com
# Exemple : cd /home/forge/rouedelafortune.com
```

3. **Exécutez les scripts de correction :**
```bash
# Correction des données
php fix-won-dates.php

# Validation des corrections
php validate-fixes.php

# Nettoyage du cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### **Méthode 2 : Via l'Interface Forge**

1. **Connectez-vous à votre tableau de bord Forge**
2. **Allez sur votre site → "Commands"**
3. **Exécutez les commandes une par une :**

```bash
cd /home/forge/votre-domaine.com && php fix-won-dates.php
```

```bash
cd /home/forge/votre-domaine.com && php validate-fixes.php
```

```bash
cd /home/forge/votre-domaine.com && php artisan cache:clear
```

### **Méthode 3 : Intégration au Script de Déploiement**

Modifiez votre script de déploiement Forge pour inclure automatiquement les corrections :

1. **Allez dans Forge → Site → "Deployment Script"**
2. **Ajoutez ces lignes après `php artisan migrate --force` :**

```bash
cd $FORGE_SITE_PATH

# Déploiement standard
git pull origin $FORGE_SITE_BRANCH
$FORGE_COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Migrations
php artisan migrate --force

# NOUVELLES LIGNES : Corrections automatiques
echo "🔧 Exécution des corrections de bugs Filament..."

# Vérifier si des corrections sont nécessaires et les appliquer
if [ -f "fix-won-dates.php" ]; then
    echo "📊 Correction des dates de gain..."
    php fix-won-dates.php
fi

# Validation des corrections
if [ -f "validate-fixes.php" ]; then
    echo "✅ Validation des corrections..."
    php validate-fixes.php
fi

# Nettoyage du cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:cache
php artisan config:cache

# Redémarrage des services
$FORGE_PHP_FPM reload
```

### **Méthode 4 : Commande Artisan Personnalisée (Élégante)**

Créez une commande Artisan pour faciliter l'exécution :

```bash
# Sur votre machine de développement, créez la commande :
php artisan make:command FixFilamentBugs
```

**Contenu de `app/Console/Commands/FixFilamentBugs.php` :**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entry;

class FixFilamentBugs extends Command
{
    protected $signature = 'filament:fix-bugs {--validate : Valider les corrections}';
    protected $description = 'Corrige les bugs du calendrier Filament';

    public function handle()
    {
        $this->info('🔧 Correction des bugs Filament...');

        if ($this->option('validate')) {
            $this->validateFixes();
            return;
        }

        $this->fixWonDates();
        $this->clearCaches();
        
        $this->info('✅ Corrections terminées avec succès !');
    }

    private function fixWonDates()
    {
        $this->info('📊 Correction des dates de gain...');
        
        $entriesNeedingFix = Entry::where('has_won', true)
            ->whereNull('won_date')
            ->get();

        $this->info("Trouvé {$entriesNeedingFix->count()} entrée(s) à corriger.");

        foreach ($entriesNeedingFix as $entry) {
            $entry->won_date = $entry->updated_at;
            $entry->save();
            $this->line("✓ Entrée #{$entry->id} corrigée");
        }
    }

    private function clearCaches()
    {
        $this->info('🧹 Nettoyage du cache...');
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');
    }

    private function validateFixes()
    {
        $this->info('✅ Validation des corrections...');
        
        $winnersWithoutDate = Entry::where('has_won', true)
            ->whereNull('won_date')
            ->count();

        if ($winnersWithoutDate === 0) {
            $this->info('🎉 Toutes les corrections sont appliquées !');
        } else {
            $this->error("❌ {$winnersWithoutDate} entrée(s) ont encore besoin de correction.");
        }
    }
}
```

**Puis sur Forge, exécutez simplement :**

```bash
# Correction
php artisan filament:fix-bugs

# Validation
php artisan filament:fix-bugs --validate
```

## 📋 **Checklist de Déploiement sur Forge**

### **Avant le Déploiement :**
- [ ] Pousser tous les fichiers modifiés vers Git
- [ ] S'assurer que les scripts `fix-won-dates.php` et `validate-fixes.php` sont dans le repository
- [ ] Tester les modifications en local

### **Pendant le Déploiement :**
- [ ] Déployer via Forge (push to branch ou deploy manually)
- [ ] Exécuter les corrections via une des méthodes ci-dessus
- [ ] Vérifier que l'application fonctionne

### **Après le Déploiement :**
- [ ] Tester les calendriers Filament
- [ ] Vérifier que les gagnants s'affichent aux bonnes dates
- [ ] Tester la création de nouvelles distributions de prix

## 🔐 **Sécurité et Bonnes Pratiques**

### **Variables d'Environnement :**
Assurez-vous que votre `.env` sur Forge contient :
```bash
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
# ... autres variables
```

### **Permissions :**
```bash
# Si vous avez des problèmes de permissions après SSH
sudo chown -R forge:forge /home/forge/votre-domaine.com
sudo chmod -R 755 /home/forge/votre-domaine.com/storage
sudo chmod -R 755 /home/forge/votre-domaine.com/bootstrap/cache
```

### **Backup Recommandé :**
```bash
# Avant d'exécuter les corrections
php artisan backup:run --only-db
```

## 🚨 **Dépannage Courant**

### **Si "fix-won-dates.php" n'existe pas :**
```bash
# Vérifiez que le fichier a été poussé vers Git
ls -la | grep fix-won-dates.php

# Si manquant, recréez-le ou redéployez
```

### **Si les permissions sont refusées :**
```bash
# Exécutez avec les bonnes permissions
sudo -u forge php fix-won-dates.php
```

### **Si la base de données n'est pas accessible :**
```bash
# Vérifiez la configuration
php artisan config:show database
```

## 📞 **Support**

Si vous rencontrez des problèmes :

1. **Vérifiez les logs Forge :** Site → Logs
2. **Vérifiez les logs Laravel :** `tail -f storage/logs/laravel.log`
3. **Testez la connexion DB :** `php artisan migrate:status`

---

**💡 Astuce :** La **Méthode 4 (Commande Artisan)** est la plus élégante pour la production car elle s'intègre parfaitement dans l'écosystème Laravel et peut être facilement intégrée aux scripts de déploiement Forge. 
