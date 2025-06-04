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

        if ($hasPlayed) {
            // S'assurer que le cookie est défini pour renforcer la limitation
            // Même si l'utilisateur est détecté par session ou BD, ajouter le cookie pour renforcer
            if (!$request->cookie($cookieName)) {
                $cookieExpiry = 60 * 24 * 30; // 30 jours en minutes par défaut

                // Si le concours a une date de fin, utiliser cette date pour l'expiration
                if ($activeContest->end_date) {
                    $contestEndDate = new \DateTime($activeContest->end_date);
                    $now = new \DateTime();
                    $minutesUntilEnd = max(1, round(($contestEndDate->getTimestamp() - $now->getTimestamp()) / 60));
                    $cookieExpiry = $minutesUntilEnd;
                }

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
        if (($request->cookie($cookieName) !== null || \Session::has($sessionKey))) {
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

        if ($existingEntry) {
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
            if ($entry->has_played) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette roue a déjà été tournée. Vous ne pouvez pas jouer à nouveau.'
                ]);
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
                ->where('remaining', '>', 0)  // Ne sélectionner que les distributions avec stock restant
                ->where(function($query) {
                    $now = now();
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', $now);
                })
                ->where(function($query) {
                    $now = now();
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', $now);
                })
                ->with(['prize' => function($query) {
                    $query->where('stock', '>', 0);  // Ne sélectionner que les prix avec stock positif
                }])
                ->get()
                ->filter(function($distribution) {
                    return $distribution->prize !== null;  // Filtrer les distributions sans prix valide
                });

            // Vérifier si tous les stocks de prix sont épuisés
            $hasPrizesInStock = false;
            foreach ($distributions as $distribution) {
                if ($distribution->prize && $distribution->prize->stock > 0) {
                    $hasPrizesInStock = true;
                    break;
                }
            }

            // Obtenir l'heure actuelle en format GMT/UTC
            $now = now()->timezone('UTC');
            $currentHour = (int) $now->format('H');

            // Vérifier si l'heure actuelle est dans les plages spécifiées (12h-14h ou 18h-20h GMT)
            $isPromotionalTime = ($currentHour >= 12 && $currentHour < 14) ||
                                 ($currentHour >= 18 && $currentHour < 20);

            // Définir des probabilités de gagner selon l'heure
            $chanceToWin = 0.01; // 1% par défaut

            // Vérifier si l'utilisateur est en mode test (d'après la mémoire existante)
            if (session('is_test_account')) {
                $chanceToWin = 1.0; // 100% pour les comptes de test
                $hasPrizesInStock = true; // Forcer l'existence de prix pour les comptes de test
                \Log::info('Compte de test détecté, 100% de chances de gagner');
            }
            // Si c'est une période promotionnelle, augmenter les chances de gain à 50%
            elseif ($isPromotionalTime) {
                $chanceToWin = 0.5; // 50% de chances durant les heures promotionnelles
                \Log::info('Période promotionnelle détectée: ' . $now->format('Y-m-d H:i:s') . ' - Chances de gain augmentées à 50%');
            }

            \Log::info('Paramètres de spin', [
                'heure_actuelle' => $now->format('Y-m-d H:i:s'),
                'période_promotionnelle' => $isPromotionalTime,
                'chance_de_gain' => $chanceToWin * 100 . '%',
                'prix_en_stock' => $hasPrizesInStock
            ]);

            // Créer 100 secteurs au total: X gagnants, Y perdants selon le pourcentage de chance
            $sectors = [];

            // Si aucun prix en stock, tous les secteurs sont perdants
            if (!$hasPrizesInStock) {
                // 100 secteurs perdants
                for ($i = 0; $i < 100; $i++) {
                    $sectors[] = [
                        'id' => null,
                        'name' => 'Pas de chance',
                        'distribution_id' => null,
                        'probability' => 1/100, // Probabilité égale
                        'is_winning' => false
                    ];
                }
            } else {
                // Ajouter 1 secteur gagnant (1% de chance de gagner, soit 1/100)
                $sectors[] = [
                    'id' => 'win', // Juste un marqueur, le vrai prix sera choisi aléatoirement
                    'name' => 'Gagné !',
                    'distribution_id' => null,
                    'probability' => $chanceToWin,
                    'is_winning' => true
                ];

                // Ajouter 99 secteurs perdants (99% de chance de perdre, soit 99/100)
                for ($i = 0; $i < 99; $i++) {
                    $sectors[] = [
                        'id' => null,
                        'name' => 'Pas de chance',
                        'distribution_id' => null,
                        'probability' => (1 - $chanceToWin) / 99,
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

                // Vérifier si le participant a déjà gagné un prix dans un autre concours
                if ($hasWon && $entry->participant) {
                    $alreadyWon = $this->hasParticipantPreviouslyWon(
                        $entry->participant->phone,
                        $entry->participant->email,
                        $entry->participant->first_name,
                        $entry->participant->last_name
                    );

                    if ($alreadyWon) {
                        \Log::warning('Participant déjà gagnant à un précédent concours - Résultat forcé à perdant', [
                            'entry_id' => $entry->id,
                            'participant_id' => $entry->participant->id,
                            'phone' => $entry->participant->phone,
                            'email' => $entry->participant->email,
                            'nom' => $entry->participant->last_name,
                            'prenom' => $entry->participant->first_name
                        ]);
                        // Forcer le résultat à perdant
                        $hasWon = false;
                    }
                }

                // Si le joueur a gagné, choisir un prix aléatoirement parmi les disponibles
                $prizeId = null;
                $distributionId = null;

                if ($hasWon) {
                    // Collecter tous les prix disponibles avec leurs distributions
                    // mais uniquement ceux associés au concours actuel
                    $availablePrizes = [];

                    foreach ($distributions as $distribution) {
                        if ($distribution->prize &&
                            $distribution->prize->stock > 0 &&
                            $distribution->contest_id == $contest->id) {
                            $availablePrizes[] = [
                                'prize_id' => $distribution->prize->id,
                                'distribution_id' => $distribution->id,
                                'prize_name' => $distribution->prize->name
                            ];
                        }
                    }

                    \Log::info('Sélection de prix pour le concours actif', [
                        'contest_id' => $contest->id,
                        'available_prizes_count' => count($availablePrizes),
                        'available_prizes' => $availablePrizes
                    ]);

                    // Choisir un prix au hasard
                    if (count($availablePrizes) > 0) {
                        $randomIndex = array_rand($availablePrizes);
                        $selectedPrize = $availablePrizes[$randomIndex];
                        $prizeId = $selectedPrize['prize_id'];
                        $distributionId = $selectedPrize['distribution_id'];

                        \Log::info('Prix sélectionné aléatoirement', [
                            'selected_index' => $randomIndex,
                            'prize_id' => $prizeId,
                            'prize_name' => $selectedPrize['prize_name'],
                            'distribution_id' => $distributionId
                        ]);
                    } else {
                        // Pas de prix disponible, faire perdre le joueur
                        $hasWon = false;
                    }
                }

                // Mettre à jour l'entrée
                $entry->has_played = true;
                $entry->has_won = $hasWon;
                $entry->prize_id = $prizeId;

                // Définir la date de gain si le participant a gagné
                if ($hasWon) {
                    $entry->won_date = now();
                }

                $entry->save();

                // Force le débogage pour voir ce qui se passe exactement ici
                \Log::debug('État avant décrémentation des stocks', [
                    'hasWon' => $hasWon,
                    'entry_id' => $entry->id,
                    'prize_id' => $prizeId,
                    'distribution_id' => $distributionId
                ]);

                // Si c'est un prix gagnant, mettre à jour la distribution et le stock
                // Même s'il n'y a pas de distribution_id, essayer de trouver une distribution
                if ($hasWon && $prizeId) {
                    \Log::debug('Tentative de décrémentation du stock pour un gain', [
                        'prize_id' => $prizeId,
                        'distribution_id' => $distributionId
                    ]);

                    // DÉCRÉMENTATION DU STOCK DU PRIX DIRECTEMENT - Stratégie 1
                    $prize = Prize::find($prizeId);
                    if ($prize) {
                        \Log::info('Prix trouvé en base, vérification du stock', [
                            'prize_id' => $prizeId,
                            'prize_name' => $prize->name,
                            'current_stock' => $prize->stock
                        ]);

                        if ($prize->stock > 0) {
                            $prize->stock -= 1;
                            $prize->save();

                            \Log::info('Stock du prix décrémenté directement', [
                                'prize_id' => $prizeId,
                                'prize_name' => $prize->name,
                                'new_stock' => $prize->stock
                            ]);
                        }
                    }

                    // DÉCRÉMENTATION DU STOCK DE LA DISTRIBUTION - Stratégie 2
                    // Si on a un distribution_id, essayer de l'utiliser directement
                    if ($distributionId) {
                        $distribution = PrizeDistribution::find($distributionId);
                        if ($distribution) {
                            \Log::debug('Distribution trouvée avec ID fourni', [
                                'distribution_id' => $distributionId,
                                'prize_id' => $distribution->prize_id,
                                'current_remaining' => $distribution->remaining
                            ]);

                            if ($distribution->remaining > 0) {
                                $distribution->remaining -= 1;
                                $distribution->save();

                                \Log::info('Stock de distribution décrémenté avec ID fourni', [
                                    'distribution_id' => $distributionId,
                                    'new_remaining' => $distribution->remaining
                                ]);
                            }
                        }
                    }
                    // Sinon, chercher des distributions liées au prix
                    else {
                        \Log::debug('Aucun distribution_id fourni, recherche de distributions pour le prix', [
                            'prize_id' => $prizeId
                        ]);

                        // Chercher une distribution pour ce prix dans le concours actuel
                        $matchingDistribution = PrizeDistribution::where('prize_id', $prizeId)
                            ->where('contest_id', $contest->id)
                            ->where('remaining', '>', 0)
                            ->first();

                        if ($matchingDistribution) {
                            \Log::info('Distribution trouvée par recherche', [
                                'distribution_id' => $matchingDistribution->id,
                                'prize_id' => $prizeId,
                                'current_remaining' => $matchingDistribution->remaining
                            ]);

                            $matchingDistribution->remaining -= 1;
                            $matchingDistribution->save();

                            \Log::info('Stock de distribution décrémenté après recherche', [
                                'distribution_id' => $matchingDistribution->id,
                                'new_remaining' => $matchingDistribution->remaining
                            ]);
                        } else {
                            \Log::warning('Aucune distribution trouvée pour ce prix dans ce concours', [
                                'prize_id' => $prizeId,
                                'contest_id' => $contest->id
                            ]);
                        }
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
                                  ". Voici votre QR code pour récupérer votre gain. Conservez-le précieusement ! Prière de contacter le 07 19 04 87 28 afin de récupérer votre prix.";

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

    /**
     * Vérifie si un participant a déjà gagné un prix à n'importe quel concours en utilisant
     * différentes méthodes d'identification (téléphone, email, nom+prénom)
     *
     * @param string $phone Numéro de téléphone
     * @param string|null $email Email (optionnel)
     * @param string|null $firstName Prénom (optionnel)
     * @param string|null $lastName Nom (optionnel)
     * @return bool True si le participant a déjà gagné, false sinon
     */
    private function hasParticipantPreviouslyWon($phone, $email = null, $firstName = null, $lastName = null)
    {
        // Trouver toutes les entrées gagnantes basées sur le téléphone ou l'email
        $query = Entry::where('has_won', true)
            ->whereHas('participant', function($query) use ($phone, $email) {
                $query->where('phone', $phone);

                if ($email) {
                    $query->orWhere('email', $email);
                }
            });

        $alreadyWonByPhoneOrEmail = $query->exists();

        // Si on a déjà trouvé par téléphone ou email, pas besoin de chercher plus loin
        if ($alreadyWonByPhoneOrEmail) {
            \Log::info('Participant déjà gagnant (détecté par téléphone/email)', [
                'phone' => $phone,
                'email' => $email,
            ]);
            return true;
        }

        // Si le prénom et le nom sont fournis, rechercher aussi les correspondances par nom/prénom
        if ($firstName && $lastName) {
            // Normaliser les noms (enlever accents, espaces, tout en minuscules)
            $normalizedFirstName = $this->normalizeString($firstName);
            $normalizedLastName = $this->normalizeString($lastName);

            // Rechercher toutes les entrées gagnantes
            $winningEntries = Entry::where('has_won', true)
                ->with('participant')
                ->get();

            // Vérifier manuellement chaque entrée pour les correspondances approximatives
            foreach ($winningEntries as $entry) {
                if (!$entry->participant) continue;

                $participantFirstName = $this->normalizeString($entry->participant->first_name);
                $participantLastName = $this->normalizeString($entry->participant->last_name);

                // Vérifier si les noms/prénoms sont similaires (avec une tolérance pour les erreurs de saisie)
                $firstNameSimilarity = similar_text($normalizedFirstName, $participantFirstName, $firstNamePercentage);
                $lastNameSimilarity = similar_text($normalizedLastName, $participantLastName, $lastNamePercentage);

                // Si les noms sont très similaires (plus de 85% de similarité)
                if ($firstNamePercentage > 85 && $lastNamePercentage > 85) {
                    \Log::info('Participant déjà gagnant (détecté par similarité de nom/prénom)', [
                        'nouveau_prenom' => $firstName,
                        'nouveau_nom' => $lastName,
                        'prenom_existant' => $entry->participant->first_name,
                        'nom_existant' => $entry->participant->last_name,
                        'similarite_prenom' => $firstNamePercentage,
                        'similarite_nom' => $lastNamePercentage
                    ]);
                    return true;
                }
            }
        }

        \Log::info('Vérification si le participant a déjà gagné', [
            'phone' => $phone,
            'email' => $email,
            'prenom' => $firstName,
            'nom' => $lastName,
            'a_déjà_gagné' => $alreadyWonByPhoneOrEmail
        ]);

        return $alreadyWonByPhoneOrEmail;
    }

    /**
     * Normalise une chaîne pour la comparaison
     * Enlève les accents, les espaces, et met tout en minuscules
     *
     * @param string $string
     * @return string
     */
    private function normalizeString($string)
    {
        // Convertir en minuscules
        $string = mb_strtolower($string);

        // Enlever les accents
        $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);

        // Enlever les caractères non alphanumériques
        $string = preg_replace('/[^a-z0-9]/', '', $string);

        return $string;
    }
}
