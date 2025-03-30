<?php

namespace App\Livewire;

use App\Models\Contest;
use App\Models\Entry;
use App\Models\Participant;
use App\Models\Prize;
use App\Models\PrizeDistribution;
use App\Models\QrCode as QrCodeModel;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Str;

class FortuneWheel extends Component
{
    public $entry;
    public $availablePrizes = [];
    public $distributions = [];
    public $spinning = false;
    public $result = null;
    public $qrCodeUrl = null;

    protected $listeners = ['spin' => 'spin'];

    public function mount($entry, $prizes, $distributions)
    {
        $this->entry = $entry;
        $this->distributions = $distributions;
        
        // Initialiser les tableaux pour les secteurs gagnants et perdants
        $winningSectors = [];
        $losingSectors = [];
        
        // Préparation des prix disponibles avec leur distribution associée
        foreach ($distributions as $distribution) {
            if ($distribution->prize && $distribution->prize->stock > 0 && $distribution->remaining > 0) {
                $winningSectors[] = [
                    'id' => $distribution->prize->id,
                    'name' => 'Gagné',
                    'type' => $distribution->prize->type,
                    'value' => $distribution->prize->value,
                    'distribution_id' => $distribution->id,
                    'remaining' => $distribution->remaining,
                    'probability' => $distribution->remaining / $distributions->sum('remaining'),
                    'is_winning' => true
                ];
            }
        }
        
        // Limiter à exactement 5 secteurs gagnants
        if (count($winningSectors) > 5) {
            $winningSectors = collect($winningSectors)->take(5)->toArray();
        } elseif (count($winningSectors) < 5) {
            // Si nous avons moins de 5 prix disponibles, on duplique certains pour arriver à 5
            $existingCount = count($winningSectors);
            if ($existingCount > 0) {
                for ($i = 0; $i < (5 - $existingCount); $i++) {
                    $index = $i % $existingCount; // Permet de boucler sur les secteurs existants
                    $winningSectors[] = $winningSectors[$index];
                }
            } else {
                // Aucun prix disponible, créer 5 secteurs génériques "Pas de prix disponible"
                for ($i = 0; $i < 5; $i++) {
                    $winningSectors[] = [
                        'id' => null,
                        'name' => 'Prix épuisé',
                        'type' => 'none',
                        'value' => 0,
                        'distribution_id' => null,
                        'remaining' => 0,
                        'probability' => 0.1,
                        'is_winning' => false // Les secteurs de prix épuisés sont en réalité perdants
                    ];
                }
            }
        }
        
        // Créer exactement le même nombre de secteurs perdants
        $winningCount = count($winningSectors);
        for ($i = 1; $i <= $winningCount; $i++) {
            $losingSectors[] = [
                'id' => null,
                'name' => 'Pas de chance',
                'type' => 'none',
                'value' => 0,
                'distribution_id' => null,
                'remaining' => 1,
                'probability' => 0.5 / $winningCount, // Probabilité partagée équitablement
                'is_winning' => false
            ];
        }
        
        // Alternance des secteurs gagnants et perdants
        $allSectors = [];
        for ($i = 0; $i < $winningCount; $i++) {
            $allSectors[] = $winningSectors[$i]; // Secteur gagnant
            $allSectors[] = $losingSectors[$i];  // Secteur perdant
        }
        
        $this->availablePrizes = $allSectors;
    }

    public function spin()
    {
        // Si déjà en cours de rotation, ignorer
        if ($this->spinning) {
            return;
        }
        
        // Marquer comme en rotation
        $this->spinning = true;
        
        // Réinitialiser les résultats
        $this->result = null;
        $this->qrCodeUrl = null;
        
        // Déclencher l'événement pour l'animation côté client
        $this->dispatch('spin', ['timestamp' => now()->timestamp]);
        
        // Contrairement à avant, on ne bloque PAS avec sleep ici
        // L'animation sera gérée côté client et le processus Livewire
        // sera appelé après l'animation via JavaScript
        
        // Détermination du résultat
        $this->determineResult();
        
        // Fin de l'animation
        $this->spinning = false;
    }
    
    protected function determineResult()
    {
        // S'il n'y a pas de prix disponibles
        if (empty($this->availablePrizes)) {
            $this->result = [
                'status' => 'error',
                'message' => 'Aucun prix n\'est disponible actuellement.'
            ];
            return;
        }
        
        // Générer un nombre aléatoire entre 0 et 1
        $rand = mt_rand(0, 100) / 100;
        
        // Sélectionner un prix en fonction des probabilités
        $cumulativeProbability = 0;
        $selectedPrize = null;
        
        foreach ($this->availablePrizes as $prize) {
            $cumulativeProbability += $prize['probability'];
            if ($rand <= $cumulativeProbability) {
                $selectedPrize = $prize;
                break;
            }
        }
        
        // Si aucun prix n'a été sélectionné, choisir le dernier
        if (!$selectedPrize) {
            $selectedPrize = end($this->availablePrizes);
        }
        
        // Enregistrer le résultat
        DB::beginTransaction();
        try {
            // Mettre à jour l'entrée
            $this->entry->result = $selectedPrize['is_winning'] ? 'win' : 'lose';
            $this->entry->prize_id = $selectedPrize['id'];
            $this->entry->played_at = now();
            $this->entry->won_date = $selectedPrize['is_winning'] ? now() : null;
            $this->entry->save();
            
            // Si c'est un prix gagnant, mettre à jour le stock et les distributions
            if ($selectedPrize['is_winning'] && $selectedPrize['distribution_id']) {
                // Mettre à jour la distribution de prix
                $distribution = PrizeDistribution::find($selectedPrize['distribution_id']);
                if ($distribution) {
                    $distribution->remaining = $distribution->remaining - 1;
                    $distribution->save();
                    
                    // Mettre à jour le stock du prix
                    $prize = Prize::find($selectedPrize['id']);
                    if ($prize) {
                        $prize->stock = $prize->stock - 1;
                        $prize->save();
                    }
                }
                
                // Générer un simple code texte unique, le QR code sera généré côté client avec JavaScript
                $qrCodeText = "DINOR-" . $this->entry->id . "-" . Str::random(8);
                
                // Enregistrer le QR code dans la base de données
                QrCodeModel::create([
                    'entry_id' => $this->entry->id,
                    'code' => $qrCodeText,
                    'scanned' => false,
                ]);
                
                $this->entry->qr_code = $qrCodeText;
                $this->entry->save();
                
                // Stocker le texte du QR code pour l'affichage
                $this->qrCodeUrl = $qrCodeText;
            }
            else {
                // En cas de perte, définir un cookie pour empêcher de jouer pendant une semaine
                cookie()->queue('played_fortune_wheel', '1', 10080); // 10080 minutes = 7 jours
            }
            
            DB::commit();
            
            $this->result = [
                'status' => $selectedPrize['is_winning'] ? 'win' : 'lose',
                'message' => $selectedPrize['is_winning'] 
                    ? 'Félicitations ! Vous avez gagné : ' . $selectedPrize['name']
                    : 'Pas de chance cette fois-ci. Vous pourrez réessayer dans une semaine !',
                'prize' => $selectedPrize,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->result = [
                'status' => 'error',
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ];
        }
    }

    public function render()
    {
        return view('livewire.fortune-wheel', [
            'prizes' => $this->availablePrizes
        ]);
    }
}
