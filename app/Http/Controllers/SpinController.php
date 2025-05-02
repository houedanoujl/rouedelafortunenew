<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Prize;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;
use App\Services\GreenWhatsAppService;
use Illuminate\Support\Facades\Schema;

class SpinController extends Controller
{
    public function show(Entry $entry, Request $request)
    {
        LogHelper::addSessionSeparator('START');
        
        if (!$entry->participant) {
            abort(404);
        }

        // Vérification pour les emails interdits (employés SIFCA et Big Five)
        if ($entry->participant && $entry->participant->email) {
            $restrictedDomains = [
                '@bigfivesolutions.com',
                '@bigfiveabidjan.com',
                '@sifca.ci'
            ];
            
            $email = strtolower($entry->participant->email);
            foreach ($restrictedDomains as $domain) {
                if (str_ends_with($email, $domain)) {
                    // Journaliser la tentative
                    Log::warning('Tentative de participation par un employé', [
                        'email' => $email,
                        'entry_id' => $entry->id,
                        'ip' => $request->ip()
                    ]);
                    
                    // Rediriger vers une vue spéciale avec message d'avertissement
                    return view('restricted-access', [
                        'entry' => $entry,
                        'reason' => 'Le jeu est interdit aux employés et membres de la famille de SIFCA et Big Five.'
                    ]);
                }
            }
        }

        // Vérifier si l'entrée a déjà été jouée
        if ($entry->has_played) {
            // Vérifier si nous avons un jeton d'authentification pour cette entrée
            $authToken = $request->session()->get('entry_auth_token_' . $entry->id);
            
            // Si aucun jeton n'existe, en générer un nouveau
            if (!$authToken) {
                $authToken = md5($entry->id . '_' . time() . '_' . rand(1000, 9999));
                $request->session()->put('entry_auth_token_' . $entry->id, $authToken);
            }
            
            // Marquer cette entrée comme ayant été consultée dans cette session
            $request->session()->put('played_entry_' . $entry->id, true);
            
            // Capturer l'empreinte de l'appareil
            $deviceFingerprint = $request->cookie('device_fingerprint');
            if ($deviceFingerprint) {
                $request->session()->put('device_fingerprint', $deviceFingerprint);
            }
            
            // Rediriger vers la page de résultat avec le jeton d'authentification
            return redirect()->route('spin.result', [
                'entry' => $entry->id,
                'token' => $authToken
            ]);
        }
        
        // Vérifier si des lots sont disponibles
        $contest = $entry->contest;
        $hasStock = false;
        
        if ($contest) {
            // Chercher les distributions avec stock disponible
            $distributions = \App\Models\PrizeDistribution::where('contest_id', $contest->id)
                ->where('remaining', '>', 0)
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
                ->with('prize')
                ->get();
                
            $validDistributions = $distributions->filter(function($dist) {
                return $dist->prize !== null;
            });
            
            $hasStock = $validDistributions->count() > 0;
        }
        
        // Si aucun stock, rediriger vers page "stock épuisé"
        if (!$hasStock) {
            return view('no-stock', ['entry' => $entry, 'contest' => $contest]);
        }

        // Générer un jeton d'authentification pour cette entrée
        $authToken = md5($entry->id . '_' . time() . '_' . rand(1000, 9999));
        $request->session()->put('entry_auth_token_' . $entry->id, $authToken);
        
        // Marquer cette entrée comme étant consultée dans cette session
        $request->session()->put('played_entry_' . $entry->id, true);
        
        // Génération d'une empreinte d'appareil unique si elle n'existe pas déjà
        $deviceFingerprint = $request->cookie('device_fingerprint');
        if (!$deviceFingerprint) {
            $deviceFingerprint = md5($request->ip() . '_' . $request->userAgent() . '_' . time());
            cookie()->queue('device_fingerprint', $deviceFingerprint, 60 * 24 * 30); // 30 jours
        }
        
        // Stocker l'empreinte dans la session
        $request->session()->put('device_fingerprint', $deviceFingerprint);

        LogHelper::addSessionSeparator('END');
        return view('wheel', compact('entry'));
    }

