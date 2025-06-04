<?php

/**
 * Script pour corriger les dates de gain manquantes (won_date)
 * À exécuter avec: php fix-won-dates.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

echo "=== CORRECTION DES DATES DE GAIN MANQUANTES ===\n\n";

try {
    // Initialiser Laravel pour avoir accès aux modèles
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    // Importer les modèles nécessaires
    $entryModel = new App\Models\Entry();

    echo "1. Recherche des entrées gagnantes sans date de gain...\n";

    // Trouver toutes les entrées avec has_won = true mais won_date = null
    $entriesNeedingFix = App\Models\Entry::where('has_won', true)
        ->whereNull('won_date')
        ->get();

    echo "   Trouvé " . $entriesNeedingFix->count() . " entrée(s) à corriger.\n\n";

    if ($entriesNeedingFix->count() > 0) {
        echo "2. Correction en cours...\n";

        $correctedCount = 0;
        foreach ($entriesNeedingFix as $entry) {
            // Utiliser updated_at comme date de gain par défaut
            $entry->won_date = $entry->updated_at;
            $entry->save();

            $correctedCount++;
            echo "   ✓ Entrée #{$entry->id} corrigée (won_date = {$entry->won_date})\n";
        }

        echo "\n3. Résumé :\n";
        echo "   - {$correctedCount} entrée(s) corrigée(s) avec succès\n";
        echo "   - Toutes les entrées gagnantes ont maintenant une date de gain (won_date)\n";
    } else {
        echo "2. Aucune correction nécessaire - toutes les entrées gagnantes ont déjà une date de gain.\n";
    }

    // Vérification finale
    echo "\n4. Vérification finale...\n";
    $remainingIssues = App\Models\Entry::where('has_won', true)->whereNull('won_date')->count();

    if ($remainingIssues === 0) {
        echo "   ✅ SUCCÈS : Toutes les entrées gagnantes ont maintenant une date de gain correcte.\n";
    } else {
        echo "   ❌ ATTENTION : Il reste encore {$remainingIssues} entrée(s) sans date de gain.\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
    echo "Assurez-vous que Laravel est correctement configuré et que la base de données est accessible.\n";
    exit(1);
}

echo "\n=== SCRIPT TERMINÉ ===\n";
