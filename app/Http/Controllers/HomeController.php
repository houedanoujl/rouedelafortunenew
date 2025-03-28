<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function index()
    {
        $activeContest = Contest::where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->first();
            
        return view('home', [
            'contest' => $activeContest
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
