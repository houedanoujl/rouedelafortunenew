<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Vérifie si l'utilisateur a participé à un concours spécifique
     * 
     * @param Request $request
     * @param int $contestId
     * @return bool
     */
    private function hasParticipatedInContest(Request $request, $contestId)
    {
        // Vérifier via cookie
        $cookieName = 'contest_played_' . $contestId;
        
        // Vérifier via session
        $sessionKey = 'contest_played_' . $contestId;
        
        return $request->cookie($cookieName) !== null || \Session::has($sessionKey);
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
        
        // Vérifier si l'utilisateur a déjà participé au concours actif
        $hasParticipated = $activeContest ? $this->hasParticipatedInContest($request, $activeContest->id) : false;
        
        return view('home', [
            'contest' => $activeContest,
            'hasParticipated' => $hasParticipated,
            'contest_id' => $activeContest ? $activeContest->id : null,
            'contest_name' => $activeContest ? $activeContest->name : 'ce concours',
            'contest_end_date' => $activeContest && $activeContest->end_date ? 
                (new \DateTime($activeContest->end_date))->format('d/m/Y') : null
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
