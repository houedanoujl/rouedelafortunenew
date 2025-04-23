<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\Contest;
use App\Models\Prize;
use App\Models\PrizeDistribution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\WhatsAppService;
use App\Services\GreenWhatsAppService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class ParticipantController extends Controller
{
    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegistrationForm(Request $request)
    {
        // Récupérer le concours actif
        $activeContest = Contest::where('status', 'active')->first();

        if (!$activeContest) {
            return view('no-contest');
        }

        // Vérifier si l'utilisateur a déjà participé à ce concours en utilisant les cookies et/ou la session
        $cookieName = 'contest_played_' . $activeContest->id;
        $hasPlayed = $request->cookie($cookieName) !== null || \Session::has($cookieName);

        // Pour les utilisateurs en mode test, ignorer la vérification de participation antérieure
        if (session('is_test_account')) {
            \Log::info('Mode test détecté : formulaire d\'inscription toujours affiché');
            $hasPlayed = false; // Forcer l'affichage du formulaire pour les comptes test
        } else {
            // Vérifier aussi via localStorage en injectant du script JavaScript
            $localStorageKey = 'contest_played_' . $activeContest->id;

            // Vérifier aussi si une entrée existe déjà pour l'utilisateur dans ce concours
            // (vérification spécifique au concours actif uniquement)
            $ipAddress = $request->ip();
            $userAgent = $request->header('User-Agent');
            $userIdentifier = $request->cookie('user_identifier');

            // Si un identifiant utilisateur existe, vérifier directement dans la base de données
            $existingEntry = null;
            if ($userIdentifier) {
                $existingEntry = Entry::where('participant_id', $userIdentifier)
                                     ->where('contest_id', $activeContest->id)
                                     ->first();
                if ($existingEntry) {
                    $hasPlayed = true;
                }
            }
        }

        if ($hasPlayed) {
            // S'assurer que le cookie est défini pour renforcer la limitation
            // Même si l'utilisateur est détecté par session ou BD, ajouter le cookie pour renforcer
            if (!$request->cookie($cookieName) && !session('is_test_account')) {
                $cookieExpiry = 60 * 24 * 30; // 30 jours en minutes par défaut

                // Si le concours a une date de fin, utiliser cette date pour l'expiration
                if ($activeContest->end_date) {
                    $contestEndDate = new \DateTime($activeContest->end_date);
                    $now = new \DateTime();
                    $minutesUntilEnd = max(1, round(($contestEndDate->getTimestamp() - $now->getTimestamp()) / 60));
                    $cookieExpiry = $minutesUntilEnd;
                }

                // Ne pas créer de cookie pour les utilisateurs en mode test
                if (!session('is_test_account')) {
                    \Cookie::queue(cookie(
                        $cookieName,         // nom du cookie
                        'played',            // valeur
                        $cookieExpiry,       // durée de vie en minutes
                        '/',                 // chemin
                        null,                // domaine (null = domaine actuel)
                        false,               // secure (https uniquement)
                        false,               // httpOnly (non accessible par JavaScript)
                        false,               // raw
                        'lax'                // sameSite
                    ));
                    
                    // Journaliser la création du cookie (pour débogage)
                    \Log::info("Cookie {$cookieName} créé pour un utilisateur normal", [
                        'cookie_name' => $cookieName,
                        'expiry_minutes' => $cookieExpiry
                    ]);
                } else {
                    \Log::info("Création du cookie {$cookieName} ÉVITÉE pour un utilisateur en mode test");
                }
            }

            // Stocker aussi en session comme sauvegarde
            \Session::put($cookieName, 'played');
            \Session::save();

            // Récupérer l'entrée existante de l'utilisateur par IP ou cookie si on ne l'a pas encore
            if (!$existingEntry) {
                // Essayer de trouver par IP
                $existingEntry = Entry::where('contest_id', $activeContest->id)
                    ->where(function($query) use ($ipAddress, $userAgent) {
                        $query->where('ip_address', $ipAddress)
                            ->orWhere('user_agent', $userAgent);
                    })
                    ->first();
            }

            return view('already-played', [
                'message' => 'Vous avez déjà participé à ce concours.',
                'contest_name' => $activeContest->name,
                'contest_end_date' => $activeContest->end_date ? (new \DateTime($activeContest->end_date))->format('d/m/Y') : null,
                'localStorageKey' => $localStorageKey, // Passer la clé à la vue pour le script JS
                'existing_entry' => $existingEntry // Passer l'entrée existante à la vue
            ]);
        }

        $activeContest = Contest::where('status', 'active')->first();

        if (!$activeContest) {
            return view('no-contest');
        }

        return view('register', ['contestId' => $activeContest->id]);
    }

    /**
     * Traite l'inscription d'un participant
     */
    /**
     * Vérifie si l'utilisateur a déjà participé à un concours spécifique
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

        // Vérifier aussi si une entrée existe déjà pour l'utilisateur dans ce concours
        $ipAddress = $request->ip();
        $userIdentifier = $request->cookie('user_identifier');

        // Si un identifiant utilisateur existe, vérifier directement dans la base de données
        $existingEntry = false;
        if ($userIdentifier) {
            $existingEntry = Entry::where('participant_id', $userIdentifier)
                ->where('contest_id', $contestId)
                ->exists();
        }

        return $request->cookie($cookieName) !== null || \Session::has($sessionKey) || $existingEntry;
    }

    public function register(Request $request)
    {
        // Récupérer le concours actif
        $contest = Contest::find($request->contestId) ?: Contest::where('status', 'active')->first();

        if (!$contest) {
            return redirect()->route('home')->with('error', 'Aucun concours actif trouvé.');
        }

        // Vérifier si l'utilisateur a déjà participé à ce concours (3 méthodes de vérification)

        // 1. Cookie spécifique au concours
        $cookieName = 'contest_played_' . $contest->id;

        // 2. Session spécifique au concours
        $sessionKey = 'contest_played_' . $contest->id;

        // 3. LocalStorage (vérifié dans JavaScript côté client)

        // Si détecté par cookie ou session, empêcher la participation
        if (($request->cookie($cookieName) !== null || \Session::has($sessionKey)) && !session('is_test_account')) {
            \Log::info('Participation bloquée - Déjà joué au concours ' . $contest->id, [
                'detection_method' => $request->cookie($cookieName) ? 'cookie' : 'session',
                'ip' => $request->ip(),
                'user_agent' => substr($request->header('User-Agent'), 0, 100),
            ]);

            return redirect()->route('home')->with([
                'error' => 'Vous avez déjà participé au concours "' . $contest->name . '". Une seule participation par concours est autorisée.',
                'contest_name' => $contest->name
            ]);
        }

        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'contestId' => 'required|exists:contests,id'
        ]);

        // Vérifier si le participant existe déjà par téléphone
        $participant = Participant::where('phone', $request->phone)->first();

        // Si non trouvé par téléphone et qu'un email valide est fourni, chercher par email
        if (!$participant && $request->email) {
            $participant = Participant::where('email', $request->email)->first();
        }

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

        if ($existingEntry && !session('is_test_account')) {
            // Définir les cookies pour empêcher les participations multiples
            // 1. Cookie HTTP côté serveur
            $cookieName = 'contest_played_' . $request->contestId;
            $cookieExpiry = 60 * 24 * 365; // 1 an en minutes (valable pour toute la durée du concours)

            // Si le concours a une date de fin, utiliser cette date
            $contest = Contest::find($request->contestId);
            if ($contest && $contest->end_date) {
                $contestEndDate = new \DateTime($contest->end_date);
                $now = new \DateTime();
                $minutesUntilEnd = max(1, round(($contestEndDate->getTimestamp() - $now->getTimestamp()) / 60));
                $cookieExpiry = $minutesUntilEnd;
            }

            // Créer un cookie avec tous les paramètres explicites pour maximiser la compatibilité
            $cookie = cookie(
                $cookieName,                // nom spécifique au concours
                'played',                   // valeur simple
                $cookieExpiry,             // durée de vie en minutes
                '/',                        // chemin
                null,                       // domaine (null = domaine actuel)
                false,                      // secure (https uniquement)
                false,                      // httpOnly (non accessible par JavaScript)
                false,                      // raw
                'lax'                       // sameSite (lax = moins restrictif)
            );

            // Stocker aussi en session comme backup
            \Session::put($cookieName, 'played');

            // Journaliser la tentative de participation multiple
            \Log::info('Participation existante détectée', [
                'participant_id' => $participant->id,
                'contest_id' => $request->contestId,
                'ip' => $request->ip()
            ]);

            // Rediriger avec un message explicatif
            session()->flash('info', 'Vous avez déjà participé à ce concours. Une seule participation par semaine au concours est autorisée.');
            return redirect()->route('wheel.show', ['entry' => $existingEntry->id]);
        }
        
        // Si c'est un compte de test avec une entrée existante, supprimer cette entrée pour permettre de rejouer
        if ($existingEntry && session('is_test_account')) {
            \Log::info('Mode test: suppression de la participation existante pour permettre de rejouer', [
                'participant_id' => $participant->id,
                'contest_id' => $request->contestId,
                'email' => $participant->email ?? 'non défini'
            ]);
            
            // Option 1: Supprimer complètement l'entrée existante (plus radical)
            // $existingEntry->delete();
            
            // Option 2: Réutiliser l'entrée existante (préservation des données)
            // Le code ci-dessous va simplement continuer avec une nouvelle entrée sans supprimer l'ancienne
        }

        // Créer une nouvelle participation
        $entry = Entry::create([
            'participant_id' => $participant->id,
            'contest_id' => $request->contestId,
            'has_played' => false,
            'has_won' => false
        ]);

        // Définir maintenant les cookies et session pour marquer la participation à ce concours
        $cookieName = 'contest_played_' . $request->contestId;
        $cookieExpiry = 60 * 24 * 365; // 1 an en minutes par défaut

        // Si le concours a une date de fin, utiliser cette date
        $contest = Contest::find($request->contestId);
        if ($contest && $contest->end_date) {
            $contestEndDate = new \DateTime($contest->end_date);
            $now = new \DateTime();
            $minutesUntilEnd = max(1, round(($contestEndDate->getTimestamp() - $now->getTimestamp()) / 60));
            $cookieExpiry = $minutesUntilEnd;
        }

        // Définir le cookie avec une longue durée pour couvrir toute la durée du concours
        $cookie = cookie(
            $cookieName,         // nom spécifique au concours
            'played',           // valeur simple
            $cookieExpiry,      // durée de vie en minutes
            '/',                // chemin
            null,               // domaine (null = domaine actuel)
            false,              // secure (https uniquement)
            false,              // httpOnly (non accessible par JavaScript)
            false,              // raw
            'lax'               // sameSite (lax = moins restrictif)
        );

        // Stocker aussi en session comme backup
        \Session::put($cookieName, 'played');
        \Session::save();

        return redirect()->route('wheel.show', ['entry' => $entry->id])->withCookie($cookie)->with('localStorageKey', $cookieName);
    }

    /**
     * Affiche la roue de la fortune
     */
    public function showWheel(Entry $entry)
    {
        // Si l'entrée a déjà été jouée, rediriger vers la page de résultat
        if ($entry->has_played) {
            return redirect()->route('result.show', ['entry' => $entry->id]);
        }

        return view('wheel', ['entry' => $entry]);
    }

    /**
     * Affiche le résultat du spin
     */
    public function showResult(Entry $entry)
    {
        if (!$entry->has_played) {
            return redirect()->route('wheel.show', ['entry' => $entry->id]);
        }

        // Charger les relations pour s'assurer d'avoir accès au prix
        $entry->load('prize');

        return view('result', [
            'entry' => $entry,
            'qrCode' => $entry->qrCode,
            'prize' => $entry->prize // Passer le prix à la vue
        ]);
    }

    /**
     * Traite la requête AJAX pour faire tourner la roue et déterminer un résultat
     */
    public function spinWheel(Request $request)
    {
        \Log::info('Requête spinWheel reçue', $request->all());

        // Validation adaptée au format FormData
        $validated = $request->validate([
            'entry_id' => 'required'
        ]);

        try {
            // Utiliser find() au lieu de findOrFail()
            $entry = Entry::find($validated['entry_id']);

            // Si l'entrée n'existe pas, retourner une erreur
            if (!$entry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Participation introuvable. Veuillez vous inscrire à nouveau.'
                ], 404);
            }

            // Vérifier si la roue a déjà été tournée pour cette entrée
            // Pour les comptes en mode test, on ignore cette vérification pour permettre de jouer plusieurs fois
            if ($entry->has_played && !session('is_test_account')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette roue a déjà été tournée. Vous ne pouvez pas jouer à nouveau.'
                ]);
            }

            // Si c'est un compte de test et que l'entrée a déjà été jouée, 
            // on réinitialise l'entrée pour permettre de jouer à nouveau
            if ($entry->has_played && session('is_test_account')) {
                \Log::info('Mode test: permettre de tourner la roue à nouveau', [
                    'entry_id' => $entry->id,
                    'email' => $entry->participant->email ?? 'non défini'
                ]);
                
                // On ne réinitialise pas has_played pour conserver le fonctionnement normal
                // du reste du code. Le problème est résolu uniquement lors de la vérification.
            }

            // Récupérer le concours et vérifier s'il est actif
            $contest = $entry->contest;

            // Vérifier que le concours est toujours actif
            if ($contest->status !== 'active') {
                // Si le concours n'est plus actif, récupérer le concours actif par défaut
                $activeContest = Contest::where('status', 'active')->first();

                if (!$activeContest) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce concours est terminé et aucun autre concours actif n\'est disponible.'
                    ], 400);
                }

                // Mettre à jour l'entrée pour utiliser le concours actif
                $entry->contest_id = $activeContest->id;
                $entry->save();

                // Utiliser le concours actif
                $contest = $activeContest;

                \Log::info('Concours associé non actif, basculement vers le concours actif', [
                    'original_contest_id' => $entry->contest_id,
                    'active_contest_id' => $activeContest->id
                ]);
            }

            // Récupérer les distributions du concours actif
            $distributions = PrizeDistribution::where('contest_id', $contest->id)
                ->where('remaining', '>', 0)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->with('prize')
                ->get();

            // Vérifier si tous les stocks de prix sont épuisés
            $hasPrizesInStock = false;
            foreach ($distributions as $distribution) {
                if ($distribution->prize && $distribution->prize->stock > 0) {
                    $hasPrizesInStock = true;
                    break;
                }
            }

            // Définir des probabilités de gagner (5% chance de gagner par défaut)
            $chanceToWin = 0.05; // 5% de chance de gagner
            
            // Si c'est un compte de test, garantir la victoire
            if (session('is_test_account')) {
                \Log::info('Compte de test détecté, garantie de victoire activée');
                $chanceToWin = 1.0; // 100% de chance de gagner pour les comptes test
                
                // Pour les comptes de test, on considère qu'il y a toujours des prix en stock
                $hasPrizesInStock = true;
            }

            // Créer 20 secteurs au total: X gagnants, Y perdants selon le pourcentage de chance
            $sectors = [];

            // Si aucun prix en stock, tous les secteurs sont perdants
            if (!$hasPrizesInStock) {
                // 20 secteurs perdants
                for ($i = 0; $i < 20; $i++) {
                    $sectors[] = [
                        'id' => null,
                        'name' => 'Pas de chance',
                        'distribution_id' => null,
                        'probability' => 1/20, // Probabilité égale
                        'is_winning' => false
                    ];
                }
            } else {
                // Ajouter 1 secteur gagnant (5% de chance de gagner, soit 1/20)
                $sectors[] = [
                    'id' => 'win', // Juste un marqueur, le vrai prix sera choisi aléatoirement
                    'name' => 'Gagné !',
                    'distribution_id' => null,
                    'probability' => $chanceToWin,
                    'is_winning' => true
                ];

                // Ajouter 19 secteurs perdants (95% de chance de perdre, soit 19/20)
                for ($i = 0; $i < 19; $i++) {
                    $sectors[] = [
                        'id' => null,
                        'name' => 'Pas de chance',
                        'distribution_id' => null,
                        'probability' => (1 - $chanceToWin) / 19,
                        'is_winning' => false
                    ];
                }
            }

            // Mélanger les secteurs pour une disposition aléatoire sur la roue
            shuffle($sectors);

            // Normaliser les probabilités pour qu'elles somment à 1
            $totalProbability = array_sum(array_column($sectors, 'probability'));
            if ($totalProbability > 0) {
                foreach ($sectors as &$sector) {
                    $sector['probability'] = $sector['probability'] / $totalProbability;
                }
            }

            // Générer un nombre aléatoire entre 0 et 1
            $rand = mt_rand(0, 100) / 100;

            // Sélectionner un secteur basé sur les probabilités
            $selectedSector = null;
            $cumulativeProbability = 0;

            foreach ($sectors as $sector) {
                $cumulativeProbability += $sector['probability'];
                if ($rand <= $cumulativeProbability) {
                    $selectedSector = $sector;
                    break;
                }
            }

            // Si aucun secteur n'a été sélectionné, prendre le dernier
            if (!$selectedSector && !empty($sectors)) {
                $selectedSector = end($sectors);
            }

            // Enregistrer le résultat dans la base de données
            DB::beginTransaction();

            try {
                // Déterminer le résultat (gagné ou perdu)
                $hasWon = $selectedSector && $selectedSector['is_winning'] && $hasPrizesInStock;

                // Si le joueur a gagné, choisir un prix aléatoirement parmi les disponibles
                $prizeId = null;
                $distributionId = null;

                if ($hasWon) {
                    // Collecter tous les prix disponibles avec leurs distributions
                    $availablePrizes = [];

                    foreach ($distributions as $distribution) {
                        if ($distribution->prize && $distribution->prize->stock > 0) {
                            $availablePrizes[] = [
                                'prize_id' => $distribution->prize->id,
                                'distribution_id' => $distribution->id
                            ];
                        }
                    }

                    // Choisir un prix au hasard
                    if (count($availablePrizes) > 0) {
                        $randomIndex = array_rand($availablePrizes);
                        $selectedPrize = $availablePrizes[$randomIndex];
                        $prizeId = $selectedPrize['prize_id'];
                        $distributionId = $selectedPrize['distribution_id'];
                    } else {
                        // Pas de prix disponible, faire perdre le joueur
                        $hasWon = false;
                    }
                }

                // Mettre à jour l'entrée
                $entry->has_played = true;
                $entry->has_won = $hasWon;
                $entry->prize_id = $prizeId;

                $entry->save();

                // Si c'est un prix gagnant, mettre à jour la distribution
                if ($hasWon && $distributionId) {
                    $distribution = PrizeDistribution::find($distributionId);
                    if ($distribution) {
                        $distribution->remaining = $distribution->remaining - 1;
                        $distribution->save();
                    }

                    // Mettre à jour le stock du prix
                    $prize = Prize::find($prizeId);
                    if ($prize) {
                        $prize->stock = $prize->stock - 1;
                        $prize->save();
                    }
                }

                // Générer un QR code unique pour cette participation
                $qrCode = 'QR-' . Str::random(8);
                $entry->qr_code = $qrCode;
                $entry->save();

                // ENVOI WHATSAPP AU GAGNANT
                if ($hasWon && $participant = $entry->participant) {
                    $recipientPhone = '+225' . ltrim($participant->phone, '0');
                    $qrDir = storage_path('app/qrcodes');
                    if (!file_exists($qrDir)) {
                        mkdir($qrDir, 0777, true);
                    }
                    $qrPath = $qrDir . "/qr-{$entry->id}.png";
                    Builder::create()
                        ->writer(new PngWriter())
                        ->data($qrCode)
                        ->size(300)
                        ->margin(10)
                        ->build()
                        ->saveToFile($qrPath);
                    
                    // Stocker le nom du prix dans la session pour le message WhatsApp
                    if ($prize) {
                        session(['prize_name' => $prize->name]);
                    }
                    
                    // Utiliser Green API pour l'envoi WhatsApp (nouvelle méthode)
                    $greenWhatsapp = new GreenWhatsAppService();
                    try {
                        $message = "Félicitations ! Vous avez gagné " . ($prize ? $prize->name : "un prix") . 
                                  ". Voici votre QR code pour récupérer votre gain. Conservez-le précieusement !";
                        
                        $greenWhatsapp->sendQrCodeToWinner($recipientPhone, $qrPath, $message);
                        \Log::info('Message WhatsApp envoyé au gagnant via Green API', ['phone' => $recipientPhone, 'entry_id' => $entry->id]);
                    } catch (\Exception $ex) {
                        \Log::error('Erreur lors de l\'envoi WhatsApp via Green API', ['error' => $ex->getMessage()]);
                        
                        // Fallback sur l'ancien service en cas d'échec
                        try {
                            $whatsapp = new WhatsAppService();
                            $whatsapp->sendQrCodeToWinner($recipientPhone, $qrPath);
                            \Log::info('Message WhatsApp envoyé au gagnant (fallback sur ancien service)', ['phone' => $recipientPhone, 'entry_id' => $entry->id]);
                        } catch (\Exception $innerEx) {
                            \Log::error('Erreur lors de l\'envoi WhatsApp (fallback également échoué)', ['error' => $innerEx->getMessage()]);
                        }
                    }
                }

                // Pour les utilisateurs en mode test, réinitialiser l'entrée pour permettre de jouer à nouveau
                if (session('is_test_account') && isset($entry) && $entry) {
                    try {
                        DB::beginTransaction();
                        
                        // Réinitialiser l'entrée pour pouvoir jouer à nouveau
                        $entry->has_played = false;
                        $entry->save();
                        
                        \Log::info('Mode test: entrée réinitialisée pour permettre de jouer à nouveau', [
                            'entry_id' => $entry->id
                        ]);
                        
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollback();
                        \Log::error('Erreur lors de la réinitialisation de l\'entrée en mode test', [
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'result' => [
                        'status' => $entry->has_won ? 'win' : 'lose',
                        'message' => $entry->has_won
                            ? 'Félicitations ! Vous avez gagné !'
                            : 'Pas de chance cette fois-ci. Vous pourrez réessayer ultérieurement !',
                        'prize' => $selectedSector,
                        'prize_info' => $prize ? [
                            'id' => $prize->id,
                            'name' => $prize->name,
                            'value' => $prize->value,
                            'type' => $prize->type,
                        ] : null
                    ],
                    'qr_code' => $entry->qr_code
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                \Log::error('Erreur lors du traitement du résultat de la roue', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors du traitement du résultat: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors du traitement de la requête spinWheel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifie le QR code et retourne le résultat
     */
    public function checkQrCode($code)
    {
        // Journaliser la requête pour débogage
        \Log::info('Vérification QR code', ['code' => $code]);

        try {
            // Chercher l'entrée par le champ qr_code
            $entry = Entry::where('qr_code', 'LIKE', '%' . $code . '%')->first();

            if (!$entry) {
                \Log::warning('QR code non trouvé', ['code' => $code]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR code non valide ou introuvable.'
                ]);
            }

            // Charger les relations nécessaires
            $entry->load(['participant', 'prize']);

            // Marquer l'entrée comme réclamée si ce n'est pas déjà fait
            if (!$entry->claimed) {
                $entry->claimed = true;
                $entry->save();
                \Log::info('QR code marqué comme réclamé', ['entry_id' => $entry->id]);
            }

            // Déterminer le message en fonction du résultat
            $message = $entry->has_won
                ? 'Félicitations ! Vous avez gagné : ' . ($entry->prize->name ?? 'un prix')
                : 'Pas de chance cette fois-ci. Merci d\'avoir participé !';

            return response()->json([
                'success' => true,
                'status' => $entry->has_won ? 'win' : 'lose',
                'message' => $message,
                'prize' => $entry->prize ? $entry->prize->name : null,
                'participant' => $entry->participant ? $entry->participant->first_name . ' ' . $entry->participant->last_name : 'Participant'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification du QR code', [
                'code' => $code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la vérification du QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche la page de vérification du QR code
     */
    public function qrCodeResultPage($code)
    {
        return view('qr-result', ['code' => $code]);
    }
}
