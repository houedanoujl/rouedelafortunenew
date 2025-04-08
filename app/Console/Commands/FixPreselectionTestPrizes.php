<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entry;
use App\Models\Contest;
use App\Models\Prize;
use App\Models\PrizeDistribution;
use Illuminate\Support\Facades\Log;

class FixPreselectionTestPrizes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-preselection-test-prizes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige les participations du concours Préselection-test pour s\'assurer que seuls des bons d\'achat de 50€ sont attribués';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la correction des prix du concours Préselection-test...');

        // Récupérer le concours Préselection-test
        $contest = Contest::where('name', 'Préselection-test')->first();

        if (!$contest) {
            $this->error('Concours "Préselection-test" non trouvé!');
            return 1;
        }

        // Trouver le bon d'achat de 50€
        $voucherPrize = Prize::where('name', 'Bon d\'achat 50€')->first();

        if (!$voucherPrize) {
            $this->error('Prix "Bon d\'achat 50€" non trouvé!');
            return 1;
        }

        // Récupérer toutes les participations gagnantes de ce concours
        $entries = Entry::where('contest_id', $contest->id)
            ->where('has_won', true)
            ->whereNotNull('prize_id')
            ->where('prize_id', '!=', $voucherPrize->id)
            ->get();

        $count = $entries->count();
        $this->info("Nombre de participations à corriger: {$count}");

        if ($count === 0) {
            $this->info('Aucune correction nécessaire.');
            return 0;
        }

        // Correction des participations
        foreach ($entries as $entry) {
            $oldPrizeId = $entry->prize_id;
            $oldPrize = Prize::find($oldPrizeId);
            $oldPrizeName = $oldPrize ? $oldPrize->name : 'Inconnu';

            // Mettre à jour l'entrée avec le bon d'achat 50€
            $entry->prize_id = $voucherPrize->id;
            $entry->save();

            Log::info('Correction prix Préselection-test', [
                'entry_id' => $entry->id,
                'participant' => $entry->participant ? "{$entry->participant->first_name} {$entry->participant->last_name}" : 'Inconnu',
                'old_prize' => $oldPrizeName,
                'new_prize' => $voucherPrize->name
            ]);

            $this->info("Participation #{$entry->id} corrigée: {$oldPrizeName} -> {$voucherPrize->name}");
        }

        // Vérifier que les distributions de prix sont correctes
        $this->info("\nVérification des distributions de prix pour le concours Préselection-test...");
        
        // Supprimer toutes les distributions de prix incorrectes pour ce concours
        $deletedDistributions = PrizeDistribution::where('contest_id', $contest->id)
            ->whereHas('prize', function ($query) {
                $query->where('name', '!=', 'Bon d\'achat 50€');
            })
            ->delete();

        $this->info("{$deletedDistributions} distributions de prix incorrectes supprimées.");

        // Vérifier si une distribution pour le bon d'achat 50€ existe déjà
        $existingDistribution = PrizeDistribution::where('contest_id', $contest->id)
            ->where('prize_id', $voucherPrize->id)
            ->first();

        if (!$existingDistribution) {
            // Créer une distribution pour le bon d'achat 50€
            PrizeDistribution::create([
                'contest_id' => $contest->id,
                'prize_id' => $voucherPrize->id,
                'quantity' => 10, // Quantité par défaut
                'remaining' => 10, // Identique à la quantité
                'start_date' => $contest->start_date,
                'end_date' => $contest->end_date,
            ]);

            $this->info("Distribution de \"Bon d'achat 50€\" créée pour le concours Préselection-test.");
        } else {
            $this->info("Distribution de \"Bon d'achat 50€\" existante mise à jour.");
        }

        $this->info("\nCorrection terminée avec succès!");
        return 0;
    }
}
