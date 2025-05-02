<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SpinResultController extends Controller
{
    /**
     * Enregistre le résultat réel affiché lors du spin dans la session et met à jour la base de données
     * 
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordResult(Request $request)
    {
        $validated = $request->validate([
            'entry_id' => 'required|integer|exists:entries,id',
            'displayed_result' => 'required|string|in:win,lose',
            'segment_text' => 'nullable|string',
            'console_logs' => 'nullable|array',
            'target_angle' => 'nullable|numeric',
            'wheel_type' => 'nullable|string'
        ]);
        
        $entry = Entry::findOrFail($validated['entry_id']);
        
        // Charger la probabilité depuis le fichier de configuration
        $winProbability = 20; // Valeur par défaut
        $configFile = 'wheel_settings.json';
        
        if (Storage::exists($configFile)) {
            $content = Storage::get($configFile);
            $settings = json_decode($content, true);
            $winProbability = $settings['win_probability'] ?? 20;
        }
        
        // Déterminer le résultat selon la probabilité configurée
        $isWinningResult = mt_rand(1, 100) <= $winProbability;
        
        // Pour la roue no-stock, toujours perdant
        if ($request->input('wheel_type') === 'no-stock') {
            $isWinningResult = false;
        }
        
        // Mettre à jour l'entrée
        $entry->has_played = true;
        $entry->has_won = $isWinningResult;
        $entry->save();
        
        // Générer un QR code si nécessaire (seulement pour les gagnants)
        if ($isWinningResult && !$entry->qrCode) {
            $this->generateQrCodeForEntry($entry);
        }
        
        $sessionKey = 'spin_result_' . $validated['entry_id'];
        Session::put($sessionKey, $isWinningResult ? 'win' : 'lose');
        
        Log::info('Résultat de spin enregistré', [
            'entry_id' => $validated['entry_id'],
            'win_probability' => $winProbability,
            'result' => $isWinningResult ? 'win' : 'lose',
            'segment_text' => $validated['segment_text'],
            'wheel_type' => $request->input('wheel_type', 'main')
        ]);
        
        // Calcul de l'angle central du segment approprié
        $segments = [
            ['text' => 'PERDU'], ['text' => 'GAGNÉ'], ['text' => 'PERDU'], ['text' => 'GAGNÉ'], ['text' => 'PERDU'],
            ['text' => 'GAGNÉ'], ['text' => 'PERDU'], ['text' => 'GAGNÉ'], ['text' => 'PERDU'], ['text' => 'GAGNÉ']
        ];
        
        // Sélection des segments gagnants/perdants en fonction du résultat
        $targetIndexes = $isWinningResult ? [1,3,5,7,9] : [0,2,4,6,8];
        
        // Choix du segment (randomisation pour plus de réalisme)
        $chosenIndex = $targetIndexes[array_rand($targetIndexes)];
        $target_angle = ($chosenIndex * 36) + 18; // Centre du segment
        
        // Enregistrer les données dans l'historique
        $request->merge(['target_angle' => $target_angle]);
        $this->saveToSpinHistory($request, $entry, $isWinningResult);
        
        return response()->json([
            'success' => true,
            'message' => 'Résultat enregistré avec succès',
            'session_id' => Session::getId(),
            'session_key' => $sessionKey,
            'database_updated' => true,
            'is_winning' => $isWinningResult,
            'target_angle' => $target_angle
        ]);
    }
    
    /**
     * Génère un code QR pour une entrée gagnante
     * 
     * @param Entry $entry
     * @return void
     */
    private function generateQrCodeForEntry(Entry $entry)
    {
        // Vérifier si l'entrée existe et n'a pas déjà un code QR
        if (!$entry || $entry->qrCode) {
            return;
        }
        
        // Générer un code unique aléatoire
        $code = 'DNR' . rand(10, 99) . '-' . substr(md5(uniqid()), 0, 8);
        
        // Créer un nouveau QR Code
        $qrCode = new \App\Models\QrCode([
            'code' => $code,
            'status' => 'active'
        ]);
        
        // Associer le QR Code à l'entrée
        $entry->qrCode()->save($qrCode);
        
        Log::info('QR Code généré pour une entrée mise à jour', [
            'entry_id' => $entry->id,
            'qr_code' => $code
        ]);
    }
    
    private function saveToSpinHistory(Request $request, Entry $entry, $isWinningResult)
    {
        // Ne pas enregistrer si c'est la roue no-stock
        if ($request->input('wheel_type') === 'no-stock') {
            return;
        }
        
        // Chemin vers le fichier JSON d'historique
        $path = storage_path('app/spin_history.json');
        
        // Données à enregistrer
        $spinData = [
            'timestamp' => now()->toIso8601String(),
            'entry_id' => $entry->id,
            'participant' => [
                'id' => $entry->participant_id,
                'name' => $entry->participant ? $entry->participant->first_name . ' ' . $entry->participant->last_name : 'Inconnu',
                'email' => $entry->participant ? $entry->participant->email : null,
                'ip_address' => $entry->participant ? $entry->participant->ip_address : null
            ],
            'contest_id' => $entry->contest_id,
            'angle' => $request->input('target_angle'),
            'sector_id' => $request->input('sector_id', ''),
            'sector_class' => ($isWinningResult ? 'secteur-gagne' : 'secteur-perdu'),
            'result' => $isWinningResult ? 'win' : 'lose',
            'has_won_in_db' => $entry->has_won,
            'ip_address' => $request->ip(),
            'console_logs' => $request->input('console_logs', [])
        ];
        
        // Créer le fichier s'il n'existe pas
        if (!file_exists($path)) {
            file_put_contents($path, json_encode([$spinData], JSON_PRETTY_PRINT));
            return;
        }
        
        // Lire le contenu existant
        $content = file_get_contents($path);
        $history = json_decode($content, true);
        
        // S'assurer que l'historique est un tableau
        if (!is_array($history)) {
            $history = [];
        }
        
        // Ajouter les nouvelles données
        $history[] = $spinData;
        
        // Enregistrer le tout
        file_put_contents($path, json_encode($history, JSON_PRETTY_PRINT));
    }
}
