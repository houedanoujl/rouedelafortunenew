<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Prize;
use App\Models\PrizeDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AvailablePrizesController extends Controller
{
    /**
     * Récupère les lots disponibles pour un concours donné
     */
    public function available(Request $request)
    {
        $contestId = $request->query('contest_id');
        
        // Utiliser le concours actif si aucun n'est spécifié
        if (!$contestId) {
            $contest = Contest::active()->first();
            if ($contest) {
                $contestId = $contest->id;
            }
        } else {
            $contest = Contest::find($contestId);
        }
        
        if (!$contest) {
            return response()->json([
                'error' => 'Aucun concours actif trouvé',
                'total' => 0,
                'prizes' => []
            ], 404);
        }
        
        // Récupérer toutes les distributions de prix pour ce concours
        $distributions = PrizeDistribution::where('contest_id', $contestId)
            ->where('stock', '>', 0)
            ->get();

        $prizes = [];
        
        foreach ($distributions as $distribution) {
            $prize = Prize::find($distribution->prize_id);
            if ($prize) {
                $prizes[] = [
                    'id' => $prize->id,
                    'name' => $prize->name,
                    'description' => $prize->description,
                    'image_url' => $prize->image_url,
                    'stock_disponible' => $distribution->stock,
                    'probability' => $distribution->probability,
                    'distribution_id' => $distribution->id
                ];
            }
        }
        
        // Enregistrer dans les logs
        Log::info("API: Consultation des lots disponibles", [
            'contest_id' => $contestId,
            'total_prizes' => count($prizes),
            'ip' => $request->ip()
        ]);
        
        return response()->json([
            'total' => count($prizes),
            'contest_id' => $contestId,
            'contest_name' => $contest->name,
            'prizes' => $prizes
        ]);
    }
}
