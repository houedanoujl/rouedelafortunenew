<?php

namespace App\Livewire;

use App\Models\Contest;
use App\Models\Participant;
use App\Models\Entry;
use Illuminate\Support\Str;
use Livewire\Component;

class RegistrationForm extends Component
{
    public $firstName = '';
    public $lastName = '';
    public $phone = '';
    public $email = '';
    public $contestId = null;
    public $registered = false;
    public $participantId = null;
    public $limitedUntil = null;
    public $isBlocked = false;

    protected $rules = [
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'phone' => 'required|string|max:50|unique:participants,phone',
        'email' => 'nullable|email|max:255',
    ];

    protected $messages = [
        'firstName.required' => 'Le prénom est obligatoire.',
        'lastName.required' => 'Le nom est obligatoire.',
        'phone.required' => 'Le numéro de téléphone est obligatoire.',
        'phone.unique' => 'Ce numéro de téléphone est déjà enregistré.',
        'email.email' => 'Veuillez entrer une adresse email valide.',
    ];

    public function mount($contestId = null)
    {
        $this->contestId = $contestId ?? Contest::where('status', 'active')->first()?->id;
        
        // Vérifier si le joueur est limité par un cookie
        $playedCookie = request()->cookie('played_fortune_wheel');
        
        if ($playedCookie) {
            $this->isBlocked = true;
            
            // Calculer quand le joueur pourra rejouer (7 jours après la dernière défaite)
            $cookieDate = now()->subMinutes(1); // Par défaut, au cas où le cookie n'a pas de date
            
            // Date d'expiration du cookie
            $cookieExpire = $cookieDate->addDays(7);
            $this->limitedUntil = $cookieExpire->format('d/m/Y à H:i');
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function register()
    {
        $this->validate();

        try {
            // Vérifier si le participant existe déjà avec ce numéro de téléphone
            $participant = Participant::where('phone', $this->phone)->first();
            
            if (!$participant) {
                // Créer un nouveau participant
                $participant = Participant::create([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'phone' => $this->phone,
                    'email' => $this->email,
                ]);
            } else {
                // Mettre à jour les informations du participant existant
                $participant->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'email' => $this->email,
                ]);
            }
            
            $this->participantId = $participant->id;
            $this->registered = true;
            
            // Vérifier si le participant a déjà participé à ce concours
            $existingEntry = Entry::where('participant_id', $participant->id)
                ->where('contest_id', $this->contestId)
                ->first();
                
            if ($existingEntry) {
                return redirect()->route('wheel.show', ['entry' => $existingEntry->id]);
            }
            
            // Créer une nouvelle participation
            $entry = Entry::create([
                'participant_id' => $participant->id,
                'contest_id' => $this->contestId,
                'qr_code' => 'QR-' . Str::random(8),
                'played_at' => now(),
                'result' => 'en attente',
            ]);
            
            // Rediriger vers la roue de la fortune
            return redirect()->route('wheel.show', ['entry' => $entry->id]);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de l\'inscription: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.registration-form');
    }
}
