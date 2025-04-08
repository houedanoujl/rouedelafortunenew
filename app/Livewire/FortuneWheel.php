<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\QrCode;
use App\Services\InfobipService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
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

        // Si gagné, créer un QR code et envoyer une notification WhatsApp
        if ($isWinning) {
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
                    \Illuminate\Support\Facades\Log::info('Stock décrémenté pour la distribution', [
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
