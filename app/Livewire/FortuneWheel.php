<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\QrCode;
use App\Services\InfobipService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FortuneWheel extends Component
{
    public Entry $entry;
    public bool $spinning = false;
    public bool $showWheel = true;

    // Constantes pour la roue 
    const SECTORS_COUNT = 10; // Nombre total de secteurs
    const SECTOR_ANGLE = 36;  // Angle de chaque secteur (360 / SECTORS_COUNT)
    
    /**
     * Initialisation du composant avec l'entrée
     */
    public function mount(Entry $entry)
    {
        $this->entry = $entry;
        $this->showWheel = !$entry->has_played;
    }

    /**
     * Fait tourner la roue et traite le résultat
     */
    public function spin()
    {
        // Vérifier si c'est un utilisateur de test autorisé à rejouer
        $isTestUser = false;
        
        if ($this->entry->participant && $this->entry->participant->email === 'noob@saibot.com') {
            $isTestUser = true;
            // Réinitialiser l'entrée pour permettre de rejouer
            if ($this->entry->has_played) {
                // On ne retourne pas, on continue l'exécution
                Log::info('Utilisateur test autorisé à rejouer', [
                    'email' => $this->entry->participant->email,
                    'entry_id' => $this->entry->id
                ]);
            }
        } else if ($this->entry->has_played) {
            // Pour tous les autres utilisateurs, on ne peut pas rejouer
            return;
        }

        $this->spinning = true;

        // Vérifier s'il y a des prix en stock disponibles
        $contest = $this->entry->contest;
        $hasPrizesInStock = false;
        
        // Recherche de distributions de prix avec stock > 0
        $distributions = \App\Models\PrizeDistribution::where('contest_id', $contest->id)
            ->where('remaining', '>', 0)
            ->with(['prize' => function($query) {
                $query->where('stock', '>', 0);
            }])
            ->get()
            ->filter(function($distribution) {
                return $distribution->prize !== null;
            });
            
        if(count($distributions) > 0) {
            $hasPrizesInStock = true;
        }
        
        // Journaliser l'état du stock
        Log::info('Vérification du stock', [
            'has_prizes_in_stock' => $hasPrizesInStock ? 'oui' : 'non',
            'email' => $this->entry->participant->email ?? 'inconnu'
        ]);

        // Si aucun prix en stock, forcer le résultat à "perdant"
        $isWinning = $hasPrizesInStock && (rand(1, 20) <= 1);
        
        // Déterminer le secteur et l'angle final en fonction du résultat souhaité
        $sectorInfo = $this->determineSector($isWinning);
        $finalAngle = $sectorInfo['angle'];
        $sectorIndex = $sectorInfo['index'];
        $sectorId = $sectorInfo['id'];
        
        // Est-ce un secteur gagnant ? (Les secteurs pairs sont gagnants)
        $isResultWinning = $sectorIndex % 2 === 0;
        
        // Journaliser les informations du secteur et de l'angle
        Log::info('Informations du secteur sélectionné', [
            'angle' => $finalAngle,
            'secteur_index' => $sectorIndex,
            'secteur_id' => $sectorId,
            'est_gagnant' => $isResultWinning ? 'oui' : 'non',
            'classe' => $isResultWinning ? 'secteur-gagne' : 'secteur-perdu'
        ]);
        
        // Utiliser le résultat déterminé par le secteur, pas par le tirage au sort
        $this->entry->has_played = true;
        $this->entry->has_won = $isResultWinning;
        $this->entry->save();
        
        // Enregistrer le résultat dans l'historique JSON
        $this->saveSpinHistory($finalAngle, $isResultWinning, $sectorId, $this->entry);
        
        // Si gagné, créer un QR code et envoyer une notification WhatsApp
        if ($isResultWinning) {
            $this->handleWinning();
        }

        // Déclencher l'animation de la roue
        $this->dispatch('startSpinWithSound', [
            'angle' => $finalAngle,
            'isWinning' => $isResultWinning ? 1 : 0,
            'sectorId' => $sectorId,
            'sectorIndex' => $sectorIndex
        ]);
        
        // Si gagné, déclencher les confettis
        if ($isResultWinning) {
            $this->dispatch('victory');
        }

        // Rediriger après la fin de l'animation
        // Augmentation du délai à 20 secondes pour tenir compte de l'animation plus longue (12-16 secondes)
        $this->js("setTimeout(() => { window.location.href = '" . route('spin.result', ['entry' => $this->entry->id]) . "' }, 20000)");
    }
    
    /**
     * Détermine le secteur et l'angle final en fonction du résultat souhaité
     * 
     * @param bool $isWinning
     * @return array
     */
    private function determineSector(bool $isWinning): array
    {
        // Indices des secteurs disponibles
        $possibleIndices = [];
        
        // Pour la roue, nous voulons des secteurs complets
        for ($i = 0; $i < self::SECTORS_COUNT; $i++) {
            // Vérifier si le secteur correspond au résultat souhaité (gagnant ou perdant)
            // Les secteurs pairs (0, 2, 4, 6, 8) sont gagnants, les impairs sont perdants
            $isSectorWinning = $i % 2 === 0;
            
            if ($isSectorWinning === $isWinning) {
                $possibleIndices[] = $i;
            }
        }
        
        // Choisir un secteur au hasard parmi ceux qui correspondent au résultat souhaité
        $randomIndex = array_rand($possibleIndices);
        $sectorIndex = $possibleIndices[$randomIndex];
        
        // Calculer l'angle du secteur
        // Chaque secteur fait 36 degrés (360 / 10 secteurs)
        $sectorAngle = $sectorIndex * self::SECTOR_ANGLE;
        
        // Générer un angle aléatoire au sein du secteur choisi
        // Pour s'assurer que la roue s'arrête bien au milieu du secteur
        $minSectorAngle = $sectorAngle;
        $maxSectorAngle = $sectorAngle + self::SECTOR_ANGLE - 1;
        $preciseSectorAngle = rand($minSectorAngle, $maxSectorAngle);
        
        // Ajouter plusieurs tours complets (entre 3 et 5 tours) pour une animation plus réaliste
        $numSpins = rand(3, 5); 
        $finalAngle = ($numSpins * 360) + (360 - $preciseSectorAngle);
        
        Log::info('Angle final calculé', [
            'secteur' => $sectorIndex,
            'est_gagnant' => $isWinning ? 'oui' : 'non',
            'angle_secteur' => $sectorAngle,
            'angle_précis' => $preciseSectorAngle,
            'angle_final' => $finalAngle,
            'tours' => $numSpins
        ]);
        
        return [
            'index' => $sectorIndex,
            'id' => 'secteur-' . $sectorIndex,
            'angle' => $finalAngle,
            'isWinning' => $isWinning
        ];
    }
    
    /**
     * Gère la logique lorsque le joueur gagne
     */
    private function handleWinning()
    {
        // Générer un code QR plus lisible et mémorisable
        $qrCode = 'DNR70-' . strtoupper(substr(md5($this->entry->id . time()), 0, 8));
        
        // Créer l'enregistrement QR code
        $qrCodeModel = QrCode::create([
            'entry_id' => $this->entry->id,
            'code' => $qrCode,
        ]);
        
        // Décrémenter le stock dans la distribution de prix active
        $contest = $this->entry->contest;
        if ($contest) {
            // Chercher une distribution de prix active pour ce concours
            $prizeDistribution = \App\Models\PrizeDistribution::where('contest_id', $contest->id)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where('remaining', '>', 0)
                ->first();
            
            // Décrémenter le stock restant
            if ($prizeDistribution) {
                $prizeDistribution->decrementRemaining();
                
                // Journaliser la mise à jour du stock
                Log::info('Stock décrémenté pour la distribution', [
                    'prize_distribution_id' => $prizeDistribution->id,
                    'remaining' => $prizeDistribution->remaining,
                ]);
            }
        }
        
        // Récupérer le participant pour obtenir son numéro de téléphone
        $participant = $this->entry->participant;
        
        // Envoyer une notification WhatsApp si un numéro de téléphone est disponible
        if ($participant && $participant->phone) {
            try {
                // Créer une instance du service Infobip
                $infobipService = new InfobipService();
                
                // Envoyer la notification WhatsApp
                $infobipService->sendWhatsAppNotification(
                    $participant->phone, 
                    $participant->first_name . ' ' . $participant->last_name,
                    $qrCode
                );
                
                // Journaliser le succès
                Log::info('Notification WhatsApp envoyée avec succès', [
                    'participant_id' => $participant->id,
                    'phone' => $participant->phone,
                    'qr_code' => $qrCode
                ]);
            } catch (\Exception $e) {
                // Journaliser l'erreur mais continuer le processus
                Log::error('Erreur lors de l\'envoi de la notification WhatsApp', [
                    'error' => $e->getMessage(),
                    'participant_id' => $participant->id,
                    'phone' => $participant->phone,
                    'qr_code' => $qrCode
                ]);
            }
        } else {
            Log::warning('Impossible d\'envoyer une notification WhatsApp : numéro de téléphone manquant', [
                'entry_id' => $this->entry->id,
                'participant_id' => $participant ? $participant->id : null
            ]);
        }
    }

    /**
     * Enregistre le résultat du spin dans un fichier JSON historique
     */
    protected function saveSpinHistory(int $finalAngle, bool $isWinning, string $sectorId, Entry $entry)
    {
        try {
            // Récupérer l'adresse IP du joueur
            $ip = request()->ip();
            
            // Chemin du fichier d'historique à la racine du projet
            $historyFile = base_path('spin_history.json');
            $csvFile = storage_path('app/public/participations.csv');
            
            // Données à enregistrer
            $spinData = [
                'timestamp' => Carbon::now()->toIso8601String(),
                'entry_id' => $entry->id,
                'participant' => $entry->participant ? [
                    'id' => $entry->participant->id,
                    'name' => $entry->participant->first_name . ' ' . $entry->participant->last_name,
                    'email' => $entry->participant->email,
                    'ip_address' => $ip
                ] : null,
                'contest_id' => $entry->contest_id,
                'angle' => $finalAngle,
                'sector_id' => $sectorId,
                'sector_class' => $isWinning ? 'secteur-gagne' : 'secteur-perdu',
                'result' => $entry->has_won ? 'win' : 'lose', // Utiliser la valeur réelle en BDD plutôt que l'angle visuel
                'has_won_in_db' => $entry->has_won,
                'ip_address' => $ip
            ];
            
            // Créer ou charger le fichier existant
            $history = [];
            if (file_exists($historyFile)) {
                $historyContent = file_get_contents($historyFile);
                $history = json_decode($historyContent, true) ?: [];
            }
            
            // Ajouter l'enregistrement actuel
            $history[] = $spinData;
            
            // Sauvegarder le fichier mis à jour
            file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT));
            
            // Mettre à jour le fichier CSV également
            $this->updateCsvHistory($entry, $isWinning, $ip, $csvFile);
            
            Log::info('Historique de spin enregistré (JSON et CSV)', [
                'entry_id' => $entry->id,
                'angle' => $finalAngle,
                'sector_id' => $sectorId,
                'result' => $entry->has_won ? 'win' : 'lose',
                'ip_address' => $ip,
                'file_path' => $historyFile
            ]);
        } catch (\Exception $e) {
            // En cas d'erreur, on log mais on ne bloque pas le processus
            Log::error('Erreur lors de l\'enregistrement de l\'historique de spin', [
                'error' => $e->getMessage(),
                'entry_id' => $entry->id
            ]);
        }
    }
    
    /**
     * Met à jour le fichier CSV avec les informations de participation
     */
    private function updateCsvHistory(Entry $entry, bool $isWinning, string $ip, string $csvFile)
    {
        try {
            // S'assurer que le répertoire existe
            $directory = dirname($csvFile);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // En-têtes du CSV
            $headers = [
                'Date', 
                'ID', 
                'Prénom', 
                'Nom', 
                'Email', 
                'Téléphone', 
                'Résultat', 
                'Adresse IP',
                'Date de naissance',
                'Adresse',
                'Code postal',
                'Ville'
            ];
            
            // Créer le fichier avec les en-têtes s'il n'existe pas
            $fileExists = file_exists($csvFile);
            $handle = fopen($csvFile, 'a'); // ouverture en mode ajout
            
            if (!$fileExists) {
                fputcsv($handle, $headers);
            }
            
            // Récupérer les infos du participant
            $participant = $entry->participant;
            if ($participant) {
                $data = [
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $entry->id,
                    $participant->first_name ?? 'N/A',
                    $participant->last_name ?? 'N/A',
                    $participant->email ?? 'N/A',
                    $participant->phone ?? 'N/A',
                    $isWinning ? 'GAGNÉ' : 'PERDU',
                    $ip,
                    $participant->birth_date ?? 'N/A',
                    $participant->address ?? 'N/A',
                    $participant->postal_code ?? 'N/A',
                    $participant->city ?? 'N/A'
                ];
                
                // Écrire la ligne dans le CSV
                fputcsv($handle, $data);
            }
            
            fclose($handle);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du fichier CSV', [
                'error' => $e->getMessage(),
                'entry_id' => $entry->id
            ]);
        }
    }

    public function render()
    {
        return view('livewire.fortune-wheel');
    }
}
