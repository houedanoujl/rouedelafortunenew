<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\Contest;
use App\Models\Prize;
use App\Models\PrizeDistribution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParticipantController extends Controller
{
    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegistrationForm(Request $request)
    {
        // Vérifier si l'utilisateur a déjà joué cette semaine
        if ($request->cookie('played_this_week')) {
            return view('already-played', [
                'message' => 'Vous avez déjà participé cette semaine. Vous pourrez rejouer dans quelques jours.',
                'days_remaining' => $this->getDaysRemaining($request->cookie('played_this_week'))
            ]);
        }
        
        $activeContest = Contest::where('status', 'active')->first();
        
        if (!$activeContest) {
            return view('no-contest');
        }
        
        return view('register', ['contestId' => $activeContest->id]);
    }
    
    /**
     * Traite l'inscription d'un participant
     */
    /**
     * Calcule le nombre de jours restants avant de pouvoir rejouer
     */
    private function getDaysRemaining($cookieValue)
    {
        $playedDate = \DateTime::createFromFormat('Y-m-d', $cookieValue);
        $now = new \DateTime();
        $interval = $playedDate->diff($now);
        
        return max(0, 7 - $interval->days);
    }
    
    public function register(Request $request)
    {
        // Vérifier si l'utilisateur a déjà joué cette semaine
        if ($request->cookie('played_this_week')) {
            return redirect()->route('home')->with('error', 'Vous avez déjà participé cette semaine.');
        }
        
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'contestId' => 'required|exists:contests,id'
        ]);
        
        // Vérifier si le participant existe déjà par téléphone
        $participant = Participant::where('phone', $request->phone)->first();
        
        // Si non trouvé par téléphone et qu'un email valide est fourni, chercher par email
        if (!$participant && $request->email) {
            $participant = Participant::where('email', $request->email)->first();
        }
        
        if (!$participant) {
            // Créer un nouveau participant
            $participant = Participant::create([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'phone' => $request->phone,
                'email' => $request->email
            ]);
        } else {
            // Mettre à jour les informations du participant
            $participant->update([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email
            ]);
        }
        
        // Vérifier si le participant a déjà participé à ce concours
        $existingEntry = Entry::where('participant_id', $participant->id)
            ->where('contest_id', $request->contestId)
            ->first();
            
        if ($existingEntry) {
            // Rediriger avec un message explicatif
            session()->flash('info', 'Vous avez déjà participé à ce concours. Vous ne pouvez participer qu\'une seule fois par concours.');
            return redirect()->route('wheel.show', ['entry' => $existingEntry->id]);
        }
        
        // Créer une nouvelle participation
        $entry = Entry::create([
            'participant_id' => $participant->id,
            'contest_id' => $request->contestId,
            'has_played' => false,
            'has_won' => false
        ]);
        
        return redirect()->route('wheel.show', ['entry' => $entry->id]);
    }
    
    /**
     * Affiche la roue de la fortune
     */
    public function showWheel(Entry $entry)
    {
        // Si l'entrée a déjà été jouée, rediriger vers la page de résultat
        if ($entry->has_played) {
            return redirect()->route('result.show', ['entry' => $entry->id]);
        }
        
        return view('wheel', ['entry' => $entry]);
    }
    
    /**
     * Affiche le résultat du spin
     */
    public function showResult(Entry $entry)
    {
        if (!$entry->has_played) {
            return redirect()->route('wheel.show', ['entry' => $entry->id]);
        }
        
        // Charger les relations pour s'assurer d'avoir accès au prix
        $entry->load('prize');
        
        return view('result', [
            'entry' => $entry,
            'qrCode' => $entry->qrCode,
            'prize' => $entry->prize // Passer le prix à la vue
        ]);
    }
    
    /**
     * Traite le résultat de la roue
     */
    public function processWheelResult(Request $request)
    {
        // Vérifier si l'utilisateur a déjà joué cette semaine
        if ($request->cookie('played_this_week')) {
            return redirect()->route('home')->with('error', 'Vous avez déjà participé cette semaine.');
        }
        
        $request->validate([
            'entry_id' => 'required|exists:entries,id',
            'prize_id' => 'nullable|exists:prizes,id'
        ]);
        
        // Utiliser find() au lieu de findOrFail()
        $entry = Entry::find($request->entry_id);
        
        // Si l'entrée n'existe pas, rediriger avec un message d'erreur
        if (!$entry) {
            return redirect()->route('home')->with('error', 'Participation introuvable. Veuillez vous inscrire à nouveau.');
        }
        
        // Marquer comme joué
        $entry->has_played = true;
        
        // Enregistrer le résultat
        if ($request->prize_id) {
            $entry->has_won = true;
            $entry->prize_id = $request->prize_id; // Associer explicitement le prix à l'entrée
        }
        
        $entry->save();
        
        // Définir un cookie qui expire dans 7 jours
        $cookieExpiry = 60 * 24 * 7; // 7 jours en minutes
        $today = new \DateTime();
        $cookieValue = $today->format('Y-m-d');
        
        return redirect()->route('result.show', ['entry' => $entry->id])
               ->cookie('played_this_week', $cookieValue, $cookieExpiry);
    }
    
    /**
     * Vérifie le QR code et retourne le résultat
     */
    public function checkQrCode($code)
    {
        // Journaliser la requête pour débogage
        \Log::info('Vérification QR code', ['code' => $code]);
        
        try {
            // Chercher l'entrée par le champ qr_code
            $entry = Entry::where('qr_code', 'LIKE', '%' . $code . '%')->first();
            
            if (!$entry) {
                \Log::warning('QR code non trouvé', ['code' => $code]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR code non valide ou introuvable.'
                ]);
            }
            
            // Charger les relations nécessaires
            $entry->load(['participant', 'prize']);
            
            // Marquer l'entrée comme réclamée si ce n'est pas déjà fait
            if (!$entry->claimed) {
                $entry->claimed = true;
                $entry->save();
                \Log::info('QR code marqué comme réclamé', ['entry_id' => $entry->id]);
            }
            
            // Déterminer le message en fonction du résultat
            $message = $entry->has_won 
                ? 'Félicitations ! Vous avez gagné : ' . ($entry->prize->name ?? 'un prix')
                : 'Pas de chance cette fois-ci. Merci d\'avoir participé !';
                
            return response()->json([
                'success' => true,
                'status' => $entry->has_won ? 'win' : 'lose',
                'message' => $message,
                'prize' => $entry->prize ? $entry->prize->name : null,
                'participant' => $entry->participant ? $entry->participant->first_name . ' ' . $entry->participant->last_name : 'Participant'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification du QR code', [
                'code' => $code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la vérification du QR code: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Affiche la page de vérification du QR code
     */
    public function qrCodeResultPage($code)
    {
        return view('qr-result', ['code' => $code]);
    }
    
    /**
     * Traite la requête AJAX pour faire tourner la roue et déterminer un résultat
     */
    public function spinWheel(Request $request)
    {
        \Log::info('Requête spinWheel reçue', $request->all());
        
        // Validation adaptée au format FormData
        $validated = $request->validate([
            'entry_id' => 'required'
        ]);
        
        try {
            // Utiliser find() au lieu de findOrFail()
            $entry = Entry::find($validated['entry_id']);
            
            // Si l'entrée n'existe pas, retourner une erreur
            if (!$entry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participation introuvable. Veuillez vous inscrire à nouveau.'
                ], 404);
            }
            
            // Vérifier si la roue a déjà été tournée pour cette entrée
            if ($entry->has_played) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette roue a déjà été tournée. Vous ne pouvez pas jouer à nouveau.'
                ]);
            }
            
            // Récupérer le concours et les distributions
            $contest = $entry->contest;
            $distributions = PrizeDistribution::where('contest_id', $contest->id)
                ->where('remaining', '>', 0)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->with('prize')
                ->get();
                
            // Vérifier si tous les stocks de prix sont épuisés
            $hasPrizesInStock = false;
            foreach ($distributions as $distribution) {
                if ($distribution->prize && $distribution->prize->stock > 0) {
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
                        'distribution_id' => null,
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
                        'distribution_id' => null,
                        'probability' => (1 - $chanceToWin) / 10, // Les secteurs perdants se partagent le reste de probabilité
                        'is_winning' => false
                    ];
                }
            }
            
            // Mélanger les secteurs pour une disposition aléatoire sur la roue
            shuffle($sectors);
            
            // Normaliser les probabilités pour qu'elles somment à 1
            $totalProbability = array_sum(array_column($sectors, 'probability'));
            if ($totalProbability > 0) {
                foreach ($sectors as &$sector) {
                    $sector['probability'] = $sector['probability'] / $totalProbability;
                }
            }
            
            // Générer un nombre aléatoire entre 0 et 1
            $rand = mt_rand(0, 100) / 100;
            
            // Sélectionner un secteur basé sur les probabilités
            $selectedSector = null;
            $cumulativeProbability = 0;
            
            foreach ($sectors as $sector) {
                $cumulativeProbability += $sector['probability'];
                if ($rand <= $cumulativeProbability) {
                    $selectedSector = $sector;
                    break;
                }
            }
            
            // Si aucun secteur n'a été sélectionné, prendre le dernier
            if (!$selectedSector && !empty($sectors)) {
                $selectedSector = end($sectors);
            }
            
            // Enregistrer le résultat dans la base de données
            DB::beginTransaction();
            
            try {
                // Déterminer le résultat (gagné ou perdu)
                $hasWon = $selectedSector && $selectedSector['is_winning'] && $hasPrizesInStock;
                
                // Si le joueur a gagné, choisir un prix aléatoirement parmi les disponibles
                $prizeId = null;
                $distributionId = null;
                
                if ($hasWon) {
                    // Collecter tous les prix disponibles avec leurs distributions
                    $availablePrizes = [];
                    foreach ($distributions as $distribution) {
                        if ($distribution->prize && $distribution->prize->stock > 0) {
                            $availablePrizes[] = [
                                'prize_id' => $distribution->prize->id,
                                'distribution_id' => $distribution->id
                            ];
                        }
                    }
                    
                    // Choisir un prix au hasard
                    if (count($availablePrizes) > 0) {
                        $randomIndex = array_rand($availablePrizes);
                        $selectedPrize = $availablePrizes[$randomIndex];
                        $prizeId = $selectedPrize['prize_id'];
                        $distributionId = $selectedPrize['distribution_id'];
                    } else {
                        // Pas de prix disponible, faire perdre le joueur
                        $hasWon = false;
                    }
                }
                
                // Mettre à jour l'entrée
                $entry->has_played = true;
                $entry->has_won = $hasWon;
                $entry->prize_id = $prizeId;
                
                $entry->save();
                
                // Si c'est un prix gagnant, mettre à jour la distribution
                if ($hasWon && $distributionId) {
                    $distribution = PrizeDistribution::find($distributionId);
                    if ($distribution) {
                        $distribution->remaining = $distribution->remaining - 1;
                        $distribution->save();
                    }
                    
                    // Mettre à jour le stock du prix
                    $prize = Prize::find($prizeId);
                    if ($prize) {
                        $prize->stock = $prize->stock - 1;
                        $prize->save();
                    }
                }
                
                // Générer un QR code unique pour cette participation
                $qrCode = 'QR-' . Str::random(8);
                $entry->qr_code = $qrCode;
                $entry->save();
                
                // Si gagné, récupérer les informations du prix pour le résultat
                $prizeInfo = null;
                if ($hasWon && $prizeId) {
                    $prize = Prize::find($prizeId);
                    if ($prize) {
                        $prizeInfo = [
                            'id' => $prize->id,
                            'name' => $prize->name,
                            'value' => $prize->value,
                            'type' => $prize->type,
                        ];
                    }
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'result' => [
                        'status' => $entry->has_won ? 'win' : 'lose',
                        'message' => $entry->has_won 
                            ? 'Félicitations ! Vous avez gagné !' 
                            : 'Pas de chance cette fois-ci. Vous pourrez réessayer ultérieurement !',
                        'prize' => $selectedSector,
                        'prize_info' => $prizeInfo
                    ],
                    'qr_code' => $entry->qr_code
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Erreur lors du traitement du résultat de la roue', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors du traitement du résultat: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors du traitement de la requête spinWheel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }
}
