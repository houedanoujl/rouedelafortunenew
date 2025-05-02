<?php

namespace App\Console\Commands;

use App\Models\Entry;
use App\Models\QrCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanDuplicateQrCodes extends Command
{
    protected $signature = 'qrcodes:clean-duplicates {--dry-run : Simuler l\'opération sans supprimer}';
    protected $description = 'Nettoie les codes QR dupliqués en ne gardant que le plus récent pour chaque participation';

    public function handle()
    {
        $this->info('Début du nettoyage des codes QR dupliqués...');
        
        // Obtenir les ID des entrées qui ont plusieurs codes QR
        $entriesWithMultipleQrCodes = DB::table('qr_codes')
            ->select('entry_id', DB::raw('COUNT(*) as qr_count'))
            ->groupBy('entry_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        $this->info("Trouvé {$entriesWithMultipleQrCodes->count()} participations avec des codes QR multiples.");
        
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('Mode simulation activé : aucune suppression ne sera effectuée.');
        }
        
        $totalDeleted = 0;
        
        // Pour chaque entrée avec plusieurs codes QR
        foreach ($entriesWithMultipleQrCodes as $entry) {
            // Obtenir tous les codes QR pour cette entrée
            $qrCodes = QrCode::where('entry_id', $entry->entry_id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            $this->info("Entrée ID {$entry->entry_id} a {$qrCodes->count()} codes QR:");
            
            // Afficher tous les codes QR pour cette entrée
            foreach ($qrCodes as $index => $qrCode) {
                $participant = Entry::find($entry->entry_id)->participant;
                $participantName = $participant ? $participant->first_name . ' ' . $participant->last_name : 'Inconnu';
                
                $status = ($index === 0) ? '<info>[À CONSERVER]</info>' : '<comment>[À SUPPRIMER]</comment>';
                $this->line("  {$status} ID: {$qrCode->id}, Code: {$qrCode->code}, Créé: {$qrCode->created_at}, Participant: {$participantName}");
                
                // Supprimer tous les codes QR sauf le plus récent
                if ($index > 0 && !$isDryRun) {
                    $qrCode->delete();
                    $totalDeleted++;
                }
            }
        }
        
        if ($isDryRun) {
            $this->info("Simulation terminée. {$totalDeleted} codes QR auraient été supprimés.");
            $this->info("Pour exécuter la suppression réelle, relancez la commande sans l'option --dry-run.");
        } else {
            $this->info("Nettoyage terminé. {$totalDeleted} codes QR dupliqués ont été supprimés.");
        }
        
        return Command::SUCCESS;
    }
}
