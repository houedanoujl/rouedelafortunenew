<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\QrCode;
use Livewire\Component;
use Illuminate\Support\Str;

class FortuneWheel extends Component
{
    public Entry $entry;
    public bool $spinning = false;
    public bool $showWheel = true;

    public function mount(Entry $entry)
    {
        $this->entry = $entry;
        $this->showWheel = !$entry->has_played;
    }

    public function spin()
    {
        if ($this->entry->has_played) {
            return;
        }

        $this->spinning = true;

        // Réduire les chances de gagner à 30% (au lieu de 50%)
        $isWinning = rand(1, 10) <= 3;
        
        // Nous avons 20 secteurs, donc chaque secteur fait 18 degrés (360/20)
        $sectorAngle = 18;
        
        // Important: Nous voulons que le pointeur s'arrête au centre d'un secteur,
        // pas à la jonction entre deux secteurs
        
        if ($isWinning) {
            // Pour les secteurs gagnants (secteurs pairs: 0, 2, 4, 6, 8, 10, 12, 14, 16, 18)
            // Nous choisissons un secteur pair au hasard
            $sectorIndex = rand(0, 9) * 2;
            // Puis nous calculons l'angle qui correspond au centre de ce secteur
            $finalAngle = ($sectorIndex * $sectorAngle) + ($sectorAngle / 2);
        } else {
            // Pour les secteurs perdants (secteurs impairs: 1, 3, 5, 7, 9, 11, 13, 15, 17, 19)
            // Nous choisissons un secteur impair au hasard
            $sectorIndex = (rand(0, 9) * 2) + 1;
            // Puis nous calculons l'angle qui correspond au centre de ce secteur
            $finalAngle = ($sectorIndex * $sectorAngle) + ($sectorAngle / 2);
        }

        // Mettre à jour l'entrée
        $this->entry->has_played = true;
        $this->entry->has_won = $isWinning;
        $this->entry->save();

        // Si gagné, créer un QR code
        if ($isWinning) {
            QrCode::create([
                'entry_id' => $this->entry->id,
                'code' => Str::random(10),
            ]);
        }

        // Déclencher l'animation de la roue
        // S'assurer que l'angle est bien un entier
        $finalAngle = (int)$finalAngle;
        $this->dispatch('startSpinWithSound', ['angle' => $finalAngle]);
        
        // Si gagné, déclencher les confettis
        if ($isWinning) {
            $this->dispatch('victory');
        }

        // Rediriger après la fin de l'animation (13.5 secondes au lieu de 8.5)
        $this->js("setTimeout(() => { window.location.href = '" . route('spin.result', ['entry' => $this->entry->id]) . "' }, 13500)");
    }

    public function render()
    {
        return view('livewire.fortune-wheel');
    }
}
