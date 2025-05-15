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
    public $contestName = null; // Nom du concours actif
    public $registered = false;
    public $participantId = null;
    public $limitedUntil = null;
    public $isBlocked = false;
    public $existingEntry = null; // Pour stocker une participation existante
    public $alreadyParticipated = false; // Flag pour indiquer si l'utilisateur a déjà participé
    public $consentement = false; // Consentement pour le traitement des données personnelles
    public $reglement = false; // Acceptation du règlement du jeu
    public $modalContents = []; // Contenu des modales chargé depuis JSON
    public $isExistingParticipant = false; // Flag pour indiquer si c'est un participant déjà enregistré
    public $previousContestsCount = 0; // Nombre de concours auxquels le participant a déjà participé

    protected $rules = [
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'phone' => 'required|string|max:50', // Retirer la contrainte 'unique' pour permettre les participants existants
        'email' => 'nullable|email|max:255',
        'consentement' => 'required|accepted',
        'reglement' => 'required|accepted',
    ];

    // Règles de validation spécifiques pour les nouveaux participants
    protected function getNewParticipantRules()
    {
        // Règles allégées pour les comptes test
        if (session('is_test_account')) {
            return [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'phone' => 'required|string|max:50', // Pas de vérification d'unicité pour les tests
                'email' => 'nullable|max:255', // Pas de validation d'email pour les tests
                'consentement' => 'required|accepted',
                'reglement' => 'required|accepted',
            ];
        }

        // Règles standard pour les utilisateurs normaux
        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|string|max:50|unique:participants,phone',
            'email' => 'nullable|email|max:255',
            'consentement' => 'required|accepted',
            'reglement' => 'required|accepted',
        ];
    }

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

    /**
     * Ignorer certaines erreurs de validation pour les participants existants
     */
    public function getErrorBag()
    {
        $errorBag = parent::getErrorBag();

        // Si c'est un participant existant, ne pas afficher l'erreur d'unicité du téléphone
        if ($this->isExistingParticipant && $errorBag->has('phone')) {
            $phoneErrors = $errorBag->get('phone');
            $filteredErrors = array_filter($phoneErrors, function($error) {
                return strpos($error, 'déjà enregistré') === false;
            });

            if (empty($filteredErrors)) {
                $errorBag->forget('phone');
            }
        }

        return $errorBag;
    }

    public function mount($contestId = null)
    {
        // Récupérer le concours actif
        $activeContest = Contest::where('status', 'active')->first();
        $this->contestId = $contestId ?? $activeContest?->id;

        // Récupérer le nom du concours
        if ($this->contestId) {
            $contest = $activeContest && $activeContest->id == $this->contestId
                ? $activeContest
                : Contest::find($this->contestId);
            $this->contestName = $contest?->name;
        }

        // Charger le contenu des modales depuis le fichier JSON
        $jsonPath = resource_path('data/modals.json');
        if (file_exists($jsonPath)) {
            $this->modalContents = json_decode(file_get_contents($jsonPath), true);
        }

        // Vérification multi-couches pour détecter participation antérieure
        if ($this->contestId) {
            $this->enforceOneParticipationLimit();
        }
    }

    public function updated($propertyName)
    {
        // Désactiver la validation en temps réel pour les comptes test
        if (!session('is_test_account')) {
            $this->validateOnly($propertyName);
        }

        // Vérifier si l'utilisateur a déjà participé lorsqu'il entre son téléphone ou son email
        // Ignorer cette vérification pour les comptes test
        if (($propertyName === 'phone' || $propertyName === 'email') && !session('is_test_account')) {
            // Transmettre un indicateur pour que le JS sache ne pas réafficher le popup d'âge
            $this->dispatch('updating-participation-check');
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

                // Définir des cookies et marqueurs pour renforcer la limitation
                $this->storeParticipationLimitation($this->contestId);

                // Alerter l'utilisateur qu'il a déjà participé
                session()->flash('error', 'Vous avez déjà participé à ce concours. Une seule participation par semaine au concours est autorisée.');
            }
        }
    }

    /**
     * Vérifie si l'utilisateur a déjà participé en utilisant plusieurs méthodes de détection
     * Vérifie uniquement pour le concours actif, pas les anciens concours
     */
    public function enforceOneParticipationLimit()
    {
        // Ignorer complètement la vérification pour les comptes de test
        if (session('is_test_account')) {
            \Log::info('Mode test: vérification de participation ignorée', [
                'contest_id' => $this->contestId
            ]);
            return;
        }

        // Ignorer la vérification si aucun concours n'est sélectionné
        if (!$this->contestId) {
            return;
        }

        // Vérifier si le concours est actif
        $contest = Contest::find($this->contestId);
        if (!$contest || $contest->status !== 'active') {
            // Si le concours n'est pas actif, permettre la participation
            return;
        }

        // 1. Vérification par téléphone ou email pour le concours actuel uniquement
        $participantCheck = false;
        if ($this->phone) {
            $participant = Participant::where('phone', $this->phone)->first();
            if ($participant) {
                $participantCheck = Entry::where('participant_id', $participant->id)
                    ->where('contest_id', $this->contestId)
                    ->exists();
            }
        }

        // 2. Vérification par cookie spécifique au concours
        $cookieName = 'contest_played_' . $this->contestId;
        $hasCookie = request()->cookie($cookieName) !== null;

        // 3. Vérification par session
        $sessionKey = 'contest_played_' . $this->contestId;
        $hasSession = session()->has($sessionKey);

        // 4. Vérification par localStorage (détecté côté client et renvoyé via URL si présent)
        $localStorageDetection = request()->query('already_played') === 'true' && request()->query('contest_id') == $this->contestId;

        // 5. Vérification par IP pour ce concours spécifique
        $ipAddress = request()->ip();
        $ipCheck = Entry::where('contest_id', $this->contestId)
            ->where('ip_address', $ipAddress)
            ->exists();

        // Si l'une des vérifications détecte une participation antérieure à ce concours spécifique
        if ($participantCheck || $hasCookie || $hasSession || $localStorageDetection || $ipCheck) {
            $this->alreadyParticipated = true;

            // Journaliser la tentative
            \Log::info('Tentative de participation multiple détectée', [
                'contest_id' => $this->contestId,
                'ip' => $ipAddress,
                'detection_method' => [
                    'cookie' => $hasCookie,
                    'session' => $hasSession,
                    'local_storage' => $localStorageDetection,
                    'ip_check' => $ipCheck
                ]
            ]);

            // Rediriger vers la page d'accueil avec un message
            $contest = Contest::find($this->contestId);
            $params = [
                'already_played' => 'true',
                'contest_id' => $this->contestId
            ];

            session()->flash('error', 'Vous avez déjà participé au concours "' . ($contest ? $contest->name : 'ce concours') . '". Une seule participation par concours est autorisée.');

            // Utiliser un script JavaScript pour rediriger
            $this->dispatch('redirect-already-played', [
                'url' => route('home', $params)
            ]);
        }
    }

    /**
     * Stocke des marqueurs pour renforcer la limitation de participation
     */
    protected function storeParticipationLimitation($contestId)
    {
        // Ne pas stocker de limitations pour les comptes de test
        if (session('is_test_account')) {
            \Log::info('Mode test: Aucun marqueur de limitation de participation créé', [
                'contest_id' => $contestId
            ]);
            return;
        }

        $cookieName = 'contest_played_' . $contestId;
        $sessionKey = 'contest_played_' . $contestId;

        // Stocker en session
        session()->put($sessionKey, 'played');

        // Stocker en localStorage via JavaScript
        $this->dispatch('store-participation', [
            'key' => $cookieName,
            'value' => 'played',
            'contestId' => $contestId
        ]);
    }

    public function register()
    {
        // Vérifier à nouveau si l'utilisateur a déjà participé (double vérification)
        $this->enforceOneParticipationLimit();

        // Si l'utilisateur a déjà participé au concours actif, bloquer l'inscription
        // Sauf si c'est un compte de test
        if ($this->alreadyParticipated && !session('is_test_account')) {
            session()->flash('error', 'Vous avez déjà participé à ce concours. Une seule participation par semaine au concours est autorisée.');
            return;
        }

        // Vérifier explicitement si c'est un participant existant
        if (!session('is_test_account')) {
            $participant = Participant::where('phone', $this->phone)->first();
            $this->isExistingParticipant = $participant ? true : false;
        } else {
            // Pour les comptes test, on ignore cette vérification
            $this->isExistingParticipant = false;
        }

        // Validation différente selon le type de participant
        if (session('is_test_account')) {
            // Pour les comptes test, validation minimale sans vérification d'unicité ni format d'email
            $this->validate([
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'phone' => 'required|string|max:50', // Pas de vérification d'unicité
                'email' => 'nullable|max:255', // Pas de validation du format email
                'consentement' => 'required|accepted',
                'reglement' => 'required|accepted',
            ]);
            \Log::info('Mode test: validation allégée des données utilisateur', [
                'email' => $this->email,
                'phone' => $this->phone
            ]);
        } else if ($this->isExistingParticipant) {
            // Pour les participants existants, uniquement valider les champs requis sans vérifier l'unicité
            $this->validate([
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'phone' => 'required|string|max:50',
                'consentement' => 'required|accepted',
                'reglement' => 'required|accepted',
            ]);
        } else {
            // Pour les nouveaux participants, valider avec unicité du téléphone
            $this->validate($this->getNewParticipantRules());
        }

        try {
            // Vérifier si un participant existe déjà avec ce numéro de téléphone ou email
            // Ignorer cette vérification pour les comptes test
            if (session('is_test_account')) {
                // Pour les comptes test, on crée toujours un nouveau participant ou on utilise un existant sans vérification
                $participant = $participantByPhone ?? $participantByEmail ?? null;
            } else {
                $participantByPhone = Participant::where('phone', $this->phone)->first();
                $participantByEmail = !empty($this->email) ? Participant::where('email', $this->email)->first() : null;
                $participant = $participantByPhone ?? $participantByEmail;
            }

            // Vérifier immédiatement si le participant existe déjà et a participé à ce concours
            if ($participant) {
                // Marquer comme participant existant
                $this->isExistingParticipant = true;

                // Compter les concours précédents auxquels ce participant a participé
                $this->previousContestsCount = Entry::where('participant_id', $participant->id)
                    ->distinct('contest_id')
                    ->count('contest_id');

                // Vérifier si le participant a déjà gagné à un autre concours
                $hasWonPreviously = Entry::where('participant_id', $participant->id)
                    ->where('has_won', true)
                    ->exists();

                if ($hasWonPreviously && !session('is_test_account')) {
                    \Log::info('Participant déjà gagnant à un précédent concours détecté pendant l\'inscription', [
                        'participant_id' => $participant->id,
                        'phone' => $participant->phone,
                        'email' => $participant->email
                    ]);

                    // Nous laissons le participant s'inscrire, mais un message d'information est ajouté
                    // La vérification dans calculateWinChance() empêchera de toute façon un nouveau gain
                    session()->flash('warning', 'Vous avez déjà gagné à un de nos concours précédents. Un participant ne peut gagner qu\'une seule fois, tous concours confondus.');
                }

                $existingEntry = Entry::where('participant_id', $participant->id)
                    ->where('contest_id', $this->contestId)
                    ->first();

                if ($existingEntry && !session('is_test_account')) {
                    $this->existingEntry = $existingEntry;
                    $this->alreadyParticipated = true;
                    // Stocker dans cookie, session et localStorage pour renforcer la limitation
                    $this->storeParticipationLimitation($this->contestId);
                    session()->flash('info', 'Vous avez déjà participé à ce concours. Une seule participation par semaine au concours est autorisée.');
                    return redirect()->route('wheel.show', ['entry' => $existingEntry->id]);
                } else if ($existingEntry && session('is_test_account')) {
                    // Pour un compte test avec participation existante, on log mais on laisse continuer
                    \Log::info('Mode test: Participation existante ignorée pour permettre des tests multiples', [
                        'participant_id' => $participant->id,
                        'contest_id' => $this->contestId,
                        'email' => $participant->email ?? 'non défini'
                    ]);
                }

                // C'est un participant existant mais il n'a pas encore participé à ce concours
                // ou c'est un compte test autorisé à rejouer
                session()->flash('success', 'Bienvenue à nouveau ' . $participant->first_name . ' ! Vous pouvez maintenant participer à ce nouveau concours.');

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
            // Ignorer cette vérification pour les comptes test
            $lastSecondCheck = null;
            if (!session('is_test_account')) {
                $lastSecondCheck = Entry::where('participant_id', $participant->id)
                    ->where('contest_id', $this->contestId)
                    ->first();
            }

            if ($lastSecondCheck && !session('is_test_account')) {
                $this->existingEntry = $lastSecondCheck;
                $this->alreadyParticipated = true;
                // Stocker dans cookie, session et localStorage pour renforcer la limitation
                $this->storeParticipationLimitation($this->contestId);
                session()->flash('info', 'Une participation a déjà été enregistrée pour ce concours.');
                return redirect()->route('wheel.show', ['entry' => $lastSecondCheck->id]);
            }

            // Créer une nouvelle participation avec l'adresse IP et l'User-Agent
            $entry = Entry::create([
                'participant_id' => $participant->id,
                'contest_id' => $this->contestId,
                'qr_code' => 'QR-' . Str::random(8),
                'played_at' => now(),
                'result' => 'en attente',
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->header('User-Agent'), 0, 500)
            ]);

            // Stocker les marqueurs de participation pour ce concours
            // Sauf pour les comptes de test
            if (!session('is_test_account')) {
                $this->storeParticipationLimitation($this->contestId);
            } else {
                \Log::info('Mode test: Limitation de participation non appliquée', [
                    'participant_id' => $participant->id,
                    'contest_id' => $this->contestId
                ]);
            }

            // Rediriger vers la roue de la fortune
            return redirect()->route('wheel.show', ['entry' => $entry->id]);

        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de l\'inscription: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Ajouter un script JavaScript pour stocker l'information de participation dans localStorage
        // et vérifier si l'utilisateur a déjà participé
        $this->dispatch('setup-participation-check', [
            'contestId' => $this->contestId
        ]);

        return view('livewire.registration-form');
    }
}
