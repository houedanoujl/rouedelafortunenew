<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\Table;

class WhatsAppLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:logs 
                            {--limit=25 : Nombre de lignes à afficher} 
                            {--status=all : Filtrer par statut (success, error, all)} 
                            {--type=all : Filtrer par type (text, qrcode, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Affiche les logs des messages WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logPath = storage_path('logs/whatsapp.log');
        
        if (!File::exists($logPath)) {
            $this->error('Aucun fichier de log WhatsApp trouvé!');
            return 1;
        }
        
        $limit = (int) $this->option('limit');
        $statusFilter = $this->option('status');
        $typeFilter = $this->option('type');
        
        $this->info("Affichage des logs WhatsApp (max. $limit lignes)");
        
        if ($statusFilter !== 'all') {
            $this->line("Filtre par statut: $statusFilter");
        }
        
        if ($typeFilter !== 'all') {
            $this->line("Filtre par type: $typeFilter");
        }
        
        // Lire le fichier log
        $logContent = File::get($logPath);
        $logLines = array_filter(explode(PHP_EOL, $logContent));
        
        // Parcourir les lignes en sens inverse (les plus récentes d'abord)
        $logLines = array_reverse($logLines);
        
        $rows = [];
        $count = 0;
        
        foreach ($logLines as $line) {
            if ($count >= $limit) {
                break;
            }
            
            $data = json_decode($line, true);
            
            if (!$data) {
                continue;
            }
            
            // Filtrer par statut si nécessaire
            if ($statusFilter !== 'all' && $data['status'] !== $statusFilter) {
                continue;
            }
            
            // Filtrer par type si nécessaire
            if ($typeFilter !== 'all' && (!isset($data['type']) || $data['type'] !== $typeFilter)) {
                continue;
            }
            
            $rows[] = [
                'timestamp' => $data['timestamp'] ?? 'N/A',
                'status' => $data['status'] ?? 'N/A',
                'phone' => $data['phone'] ?? 'N/A',
                'message' => $data['message'] ?? 'N/A',
                'type' => $data['type'] ?? 'N/A',
                'details' => isset($data['error']) ? 'Erreur: ' . $data['error'] : 
                            (isset($data['message_id']) ? 'ID: ' . $data['message_id'] : '')
            ];
            
            $count++;
        }
        
        if (empty($rows)) {
            $this->info('Aucun message WhatsApp ne correspond aux critères de recherche.');
            return 0;
        }
        
        // Afficher un tableau avec les données
        $this->table(
            ['Timestamp', 'Statut', 'Téléphone', 'Message', 'Type', 'Détails'],
            $rows
        );
        
        return 0;
    }
}
