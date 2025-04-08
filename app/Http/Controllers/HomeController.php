<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
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
    
    /**
     * Affiche la page d'accueil
     */
    public function index(Request $request)
    {
        $activeContest = Contest::where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->first();
          
        // Gérer le cas où l'utilisateur a déjà participé (détecté via localStorage)
        $alreadyPlayed = $request->query('already_played') === 'true';
        $contestId = $request->query('contest_id');
        
        // Prioriser la détection via LocalStorage (paramètres d'URL)
        if ($alreadyPlayed && $contestId) {
            // Récupérer le concours concerné
            $playedContest = $contestId ? Contest::find($contestId) : null;
            
            // Afficher la page indiquant que l'utilisateur a déjà participé
            return view('already-played', [
                'message' => 'Vous avez déjà participé à ce concours.',
                'contest_name' => $playedContest ? $playedContest->name : 'ce concours',
                'contest_end_date' => $playedContest && $playedContest->end_date ? 
                    (new \DateTime($playedContest->end_date))->format('d/m/Y') : null
            ]);
        }
        
        // Fallback pour la vérification des cookies (approche précédente)
        $hasPlayedThisWeek = $request->cookie('played_this_week') ? true : false;
        $daysRemaining = $hasPlayedThisWeek ? $this->getDaysRemaining($request->cookie('played_this_week')) : 0;
        
        return view('home', [
            'contest' => $activeContest,
            'hasPlayedThisWeek' => $hasPlayedThisWeek,
            'daysRemaining' => $daysRemaining
        ]);
    }
    
    /**
     * Affiche la page d'inscription
     */
    public function register($contestId = null)
    {
        if (!$contestId) {
            $contest = Contest::where('status', 'active')
                ->orderBy('start_date', 'desc')
                ->first();
                
            if ($contest) {
                $contestId = $contest->id;
            }
        }
        
        return view('register', [
            'contestId' => $contestId
        ]);
    }
    
    /**
     * Affiche la page de jeu
     */
    public function play($participantId, $contestId)
    {
        return view('play', [
            'participantId' => $participantId,
            'contestId' => $contestId
        ]);
    }
    
    /**
     * Affiche la page de résultat
     */
    public function result($entryId)
    {
        return view('result', [
            'entryId' => $entryId
        ]);
    }
    
    /**
     * Affiche la page de règlement
     */
    public function rules()
    {
        return view('rules');
    }
}
