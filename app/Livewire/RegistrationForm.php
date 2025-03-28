<?php

namespace App\Livewire;

use App\Models\Contest;
use App\Models\Participant;
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
            
            // Rediriger vers la roue de la fortune
            if ($this->contestId) {
                return redirect()->route('play', [
                    'participantId' => $this->participantId,
                    'contestId' => $this->contestId
                ]);
            }
            
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
        }
    }

    public function render()
    {
        return view('livewire.registration-form');
    }
}
