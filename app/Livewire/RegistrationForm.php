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
    public $existingEntry = null; // Pour stocker une participation existante
    public $alreadyParticipated = false; // Flag pour indiquer si l'utilisateur a déjà participé
    public $consentement = false; // Consentement pour le traitement des données personnelles
    public $reglement = false; // Acceptation du règlement du jeu
    public $modalContents = []; // Contenu des modales chargé depuis JSON

    protected $rules = [
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'phone' => 'required|string|max:50|unique:participants,phone',
        'email' => 'nullable|email|max:255',
        'consentement' => 'required|accepted',
        'reglement' => 'required|accepted',
    ];

    protected $messages = [
        'firstName.required' => 'Le prénom est obligatoire.',
        'lastName.required' => 'Le nom est obligatoire.',
        'phone.required' => 'Le numéro de téléphone est obligatoire.',
        'phone.unique' => 'Ce numéro de téléphone est déjà enregistré.',
        'email.email' => 'Veuillez entrer une adresse email valide.',
        'consentement.required' => 'Vous devez accepter le traitement de vos données personnelles.',
        'consentement.accepted' => 'Vous devez accepter le traitement de vos données personnelles.',
        'reglement.required' => 'Vous devez accepter le règlement du jeu.',
        'reglement.accepted' => 'Vous devez accepter le règlement du jeu.',
    ];

    public function mount($contestId = null)
    {
        $this->contestId = $contestId ?? Contest::where('status', 'active')->first()?->id;
        
        // Charger le contenu des modales depuis le fichier JSON
        $jsonPath = resource_path('data/modals.json');
        if (file_exists($jsonPath)) {
            $this->modalContents = json_decode(file_get_contents($jsonPath), true);
        }
        
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
        
        // Vérifier si l'utilisateur a déjà participé lorsqu'il entre son téléphone ou son email
        if ($propertyName === 'phone' || $propertyName === 'email') {
            $this->checkExistingParticipation();
        }
    }
    
    /**
     * Vérifie si l'utilisateur a déjà participé au concours actuel
     */
    public function checkExistingParticipation()
    {
        // Ne rien faire si le téléphone ou l'email sont vides
        if (empty($this->phone) && empty($this->email)) {
            return;
        }
        
        // Rechercher le participant par téléphone ou email
        $participant = null;
        
        if (!empty($this->phone)) {
            $participant = Participant::where('phone', $this->phone)->first();
        }
        
        if (!$participant && !empty($this->email)) {
            $participant = Participant::where('email', $this->email)->first();
        }
        
        // Si on trouve un participant, vérifier s'il a déjà une participation
        if ($participant) {
            $existingEntry = Entry::where('participant_id', $participant->id)
                ->where('contest_id', $this->contestId)
                ->first();
                
            if ($existingEntry) {
                $this->existingEntry = $existingEntry;
                $this->alreadyParticipated = true;
                
                // Préremplir les champs avec les données du participant
                $this->firstName = $participant->first_name;
                $this->lastName = $participant->last_name;
                $this->phone = $participant->phone;
                $this->email = $participant->email;
            }
        }
    }

    public function register()
    {
        $this->validate();

        try {
            // Vérifier si un participant existe déjà avec ce numéro de téléphone ou email
            $participantByPhone = Participant::where('phone', $this->phone)->first();
            $participantByEmail = !empty($this->email) ? Participant::where('email', $this->email)->first() : null;
            $participant = $participantByPhone ?? $participantByEmail;
            
            // Vérifier immédiatement si le participant existe déjà et a participé à ce concours
            if ($participant) {
                $existingEntry = Entry::where('participant_id', $participant->id)
                    ->where('contest_id', $this->contestId)
                    ->first();
                    
                if ($existingEntry) {
                    $this->existingEntry = $existingEntry;
                    $this->alreadyParticipated = true;
                    session()->flash('info', 'Vous avez déjà participé à ce concours. Une seule participation par concours est autorisée.');
                    return redirect()->route('wheel.show', ['entry' => $existingEntry->id]);
                }
                
                // Mettre à jour les informations du participant existant
                $participant->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'phone' => $this->phone,
                    'email' => $this->email,
                ]);
            } else {
                // Créer un nouveau participant
                $participant = Participant::create([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'phone' => $this->phone,
                    'email' => $this->email,
                ]);
            }
            
            $this->participantId = $participant->id;
            $this->registered = true;
            
            // Double vérification - si un autre appareil/navigateur a créé une participation entre-temps
            $lastSecondCheck = Entry::where('participant_id', $participant->id)
                ->where('contest_id', $this->contestId)
                ->first();
                
            if ($lastSecondCheck) {
                $this->existingEntry = $lastSecondCheck;
                $this->alreadyParticipated = true;
                session()->flash('info', 'Une participation a déjà été enregistrée pour ce concours.');
                return redirect()->route('wheel.show', ['entry' => $lastSecondCheck->id]);
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
