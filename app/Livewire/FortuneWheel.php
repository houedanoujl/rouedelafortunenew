<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\QrCode;
use App\Models\PrizeDistribution;
use App\Services\InfobipService;
use App\Services\WinLimitService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FortuneWheel extends Component
{
    public Entry $entry;
    public bool $spinning = false;
    public bool $showWheel = true;
    public bool $hasStock = false; // Nouvelle propriété pour stocker l'état des stocks
    public array $stockInfo = []; // Information sur les stocks disponibles

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
        
        // Vérifier les stocks AVANT l'affichage de la roue
        $this->checkStocksBeforeRender();
        
        // Journaliser l'état des stocks au chargement de la page
        $this->logStockStatus();
        
        // Vérifier l'état des stocks et passer l'information au frontend
        $this->checkStockAvailability();
    }
    
    /**
     * Vérifie l'état des stocks avant le rendu de la page
     */
    private function checkStocksBeforeRender()
    {
        // Récupérer le concours actif
        $contest = $this->entry->contest;
        if (!$contest) {
            $this->hasStock = false;
            return;
        }
        
        // Chercher les distributions de prix pour ce concours avec des prix disponibles
        $distributions = PrizeDistribution::where('contest_id', $contest->id)
            ->where('remaining', '>', 0)
            ->with('prize')
            ->get();
        
        $validDistributions = $distributions->filter(function($dist) {
            return $dist->prize !== null;
        });
        
        // Stocker l'état des stocks
        $this->hasStock = $validDistributions->count() > 0;
        
        // Stocker des informations sur les stocks pour le frontend
        $this->stockInfo = [
            'has_stock' => $this->hasStock,
            'valid_count' => $validDistributions->count(),
            'distributions' => $validDistributions->map(function($dist) {
                return [
                    'id' => $dist->id,
                    'prize_id' => $dist->prize_id,
                    'prize_name' => $dist->prize ? $dist->prize->name : 'Inconnu',
                    'remaining' => $dist->remaining
                ];
            })->toArray()
        ];
    }
    
    /**
     * Vérifie l'état des stocks et passer l'information au frontend
     */
    private function checkStockAvailability()
    {
        // Récupérer le concours actif
        $contest = $this->entry->contest;
        if (!$contest) {
            // Si pas de concours, pas de stock
            $this->dispatch('stock-status', [
                'has_stock' => false,
                'message' => 'Aucun concours actif'
            ]);
            return;
        }
        
        // Chercher les distributions de prix pour ce concours avec des prix disponibles
        $distributions = PrizeDistribution::where('contest_id', $contest->id)
            ->where('remaining', '>', 0)
            ->with('prize')
            ->get();
        
        $validDistributions = $distributions->filter(function($dist) {
            return $dist->prize !== null;
        });
        
        // Envoyer l'état au frontend
        $this->dispatch('stock-status', [
            'has_stock' => $validDistributions->count() > 0,
            'valid_count' => $validDistributions->count(),
            'distributions' => $validDistributions->map(function($dist) {
                return [
                    'id' => $dist->id,
                    'prize_id' => $dist->prize_id,
                    'prize_name' => $dist->prize ? $dist->prize->name : 'Inconnu',
                    'remaining' => $dist->remaining,
                    'valid' => true
                ];
            })
        ]);
    }
    
    /**
     * Vérifie et journaliser l'état de tous les stocks disponibles
     */
    private function logStockStatus()
    {
        try {
            // Récupérer tous les concours actifs
            $activeContests = \App\Models\Contest::where('status', 'active')->get();
            
            Log::info('====== VÉRIFICATION DES STOCKS AU DÉMARRAGE ======');
            Log::info('Nombre de concours actifs: ' . count($activeContests));
            
            $allStockData = [];
            
            foreach ($activeContests as $contest) {
                Log::info("Vérification des stocks pour le concours: {$contest->name} (ID: {$contest->id})");
                
                // Récupérer toutes les distributions de prix pour ce concours
                $distributions = \App\Models\PrizeDistribution::where('contest_id', $contest->id)
                    ->with('prize')
                    ->get();
                
                if ($distributions->isEmpty()) {
                    Log::warning("Aucune distribution de prix trouvée pour le concours {$contest->name}");
                    continue;
                }
                
                $contestStockData = [
                    'contest_name' => $contest->name,
                    'contest_id' => $contest->id,
                    'distributions' => []
                ];
                
                // Journaliser chaque distribution
                foreach ($distributions as $dist) {
                    $distData = [
                        'distribution_id' => $dist->id,
                        'prize_name' => $dist->prize ? $dist->prize->name : 'Prix NULL',
                        'quantite_totale' => $dist->quantity,
                        'restant' => $dist->remaining,
                        'prix_id' => $dist->prize_id,
                        'prix_stock' => $dist->prize ? $dist->prize->stock : 'N/A',
                        'statut' => $dist->remaining > 0 ? 'DISPONIBLE' : 'ÉPUISÉ'
                    ];
                    
                    $contestStockData['distributions'][] = $distData;
                    
                    Log::info("Distribution #{$dist->id}: " . ($dist->prize ? $dist->prize->name : 'Prix NULL'), $distData);
                }
                
                // Compter les distributions disponibles
                $availableDistributions = $distributions->filter(function($dist) {
                    return $dist->prize !== null && $dist->remaining > 0;
                });
                
                $contestStockData['available_count'] = count($availableDistributions);
                $contestStockData['total_count'] = count($distributions);
                
                $allStockData[] = $contestStockData;
                
                Log::info("Résumé pour le concours {$contest->name}: " . count($availableDistributions) . " prix disponibles sur " . count($distributions) . " distributions");
            }
            
            // Envoyer les données à la console du navigateur
            $this->dispatch('stock-status-init', [
                'contests' => $allStockData,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
            
            Log::info('====== FIN DE LA VÉRIFICATION DES STOCKS ======');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des stocks', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Envoyer l'erreur à la console également
            $this->dispatch('stock-status-error', [
                'message' => $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Fait tourner la roue et traite le résultat
     */
    public function spin()
    {
        try {
            // Définir l'état de tournage à vrai
            $this->spinning = true;
            
            // Si la roue a déjà été tournée, on ne permet pas de tourner à nouveau
            if ($this->entry->has_played) {
                return redirect()->route('spin.result', ['entry' => $this->entry->id]);
            }

            // Vérifier les stocks encore une fois (double vérification) pour éviter les manipulations
            $this->checkStocksBeforeRender();
            
            if (!$this->hasStock) {
                // Rediriger vers une page indiquant qu'il n'y a plus de stock
                $this->dispatch('no-stock');
                return;
            }
            
            // Générer un résultat (gagné/perdu) basé sur la probabilité
            $isWinning = $this->calculateWinChance();
            
            // Déterminer le secteur et l'angle final
            $result = $this->determineSector($isWinning);
            $finalAngle = $result['angle'];
            $sectorIndex = $result['index'];
            $sectorId = $result['id'];
            
            // Journaliser la décision
            Log::info('Décision de spin générée', [
                'entry_id' => $this->entry->id,
                'is_winning' => $isWinning ? 'Oui' : 'Non',
                'final_angle' => $finalAngle,
                'sector_id' => $sectorId
            ]);
            
            // Est-ce un secteur gagnant ? (Les secteurs pairs sont gagnants)
            $isResultWinning = $sectorIndex % 2 === 0;
            
            // Utiliser le résultat déterminé par le secteur, pas par le tirage au sort
            $this->entry->has_played = true;
            $this->entry->has_won = $isResultWinning;
            $this->entry->save();
            
            // Si c'est un gain, incrémenter le compteur quotidien
            if ($isResultWinning) {
                app(WinLimitService::class)->incrementTodayWinnersCount();
                
                Log::info('Compteur de gagnants incrémenté pour aujourd\'hui', [
                    'entry_id' => $this->entry->id,
                    'date' => Carbon::now()->toDateString()
                ]);
            }
            
            // Enregistrer le résultat dans l'historique JSON
            $this->saveSpinHistory($finalAngle, $isResultWinning, $sectorId, $this->entry);
            
            // Déclencher l'animation de la roue
            $this->dispatch('startSpinWithSound', [
                'angle' => $finalAngle,
                'isWinning' => $isResultWinning ? 1 : 0,
                'sectorId' => $sectorId,
                'sectorIndex' => $sectorIndex
            ]);
            
            // Stocke le résultat pour traitement après la fin de l'animation
            session()->put('wheel_result', [
                'entry_id' => $this->entry->id,
                'isWinning' => $isResultWinning,
                'processed' => false
            ]);

            // Rediriger après la fin de l'animation
            // Augmentation du délai à 20 secondes pour tenir compte de l'animation plus longue (12-16 secondes)
            $this->js("setTimeout(() => { window.location.href = '" . route('spin.result', ['entry' => $this->entry->id]) . "' }, 20000)");
        } catch (\Exception $e) {
            Log::error('Erreur lors du spin', [
                'error' => $e->getMessage(),
                'entry_id' => $this->entry->id
            ]);
        }
    }
    
    /**
     * Calcule la probabilité de gain selon la règle du jeu
     * @return bool
     */
    private function calculateWinChance(): bool
    {
        // Instancier le service de limite de gains
        $winLimitService = app(WinLimitService::class);
        
        // Vérifier si on a atteint la limite quotidienne de gagnants
        if (!$winLimitService->canWinToday()) {
            Log::info('Limite quotidienne de gagnants atteinte, résultat forcé à perdant', [
                'entry_id' => $this->entry->id,
                'date' => Carbon::now()->toDateString()
            ]);
            return false; // Forcer le résultat à perdant
        }
        
        // Si la limite n'est pas atteinte, calculer normalement
        // Ajustement de la probabilité pour atteindre environ 2 gagnants par jour
        // Supposons qu'il y ait en moyenne 200 participants par jour
        // 2 gagnants sur 200 = 1% de chance
        return rand(1, 100) === 1; // 1% de chance de gagner (1/100)
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
        
        // NOTE: La décrémentation des stocks a été déplacée vers SpinController
        // pour éviter la double décrémentation
        $contest = $this->entry->contest;
        if ($contest) {
            // Journaliser l'information que nous n'effectuons plus la décrémentation ici
            Log::info('Décrémentation des stocks désactivée dans handleWinning() pour éviter les doubles décrements', [
                'entry_id' => $this->entry->id,
                'contest_id' => $contest->id
            ]);
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
