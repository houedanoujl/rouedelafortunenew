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
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FortuneWheel extends Component
{
    public $participant;
    public $contest;
    public $prizes = [];
    public $spinning = false;
    public $result = null;
    public $qrCodeUrl = null;

    protected $listeners = ['spin' => 'spin'];

    public function mount($participantId, $contestId)
    {
        $this->participant = Participant::findOrFail($participantId);
        $this->contest = Contest::findOrFail($contestId);
        
        // Récupérer les prix disponibles pour ce concours
        $this->loadPrizes();
    }

    public function loadPrizes()
    {
        $distributions = PrizeDistribution::where('contest_id', $this->contest->id)
            ->where('remaining', '>', 0)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->with('prize')
            ->get();
            
        $this->prizes = [];
        foreach ($distributions as $distribution) {
            if ($distribution->prize) {
                $this->prizes[] = [
                    'id' => $distribution->prize->id,
                    'name' => $distribution->prize->name,
                    'type' => $distribution->prize->type,
                    'value' => $distribution->prize->value,
                    'distribution_id' => $distribution->id,
                    'remaining' => $distribution->remaining,
                    'probability' => $distribution->remaining / $distributions->sum('remaining')
                ];
            }
        }
        
        // Ajouter une option "Pas de chance" si nécessaire
        if (count($this->prizes) > 0) {
            $this->prizes[] = [
                'id' => null,
                'name' => 'Pas de chance',
                'type' => 'none',
                'value' => 0,
                'distribution_id' => null,
                'remaining' => 1,
                'probability' => 0.3 // 30% de chance de ne rien gagner
            ];
        }
    }

    public function spin()
    {
        $this->spinning = true;
        $this->result = null;
        $this->qrCodeUrl = null;
        
        // Simuler un délai pour l'animation de la roue
        sleep(3);
        
        // Déterminer le résultat
        $this->determineResult();
        
        $this->spinning = false;
    }
    
    protected function determineResult()
    {
        // Vérifier si le participant a déjà joué aujourd'hui
        $alreadyPlayed = Entry::where('participant_id', $this->participant->id)
            ->where('contest_id', $this->contest->id)
            ->whereDate('played_at', today())
            ->exists();
            
        if ($alreadyPlayed) {
            $this->result = [
                'status' => 'error',
                'message' => 'Vous avez déjà participé aujourd\'hui.'
            ];
            return;
        }
        
        // S'il n'y a pas de prix disponibles
        if (empty($this->prizes)) {
            $this->result = [
                'status' => 'error',
                'message' => 'Aucun prix n\'est disponible actuellement.'
            ];
            return;
        }
        
        // Sélectionner un prix aléatoirement en fonction des probabilités
        $rand = mt_rand(1, 100) / 100;
        $cumulativeProbability = 0;
        $selectedPrize = null;
        
        foreach ($this->prizes as $prize) {
            $cumulativeProbability += $prize['probability'];
            if ($rand <= $cumulativeProbability) {
                $selectedPrize = $prize;
                break;
            }
        }
        
        // Si aucun prix n'a été sélectionné, choisir le dernier (pas de chance)
        if (!$selectedPrize) {
            $selectedPrize = end($this->prizes);
        }
        
        // Enregistrer le résultat
        DB::beginTransaction();
        try {
            // Créer une nouvelle participation
            $entry = Entry::create([
                'participant_id' => $this->participant->id,
                'contest_id' => $this->contest->id,
                'prize_id' => $selectedPrize['id'],
                'result' => $selectedPrize['id'] ? 'win' : 'lose',
                'played_at' => now(),
                'won_date' => $selectedPrize['id'] ? now() : null,
            ]);
            
            // Si un prix a été gagné, mettre à jour le stock
            if ($selectedPrize['id'] && $selectedPrize['distribution_id']) {
                $distribution = PrizeDistribution::find($selectedPrize['distribution_id']);
                if ($distribution) {
                    $distribution->remaining = $distribution->remaining - 1;
                    $distribution->save();
                }
                
                // Générer un QR code pour le prix
                $qrCodeText = "DINOR-" . $entry->id . "-" . time();
                $qrCodeImage = QrCode::format('png')
                    ->size(300)
                    ->errorCorrection('H')
                    ->generate($qrCodeText);
                
                $qrCodePath = 'qrcodes/' . $qrCodeText . '.png';
                \Storage::disk('public')->put($qrCodePath, $qrCodeImage);
                
                // Enregistrer le QR code dans la base de données
                QrCodeModel::create([
                    'entry_id' => $entry->id,
                    'code' => $qrCodeText,
                    'scanned' => false,
                ]);
                
                $entry->qr_code = $qrCodePath;
                $entry->save();
                
                $this->qrCodeUrl = \Storage::url($qrCodePath);
            }
            
            DB::commit();
            
            // Préparer le résultat
            $this->result = [
                'status' => $selectedPrize['id'] ? 'win' : 'lose',
                'prize' => $selectedPrize,
                'message' => $selectedPrize['id'] 
                    ? 'Félicitations ! Vous avez gagné ' . $selectedPrize['name'] 
                    : 'Pas de chance cette fois-ci. Réessayez demain !',
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->result = [
                'status' => 'error',
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ];
        }
    }

    public function render()
    {
        return view('livewire.fortune-wheel');
    }
}
