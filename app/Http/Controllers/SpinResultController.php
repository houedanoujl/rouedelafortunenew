<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

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
            'segment_text' => 'required|string'
        ]);
        
        // Récupérer l'entrée à partir de l'ID
        $entry = Entry::findOrFail($validated['entry_id']);
        
        // Déterminer si l'utilisateur a gagné selon le segment affiché
        $isWinningResult = $validated['displayed_result'] === 'win';
        
        // Mettre à jour le résultat dans la base de données pour correspondre au résultat visuel
        $entry->has_played = true;
        $entry->has_won = $isWinningResult;
        $entry->save();
        
        // Si l'utilisateur a gagné visuellement mais que la valeur précédente était perdante,
        // il faut générer un code QR pour le prix
        if ($isWinningResult && !$entry->qrCode) {
            // Générer un nouveau code QR et l'associer à cette entrée
            $this->generateQrCodeForEntry($entry);
        }
        
        // Enregistrer le résultat dans la session
        $sessionKey = 'spin_result_' . $validated['entry_id'];
        Session::put($sessionKey, $validated['displayed_result']);
        
        // Journaliser pour débogage
        Log::info('Résultat de spin enregistré en session et base de données', [
            'entry_id' => $validated['entry_id'],
            'displayed_result' => $validated['displayed_result'],
            'segment_text' => $validated['segment_text'],
            'session_key' => $sessionKey,
            'updated_db' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Résultat enregistré avec succès',
            'session_id' => Session::getId(),
            'session_key' => $sessionKey,
            'database_updated' => true,
            'is_winning' => $isWinningResult
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
}
