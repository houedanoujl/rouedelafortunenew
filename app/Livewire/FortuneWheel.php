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
        
        // Vérifier si nous avons une configuration de roue sauvegardée
        if ($entry->wheel_config) {
            // Utiliser la configuration sauvegardée
            $this->availablePrizes = json_decode($entry->wheel_config, true);
            return;
        }
        
        // Vérifier si tous les stocks de prix sont épuisés
        $hasPrizesInStock = false;
        foreach ($distributions as $distribution) {
            if ($distribution->prize && $distribution->prize->stock > 0 && $distribution->remaining > 0) {
                $hasPrizesInStock = true;
                break;
            }
        }
        
        // Définir des probabilités de gagner (10% chance de gagner)
        $chanceToWin = 0.10; // 10% de chance de gagner
        
        // Créer 20 secteurs au total: 10 gagnants, 10 perdants
        $sectors = [];
        
        // Si aucun prix en stock, tous les secteurs sont perdants
        if (!$hasPrizesInStock) {
            // 20 secteurs perdants
            for ($i = 0; $i < 20; $i++) {
                $sectors[] = [
                    'id' => null,
                    'name' => 'Pas de chance',
                    'type' => 'none',
                    'value' => 0,
                    'distribution_id' => null,
                    'remaining' => 0,
                    'probability' => 1/20, // Probabilité égale
                    'is_winning' => false
                ];
            }
        } else {
            // Ajouter 10 secteurs gagnants (tous indiquent "Gagné" sans préciser le lot)
            for ($i = 0; $i < 10; $i++) {
                $sectors[] = [
                    'id' => 'win', // Juste un marqueur, le vrai prix sera choisi aléatoirement
                    'name' => 'Gagné',
                    'type' => 'win',
                    'value' => 0,
                    'distribution_id' => null, // Sera déterminé si le joueur gagne
                    'probability' => $chanceToWin / 10, // Chaque secteur gagnant a une probabilité égale
                    'is_winning' => true
                ];
            }
            
            // Ajouter 10 secteurs perdants
            for ($i = 0; $i < 10; $i++) {
                $sectors[] = [
                    'id' => null,
                    'name' => 'Pas de chance',
                    'type' => 'none',
                    'value' => 0,
                    'distribution_id' => null,
                    'probability' => (1 - $chanceToWin) / 10, // Les secteurs perdants se partagent le reste de probabilité
                    'is_winning' => false
                ];
            }
        }
        
        // Mélanger les secteurs pour une disposition aléatoire sur la roue
        shuffle($sectors);
        
        $this->availablePrizes = $sectors;
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
        
        // L'animation est gérée côté client et le résultat sera déterminé par la requête AJAX
        
        // Fin de l'animation
        $this->spinning = false;
    }
    
    protected function determineResult()
    {
        // La détermination du résultat est maintenant gérée par le contrôleur via la requête AJAX
        // Cette méthode reste pour la compatibilité et le debug éventuel
        
        $this->result = [
            'status' => 'wait',
            'message' => 'Veuillez attendre la fin de l\'animation...'
        ];
    }

    public function render()
    {
        return view('livewire.fortune-wheel', [
            'prizes' => $this->availablePrizes
        ]);
    }
}
