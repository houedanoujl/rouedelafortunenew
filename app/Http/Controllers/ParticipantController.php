<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\Contest;
use App\Models\Prize;
use App\Models\PrizeDistribution;
use App\Models\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ParticipantController extends Controller
{
    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegistrationForm()
    {
        $activeContest = Contest::where('status', 'active')->first();
        
        if (!$activeContest) {
            return view('no-contest');
        }
        
        return view('register', ['contestId' => $activeContest->id]);
    }
    
    /**
     * Traite l'inscription d'un participant
     */
    public function register(Request $request)
    {
        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'contestId' => 'required|exists:contests,id'
        ]);
        
        // Vérifier si le participant existe déjà
        $participant = Participant::where('phone', $request->phone)->first();
        
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
            return redirect()->route('wheel.show', ['entry' => $existingEntry->id]);
        }
        
        // Créer une nouvelle participation
        $entry = Entry::create([
            'participant_id' => $participant->id,
            'contest_id' => $request->contestId,
            'qr_code' => 'QR-' . Str::random(8),
            'played_at' => now(),
            'result' => 'en attente',
        ]);
        
        return redirect()->route('wheel.show', ['entry' => $entry->id]);
    }
    
    /**
     * Affiche la roue de la fortune
     */
    public function showWheel($entryId)
    {
        try {
            $entry = Entry::findOrFail($entryId);
            
            // Récupérer le concours associé à l'inscription
            $contest = $entry->contest;
            
            // Récupérer les prix disponibles via les distributions de prix
            $distributions = PrizeDistribution::where('contest_id', $contest->id)
                ->where('remaining', '>', 0)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->with('prize')
                ->get();
                
            $prizes = collect();
            foreach ($distributions as $distribution) {
                if ($distribution->prize && $distribution->prize->stock > 0) {
                    $prizes->push($distribution->prize);
                }
            }
            
            return view('wheel', [
                'entry' => $entry,
                'contest' => $contest,
                'prizes' => $prizes,
                'distributions' => $distributions,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
    
    /**
     * Traite le résultat de la roue
     */
    public function processWheelResult(Request $request)
    {
        $request->validate([
            'entry_id' => 'required|exists:entries,id',
            'prize_id' => 'nullable|exists:prizes,id'
        ]);
        
        $entry = Entry::findOrFail($request->entry_id);
        
        // Marquer comme joué
        $entry->played_at = now();
        
        // Enregistrer le résultat
        if ($request->prize_id) {
            $entry->prize_id = $request->prize_id;
        }
        
        $entry->save();
        
        return redirect()->route('result.show', ['entry' => $entry->id]);
    }
    
    /**
     * Affiche le résultat
     */
    public function showResult($entry)
    {
        $entry = Entry::with(['participant', 'prize'])->findOrFail($entry);
        
        return view('result', ['entry' => $entry]);
    }
    
    /**
     * Traite la requête AJAX pour faire tourner la roue et déterminer un résultat
     */
    public function spinWheel(Request $request)
    {
        \Log::info('Requête spinWheel reçue', $request->all());
        
        // Validation
        $validated = $request->validate([
            'entry_id' => 'required|exists:entries,id'
        ]);
        
        try {
            $entry = Entry::findOrFail($validated['entry_id']);
            
            // Vérifier si la roue a déjà été tournée pour cette entrée
            if ($entry->prize_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette roue a déjà été tournée. Vous ne pouvez pas jouer à nouveau.'
                ]);
            }
            
            // Vérifier si l'entrée a déjà été jouée
            if ($entry->result !== 'en attente') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette participation a déjà été jouée.'
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
                
            // Préparer les secteurs gagnants et perdants
            $winningSectors = [];
            foreach ($distributions as $distribution) {
                if ($distribution->prize && $distribution->prize->stock > 0) {
                    $winningSectors[] = [
                        'id' => $distribution->prize->id,
                        'name' => $distribution->prize->name,
                        'distribution_id' => $distribution->id,
                        'probability' => $distribution->remaining / $distributions->sum('remaining'),
                        'is_winning' => true
                    ];
                }
            }
            
            // Limiter à 5 secteurs gagnants
            if (count($winningSectors) > 5) {
                $winningSectors = array_slice($winningSectors, 0, 5);
            }
            
            // Si moins de 5 secteurs gagnants, on duplique
            if (count($winningSectors) < 5 && count($winningSectors) > 0) {
                $existingCount = count($winningSectors);
                for ($i = count($winningSectors); $i < 5; $i++) {
                    $index = $i % $existingCount;
                    $winningSectors[] = $winningSectors[$index];
                }
            }
            
            // Créer autant de secteurs perdants que de secteurs gagnants
            $losingSectors = [];
            $winningCount = count($winningSectors);
            for ($i = 0; $i < $winningCount; $i++) {
                $losingSectors[] = [
                    'id' => null,
                    'name' => 'Pas de chance',
                    'distribution_id' => null,
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
            
            // Générer un nombre aléatoire
            $rand = mt_rand(0, 100) / 100;
            
            // Sélectionner un secteur selon la probabilité
            $selectedSector = null;
            $cumulativeProbability = 0;
            
            foreach ($allSectors as $sector) {
                $cumulativeProbability += $sector['probability'];
                if ($rand <= $cumulativeProbability) {
                    $selectedSector = $sector;
                    break;
                }
            }
            
            // Si aucun secteur n'a été sélectionné, choisir le dernier
            if (!$selectedSector) {
                $selectedSector = end($allSectors);
            }
            
            // Mettre à jour l'entrée dans la base de données
            DB::beginTransaction();
            
            try {
                // Enregistrer le résultat
                $entry->result = $selectedSector['is_winning'] ? 'win' : 'lose';
                $entry->prize_id = $selectedSector['id'];
                $entry->played_at = now();
                $entry->won_date = $selectedSector['is_winning'] ? now() : null;
                $entry->save();
                
                // Si c'est un gain, mettre à jour la distribution et le stock
                if ($selectedSector['is_winning'] && $selectedSector['distribution_id']) {
                    // Mettre à jour la distribution
                    $distribution = PrizeDistribution::find($selectedSector['distribution_id']);
                    if ($distribution) {
                        $distribution->remaining = $distribution->remaining - 1;
                        $distribution->save();
                        
                        // Mettre à jour le stock du prix
                        $prize = Prize::find($selectedSector['id']);
                        if ($prize) {
                            $prize->stock = $prize->stock - 1;
                            $prize->save();
                        }
                    }
                    
                    // Générer un code QR texte avec la dénomination du lot
                    $prize = Prize::find($selectedSector['id']);
                    $prizeName = $prize ? $prize->name : 'Prix inconnu';
                    $qrCode = 'DINOR-' . $entry->id . '-' . $prizeName . '-' . Str::random(8);
                    
                    // Enregistrer le QR code
                    \App\Models\QrCode::create([
                        'entry_id' => $entry->id,
                        'code' => $qrCode,
                        'scanned' => false
                    ]);
                    
                    $entry->qr_code = $qrCode;
                    $entry->save();
                } 
                else {
                    // En cas de perte, définir un cookie pour limiter les tentatives
                    cookie()->queue('played_fortune_wheel', '1', 10080); // 10080 minutes = 7 jours
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'is_winning' => $selectedSector['is_winning'],
                    'result' => $entry->result,
                    'message' => $selectedSector['is_winning'] 
                        ? 'Félicitations ! Vous avez gagné : ' . ($selectedSector['name'] ?? 'un prix')
                        : 'Pas de chance cette fois-ci. Merci d\'avoir participé !'
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage()
            ], 500);
        }
    }
}