    public function result(Entry $entry, Request $request)
    {
        LogHelper::addSessionSeparator('START');
        
        // Début de la session de la page de résultat
        Log::info('=== DÉBUT DE LA SESSION - PAGE RÉSULTAT ===', [
            'entry_id' => $entry->id,
            'timestamp' => now()->toDateTimeString(),
            'has_won' => $entry->has_won ? 'Oui' : 'Non'
        ]);
        
        if (!$entry->participant) {
            abort(404);
        }
        
        // === PROTECTION CONTRE LE PARTAGE D'URL ===
        // Vérifier si cette entrée a été vue dans cette session
        $sessionKey = 'played_entry_' . $entry->id;
        $entryPlayed = $request->session()->has($sessionKey);
        
        // Vérifier le token d'authentification
        $authToken = $request->session()->get('entry_auth_token_' . $entry->id);
        $requestToken = $request->query('token');
        $tokenValid = ($authToken && $requestToken && $authToken === $requestToken);
        
        // Vérifier l'empreinte du navigateur
        $deviceFingerprint = $request->cookie('device_fingerprint');
        $storedFingerprint = $request->session()->get('device_fingerprint');
        $fingerprintValid = ($deviceFingerprint && $storedFingerprint && $deviceFingerprint === $storedFingerprint);
        
        // Si ni le token ni l'empreinte ne sont valides, rediriger
        if (!$entryPlayed || (!$tokenValid && !$fingerprintValid)) {
            Log::warning('Tentative d\'accès non autorisé à un résultat de spin', [
                'entry_id' => $entry->id,
                'token_valid' => $tokenValid,
                'fingerprint_valid' => $fingerprintValid,
                'session_has_played' => $entryPlayed,
                'ip' => $request->ip()
            ]);
            
            return redirect()->route('home')->with('error', 
                'Accès non autorisé. Cette URL est personnelle et ne peut pas être partagée.');
        }
        // === FIN DE LA PROTECTION ===
        
        // Vérifier si le jeton d'authentification est valide (pour le débogage)
        Log::debug('Vérification d\'authentification pour la page de résultat', [
            'entry_id' => $entry->id,
            'has_auth_token' => !empty($authToken),
            'request_has_token' => !empty($requestToken),
            'tokens_match' => (!empty($authToken) && !empty($requestToken) && $authToken === $requestToken),
            'session_marked_played' => $request->session()->has('played_entry_' . $entry->id)
        ]);
        
        // Log détaillé pour le débogage des informations d'entrée
        Log::debug('SpinController@result - Informations initiales', [
            'entry_id' => $entry->id,
            'participant_id' => $entry->participant->id ?? 'Non défini',
            'email' => $entry->participant->email ?? 'Non défini',
            'phone' => $entry->participant->phone ?? 'Non défini',
            'has_won' => $entry->has_won ? 'Oui' : 'Non'
        ]);
        
        // Traiter le résultat du spin s'il est stocké en session et pas encore traité
        $wheelResult = session()->get('wheel_result');
        if ($wheelResult && !$wheelResult['processed'] && $wheelResult['entry_id'] == $entry->id) {
            Log::info('Traitement du résultat après fin d\'animation', [
                'entry_id' => $entry->id,
                'isWinning' => $wheelResult['isWinning'] ? 'Oui' : 'Non',
                'has_won_in_db' => $entry->has_won ? 'Oui' : 'Non'
            ]);
            
            // Si gagné, créer un QR code et décrémenter les stocks
            // ATTENTION: On vérifie maintenant le statut réel en DB (has_won), pas celui en session (isWinning)
            // Car il peut y avoir une divergence entre les deux
            if ($entry->has_won) {
                // Générer un code QR plus lisible et mémorisable
                $qrCode = 'DNR70-' . strtoupper(substr(md5($entry->id . time()), 0, 8));
                
                // Créer l'enregistrement QR code s'il n'existe pas déjà
                if (!$entry->qr_code) {
                    $qrCodeModel = \App\Models\QrCode::create([
                        'entry_id' => $entry->id,
                        'code' => $qrCode,
                    ]);
                    
                    // Mettre à jour l'entrée avec le QR code
                    $entry->qr_code = $qrCode;
                    $entry->save();
                }
                
                // NE PAS décrémenter ici - on attendra d'avoir le prix final
                
                // Ajouter une variable de session pour déclencher les confettis
                session()->put('show_confetti', true);
            }
            
            // Marquer le résultat comme traité
            $wheelResult['processed'] = true;
            session()->put('wheel_result', $wheelResult);
        }
        
        // Pour éviter les doubles envois de messages WhatsApp, vérifier si cette entrée a déjà été visitée
        $resultVisitKey = 'result_visited_'.$entry->id;
        $alreadyVisited = $request->session()->has($resultVisitKey);
        
        // Marquer cette entrée comme visitée
        $request->session()->put($resultVisitKey, now()->toDateTimeString());
        
        if (!$alreadyVisited) {
            Log::info('Première visite de la page de résultat', ['entry_id' => $entry->id]);
            
            // Envoyer la notification WhatsApp lors de la première visite
            if ($entry->has_won && $entry->participant && $entry->participant->phone) {
                try {
                    $participant = $entry->participant;
                    $greenWhatsApp = new GreenWhatsAppService();
                    
                    // Récupérer ou créer le QR code
                    $qrCodeObj = null;
                    if ($entry->qr_code) {
                        // Rechercher le QR code dans la base de données
                        $qrCodeObj = \App\Models\QrCode::where('code', $entry->qr_code)
                            ->orWhere('entry_id', $entry->id)
                            ->first();
                    }
                    
                    if (!$qrCodeObj) {
                        $code = 'DNR70-' . strtoupper(substr(md5($entry->id . time()), 0, 8));
                        $qrCodeObj = \App\Models\QrCode::create([
                            'entry_id' => $entry->id,
                            'code' => $code,
                        ]);
                        
                        // Mettre à jour l'entrée avec le QR code
                        $entry->qr_code = $code;
                        $entry->save();
                    }
                    
                    // Générer l'image du QR code si nécessaire
                    $qrCodePath = storage_path('app/public/qrcodes/qrcode-' . $qrCodeObj->code . '.png');
                    if (!file_exists($qrCodePath)) {
                        $qrCodeImage = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                            ->format('png')
                            ->generate($qrCodeObj->code);
                            
                        if (!file_exists(dirname($qrCodePath))) {
                            mkdir(dirname($qrCodePath), 0755, true);
                        }
                        file_put_contents($qrCodePath, $qrCodeImage);
                    }
                    
                    // Message personnalisé pour la première visite de la page de résultat
                    $message = "Félicitations {$participant->first_name}! Vous avez gagné " . 
                              ($entry->prize ? $entry->prize->name : "un lot") . 
                              ". Voici votre QR code pour récupérer votre gain. Conservez-le précieusement!\n\nNuméro du QR code : ".$qrCodeObj->code.
                              "\n\nPour le retrait de votre lot, contactez le 07 19 04 87 28";
                    
                    // Envoyer via Green API
                    $result = $greenWhatsApp->sendQrCodeToWinner($participant->phone, $qrCodePath, $message);
                    
                    // Journaliser le résultat de l'envoi
                    if ($result === true) {
                        Log::info('Notification WhatsApp envoyée avec succès', [
                            'entry_id' => $entry->id,
                            'phone' => $participant->phone,
                            'qr_code' => $qrCodeObj->code
                        ]);
                    } else {
                        Log::error('Échec de l\'envoi WhatsApp', [
                            'entry_id' => $entry->id,
                            'phone' => $participant->phone,
                            'error' => $result
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erreur lors de l\'envoi WhatsApp à la première visite', [
                        'error' => $e->getMessage(),
                        'entry_id' => $entry->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } else {
            Log::info('Page de résultat déjà visitée', [
                'entry_id' => $entry->id, 
                'first_visit' => $request->session()->get($resultVisitKey),
                'current_visit' => now()->toDateTimeString()
            ]);
        }
        
        // =====================================================================
        // INFORMATION SUR LA DÉCRÉMENTATION DES STOCKS
        // Le stock est maintenant décrémenté UNE SEULE FOIS ci-dessus dans le traitement
        // du résultat de wheel_result, après que la roue a fini de tourner.
        // La décrémentation dans FortuneWheel.handleWinning() a été désactivée.
        // =====================================================================
        
        // Récupérer le prix si l'entrée est gagnante
        $prize = null;
        $qrCode = null;
        
        try {
            // Récupérer les données du participant
            $participant = $entry->participant;
            
            // Si l'entrée est gagnante
            if ($entry->has_won) {
                // S'assurer que prize_id est défini
                if ($entry->prize_id) {
                    $prize = $entry->prize;
                } else {
                    // Si prize_id manquant mais entrée marquée comme gagnante, attribuer un prix
                    Log::warning('Entrée marquée comme gagnante sans prix attribué - Recherche d\'un prix', [
                        'entry_id' => $entry->id
                    ]);
                    
                    // Chercher toutes les distributions disponibles avec un prix
                    $distributions = \App\Models\PrizeDistribution::where('contest_id', $entry->contest_id)
                        ->where('remaining', '>', 0)
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
                        ->with('prize')
                        ->get();
                        
                    if ($distributions->count() > 0) {
                        // Sélectionner une distribution aléatoire au lieu de prendre la première
                        $randomIndex = rand(0, $distributions->count() - 1);
                        $distribution = $distributions[$randomIndex];
                        
                        if ($distribution && $distribution->prize) {
                            $prize = $distribution->prize;
                            
                            // Assigner le prix à l'entrée (SANS décrémenter)
                            $entry->prize_id = $prize->id;
                            $entry->distribution_id = $distribution->id;
                            $entry->save();
                            
                            // Décrémenter APRÈS avoir attribué le prix
                            $distribution->decrementRemaining();
                            
                            Log::info('Prix attribué aléatoirement à une entrée gagnante', [
                                'entry_id' => $entry->id,
                                'prize_id' => $prize->id,
                                'prize_name' => $prize->name,
                                'distribution_id' => $distribution->id,
                                'total_distributions' => $distributions->count()
                            ]);
                        }
                    } else {
                        Log::warning('Aucune distribution avec stock disponible trouvée pour attribuer un prix', [
                            'entry_id' => $entry->id,
                            'contest_id' => $entry->contest_id
                        ]);
                    }
                }
                
                // Récupérer le code QR s'il existe
                if ($entry->qr_code) {
                    $qrCode = $entry->qr_code;
                } else {
                    // Créer un QR code si manquant
                    $code = 'DNR70-' . strtoupper(substr(md5($entry->id . time()), 0, 8));
                    $qrCode = \App\Models\QrCode::create([
                        'entry_id' => $entry->id,
                        'code' => $code,
                    ]);
                    
                    // Mettre à jour l'entrée avec le QR code
                    $entry->qr_code = $code;
                    $entry->save();
                    
                    Log::info('QR code créé pour une entrée gagnante', [
                        'entry_id' => $entry->id,
                        'qr_code' => $code
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des données', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'entry_id' => $entry->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        
        // Générer une clé unique pour le localStorage (à des fins de vérification côté client)
        $localStorageKey = 'contest_played_' . $entry->contest_id;
        $request->session()->put('localStorageKey', $localStorageKey);

        LogHelper::addSessionSeparator('END');
        return view('result', compact('entry', 'qrCode', 'prize'));
    }

    /**
     * Formate un numéro de téléphone pour l'affichage (obfuscation partielle)
     */
    private function formatPhoneDisplay($phone)
    {
        // Supprime les espaces et caractères non-numériques
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Gère différents formats
        if (strlen($phone) >= 8) {
            // Masque le milieu du numéro
            $visible = 4; // Caractères visibles à la fin
            $start = max(0, strlen($phone) - (4 + $visible));
            return substr($phone, 0, $start) . '****' . substr($phone, -$visible);
        }
        
        return $phone; // Si le numéro est trop court, le retourne tel quel
    }
}
