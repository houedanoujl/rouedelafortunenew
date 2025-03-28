<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\Contest;
use App\Models\Prize;
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
            'token' => Str::random(10)
        ]);
        
        return redirect()->route('wheel.show', ['entry' => $entry->id]);
    }
    
    /**
     * Affiche la roue de la fortune
     */
    public function showWheel($entry)
    {
        $entry = Entry::findOrFail($entry);
        $contest = $entry->contest;
        $prizes = Prize::whereHas('prizeDistributions', function($query) use ($contest) {
            $query->where('contest_id', $contest->id);
        })->get();
        
        return view('wheel', [
            'entry' => $entry,
            'contest' => $contest,
            'prizes' => $prizes
        ]);
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
        
        return view('result', ['entryId' => $entry->id]);
    }
}
