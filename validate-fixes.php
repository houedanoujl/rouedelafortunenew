<?php

/**
 * Script de validation des corrections apportées aux bugs Filament
 * À exécuter avec: php validate-fixes.php
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "=== VALIDATION DES CORRECTIONS BUGS FILAMENT ===\n\n";

try {
    // Initialiser Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $errors = [];
    $warnings = [];
    $success = [];

    echo "🔍 1. Vérification du modèle Entry...\n";

    // Vérifier que won_date est dans fillable
    $entryModel = new App\Models\Entry();
    $fillable = $entryModel->getFillable();

    if (in_array('won_date', $fillable)) {
        $success[] = "✅ won_date est dans \$fillable du modèle Entry";
    } else {
        $errors[] = "❌ won_date manque dans \$fillable du modèle Entry";
    }

    // Vérifier que won_date est dans casts
    $casts = $entryModel->getCasts();
    if (isset($casts['won_date']) && $casts['won_date'] === 'datetime') {
        $success[] = "✅ won_date est correctement casté en datetime";
    } else {
        $errors[] = "❌ won_date n'est pas correctement casté en datetime";
    }

    echo "🔍 2. Vérification de la cohérence des données...\n";

    // Vérifier les entrées gagnantes sans won_date
    $winnersWithoutWonDate = App\Models\Entry::where('has_won', true)
        ->whereNull('won_date')
        ->count();

    if ($winnersWithoutWonDate === 0) {
        $success[] = "✅ Toutes les entrées gagnantes ont une date de gain (won_date)";
    } else {
        $warnings[] = "⚠️  {$winnersWithoutWonDate} entrée(s) gagnante(s) sans date de gain - Exécuter fix-won-dates.php";
    }

    // Vérifier les dates incohérentes (won_date dans le futur)
    $futureWinDates = App\Models\Entry::where('has_won', true)
        ->whereNotNull('won_date')
        ->where('won_date', '>', now())
        ->count();

    if ($futureWinDates === 0) {
        $success[] = "✅ Aucune date de gain dans le futur détectée";
    } else {
        $warnings[] = "⚠️  {$futureWinDates} entrée(s) avec date de gain dans le futur";
    }

    echo "🔍 3. Vérification des fichiers modifiés...\n";

    // Vérifier que les fichiers existent et contiennent les modifications
    $filesToCheck = [
        'app/Services/WinLimitService.php' => 'won_date',
        'app/Models/Entry.php' => 'won_date',
        'app/Http/Controllers/ParticipantController.php' => 'won_date = now()',
        'app/Livewire/FortuneWheel.php' => 'won_date = now()',
        'app/Http/Controllers/SpinResultController.php' => 'won_date = now()',
        'app/Filament/Resources/PrizeDistributionResource.php' => 'La date de fin doit être postérieure',
        'app/Filament/Pages/WinnersList.php' => 'won_date'
    ];

    foreach ($filesToCheck as $file => $searchTerm) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (strpos($content, $searchTerm) !== false) {
                $success[] = "✅ {$file} contient les modifications attendues";
            } else {
                $errors[] = "❌ {$file} ne contient pas '{$searchTerm}'";
            }
        } else {
            $errors[] = "❌ Fichier manquant: {$file}";
        }
    }

    echo "🔍 4. Vérification des statistiques...\n";

    // Tester le service WinLimitService
    $winLimitService = new App\Services\WinLimitService();

    try {
        $todayCount = $winLimitService->getTodayWinnersCount();
        $success[] = "✅ WinLimitService::getTodayWinnersCount() fonctionne (résultat: {$todayCount})";

        $weeklyStats = $winLimitService->getWeeklyWinningStats();
        if (is_array($weeklyStats) && count($weeklyStats) === 7) {
            $success[] = "✅ WinLimitService::getWeeklyWinningStats() retourne 7 jours";
        } else {
            $warnings[] = "⚠️  WinLimitService::getWeeklyWinningStats() ne retourne pas 7 jours";
        }

        $monthlyStats = $winLimitService->getMonthlyWinningStats();
        if (is_array($monthlyStats) && count($monthlyStats) > 0) {
            $success[] = "✅ WinLimitService::getMonthlyWinningStats() fonctionne";
        } else {
            $warnings[] = "⚠️  WinLimitService::getMonthlyWinningStats() retourne un résultat vide";
        }

    } catch (Exception $e) {
        $errors[] = "❌ Erreur dans WinLimitService: " . $e->getMessage();
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "📊 RÉSULTATS DE LA VALIDATION\n";
    echo str_repeat("=", 60) . "\n\n";

    if (!empty($success)) {
        echo "✅ SUCCÈS (" . count($success) . "):\n";
        foreach ($success as $item) {
            echo "   {$item}\n";
        }
        echo "\n";
    }

    if (!empty($warnings)) {
        echo "⚠️  AVERTISSEMENTS (" . count($warnings) . "):\n";
        foreach ($warnings as $item) {
            echo "   {$item}\n";
        }
        echo "\n";
    }

    if (!empty($errors)) {
        echo "❌ ERREURS (" . count($errors) . "):\n";
        foreach ($errors as $item) {
            echo "   {$item}\n";
        }
        echo "\n";
    }

    // Résumé final
    if (empty($errors)) {
        if (empty($warnings)) {
            echo "🎉 PARFAIT ! Toutes les corrections ont été appliquées avec succès.\n";
            echo "Les bugs du calendrier des gagnants et de distribution des prix sont corrigés.\n";
        } else {
            echo "✅ BIEN ! Les corrections principales sont appliquées.\n";
            echo "Il y a quelques avertissements mineurs à vérifier.\n";
        }
    } else {
        echo "🚨 ATTENTION ! Il y a des erreurs qui doivent être corrigées.\n";
        echo "Veuillez vérifier les fichiers mentionnés ci-dessus.\n";
    }

} catch (Exception $e) {
    echo "❌ ERREUR CRITIQUE : " . $e->getMessage() . "\n";
    echo "Assurez-vous que Laravel est correctement configuré.\n";
    exit(1);
}

echo "\n=== VALIDATION TERMINÉE ===\n";
