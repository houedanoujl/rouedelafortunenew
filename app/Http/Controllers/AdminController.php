<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Affiche le tableau de bord administratif
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }
    
    /**
     * Affiche la liste des participants
     */
    public function participants()
    {
        $participants = Participant::withCount('entries')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.participants', [
            'participants' => $participants
        ]);
    }
    
    /**
     * Affiche la liste des participations
     */
    public function entries()
    {
        $entries = Entry::with(['participant', 'prize', 'contest'])
            ->orderBy('played_at', 'desc')
            ->paginate(20);
            
        return view('admin.entries', [
            'entries' => $entries
        ]);
    }
    
    /**
     * Affiche la page de gestion des prix
     */
    public function prizes()
    {
        return view('admin.prizes');
    }
    
    /**
     * Affiche la page de génération de code QR
     */
    public function qrCode($entryId)
    {
        return view('admin.qr-code', [
            'entryId' => $entryId
        ]);
    }
    
    /**
     * Vérifie un code QR
     */
    public function verifyQrCode(Request $request)
    {
        $code = $request->input('code');
        
        $qrCode = \App\Models\QrCode::where('code', $code)
            ->with(['entry.participant', 'entry.prize'])
            ->first();
            
        if (!$qrCode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code QR invalide'
            ]);
        }
        
        if ($qrCode->scanned) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce code QR a déjà été utilisé le ' . $qrCode->scanned_at->format('d/m/Y à H:i')
            ]);
        }
        
        // Marquer le code QR comme scanné
        $qrCode->scanned = true;
        $qrCode->scanned_at = now();
        $qrCode->scanned_by = Auth::id();
        $qrCode->save();
        
        // Mettre à jour le statut "réclamé" dans Entry
        $entry = $qrCode->entry;
        $entry->claimed = true;
        $entry->claimed_at = now();
        $entry->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Code QR valide',
            'data' => [
                'participant' => $qrCode->entry->participant->full_name,
                'prize' => $qrCode->entry->prize->name,
                'won_date' => $qrCode->entry->won_date->format('d/m/Y H:i'),
            ]
        ]);
    }
    
    /**
     * Affiche la page de scan de code QR
     */
    public function scanQrCode()
    {
        return view('admin.scan-qr-code');
    }
}
