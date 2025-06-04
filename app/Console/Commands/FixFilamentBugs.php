<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entry;
use App\Services\WinLimitService;

class FixFilamentBugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament:fix-bugs {--validate : Valider uniquement les corrections sans les appliquer} {--force : Forcer l\'exécution même si tout semble correct}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige les bugs du calendrier Filament (dates de gain manquantes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Correction des bugs Filament - Calendriers');
        $this->info('================================================');

        if ($this->option('validate')) {
            return $this->validateFixes();
        }

        $this->newLine();
        $this->fixWonDates();
        $this->newLine();
        $this->validateFixes();
        $this->newLine();
        $this->clearCaches();

        $this->newLine();
        $this->info('✅ Corrections terminées avec succès !');
        $this->info('Les calendriers Filament devraient maintenant afficher les bonnes dates.');

        return Command::SUCCESS;
    }

    /**
     * Corrige les dates de gain manquantes
     */
    private function fixWonDates()
    {
        $this->info('📊 1. Correction des dates de gain manquantes...');

        // Rechercher les entrées gagnantes sans won_date
        $entriesNeedingFix = Entry::where('has_won', true)
            ->whereNull('won_date')
            ->with('participant')
            ->get();

        $count = $entriesNeedingFix->count();

        if ($count === 0) {
            $this->line('   ✅ Aucune correction nécessaire - toutes les entrées ont déjà une date de gain.');
            return;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("   Trouvé {$count} entrée(s) à corriger. Continuer ?")) {
                $this->warn('   Correction annulée par l\'utilisateur.');
                return;
            }
        }

        $this->withProgressBar($entriesNeedingFix, function ($entry) {
            // Utiliser updated_at comme date de gain par défaut
            $entry->won_date = $entry->updated_at;
            $entry->save();
        });

        $this->newLine(2);
        $this->info("   ✅ {$count} entrée(s) corrigée(s) avec succès.");

        // Afficher quelques exemples
        if ($count > 0 && $count <= 5) {
            $this->line('   Entrées corrigées :');
            foreach ($entriesNeedingFix->take(5) as $entry) {
                $participantName = $entry->participant
                    ? $entry->participant->first_name . ' ' . $entry->participant->last_name
                    : 'Participant inconnu';
                $this->line("     • Entrée #{$entry->id} ({$participantName}) - {$entry->won_date}");
            }
        }
    }

    /**
     * Valide les corrections appliquées
     */
    private function validateFixes()
    {
        $this->info('✅ 2. Validation des corrections...');

        $errors = [];
        $warnings = [];
        $success = [];

        // Vérifier le modèle Entry
        $entryModel = new Entry();
        $fillable = $entryModel->getFillable();

        if (in_array('won_date', $fillable)) {
            $success[] = "won_date est dans \$fillable du modèle Entry";
        } else {
            $errors[] = "won_date manque dans \$fillable du modèle Entry";
        }

        // Vérifier que won_date est dans casts
        $casts = $entryModel->getCasts();
        if (isset($casts['won_date']) && $casts['won_date'] === 'datetime') {
            $success[] = "won_date est correctement casté en datetime";
        } else {
            $errors[] = "won_date n'est pas correctement casté en datetime";
        }

        // Vérifier les entrées gagnantes sans won_date
        $winnersWithoutDate = Entry::where('has_won', true)
            ->whereNull('won_date')
            ->count();

        if ($winnersWithoutDate === 0) {
            $success[] = "Toutes les entrées gagnantes ont une date de gain";
        } else {
            $warnings[] = "{$winnersWithoutDate} entrée(s) gagnante(s) sans date de gain";
        }

        // Vérifier les dates incohérentes (won_date dans le futur)
        $futureWinDates = Entry::where('has_won', true)
            ->whereNotNull('won_date')
            ->where('won_date', '>', now())
            ->count();

        if ($futureWinDates === 0) {
            $success[] = "Aucune date de gain dans le futur détectée";
        } else {
            $warnings[] = "{$futureWinDates} entrée(s) avec date de gain dans le futur";
        }

        // Tester le service WinLimitService
        try {
            $winLimitService = new WinLimitService();
            $todayCount = $winLimitService->getTodayWinnersCount();
            $success[] = "WinLimitService fonctionne correctement (gagnants aujourd'hui: {$todayCount})";

            $weeklyStats = $winLimitService->getWeeklyWinningStats();
            if (is_array($weeklyStats) && count($weeklyStats) === 7) {
                $success[] = "Statistiques hebdomadaires fonctionnelles";
            } else {
                $warnings[] = "Problème avec les statistiques hebdomadaires";
            }
        } catch (\Exception $e) {
            $errors[] = "Erreur dans WinLimitService: " . $e->getMessage();
        }

        // Afficher les résultats
        if (!empty($success)) {
            $this->line('   <fg=green>✅ SUCCÈS (' . count($success) . '):</>');
            foreach ($success as $item) {
                $this->line("     • {$item}");
            }
        }

        if (!empty($warnings)) {
            $this->line('   <fg=yellow>⚠️  AVERTISSEMENTS (' . count($warnings) . '):</>');
            foreach ($warnings as $item) {
                $this->line("     • {$item}");
            }
        }

        if (!empty($errors)) {
            $this->line('   <fg=red>❌ ERREURS (' . count($errors) . '):</>');
            foreach ($errors as $item) {
                $this->line("     • {$item}");
            }
            return Command::FAILURE;
        }

        if (empty($warnings)) {
            $this->info('   🎉 Toutes les validations sont passées avec succès !');
        } else {
            $this->warn('   ⚠️  Validations passées avec quelques avertissements.');
        }

        return Command::SUCCESS;
    }

    /**
     * Nettoie les caches Laravel
     */
    private function clearCaches()
    {
        $this->info('🧹 3. Nettoyage du cache...');

        $caches = [
            'cache:clear' => 'Cache application',
            'config:clear' => 'Cache configuration',
            'view:clear' => 'Cache des vues',
            'route:clear' => 'Cache des routes'
        ];

        foreach ($caches as $command => $description) {
            try {
                $this->call($command, [], $this->getOutput());
                $this->line("   ✅ {$description} vidé");
            } catch (\Exception $e) {
                $this->line("   ⚠️  Erreur lors du nettoyage du {$description}: {$e->getMessage()}");
            }
        }

        $this->info('   🎯 Cache nettoyé avec succès.');
    }
}
