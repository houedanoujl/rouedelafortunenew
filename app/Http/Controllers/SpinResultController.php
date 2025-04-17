<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SpinResultController extends Controller
{
    /**
     * Enregistre le résultat réel affiché lors du spin dans la session
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
        
        // Enregistrer le résultat dans la session
        $sessionKey = 'spin_result_' . $validated['entry_id'];
        Session::put($sessionKey, $validated['displayed_result']);
        
        // Journaliser pour débogage
        Log::info('Résultat de spin enregistré en session', [
            'entry_id' => $validated['entry_id'],
            'displayed_result' => $validated['displayed_result'],
            'segment_text' => $validated['segment_text'],
            'session_key' => $sessionKey
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Résultat enregistré avec succès',
            'session_id' => Session::getId(),
            'session_key' => $sessionKey
        ]);
    }
}
