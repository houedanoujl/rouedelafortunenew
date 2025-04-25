<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Prize;
use Illuminate\Http\Request;
use App\Helpers\TestAccountHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;
use App\Services\GreenWhatsAppService;

class SpinController extends Controller
{
    public function show(Entry $entry, Request $request)
    {
        if (!$entry->participant) {
            abort(404);
        }

        // Vérifier si c'est un compte de test qui peut rejouer sans restriction
        $isTestAccount = false;
        if ($entry->participant && $entry->participant->email) {
            $isTestAccount = TestAccountHelper::isTestAccount($entry->participant->email);
            
            // Stocker dans la session pour l'affichage de la bannière
            if ($isTestAccount) {
                $companyName = TestAccountHelper::getCompanyName($entry->participant->email);
                $request->session()->put('is_test_account', true);
                $request->session()->put('test_account_company', $companyName);
            }
        }
        
        // Si l'entrée a déjà été jouée et que ce n'est PAS un compte de test, rediriger vers la page de résultat
        if ($entry->has_played && !$isTestAccount) {
            return redirect()->route('spin.result', ['entry' => $entry->id]);
        }

        return view('wheel', compact('entry'));
    }

    public function result(Entry $entry, Request $request)
    {
        if (!$entry->participant) {
            abort(404);
        }
        
        // Vérifier si c'est un compte de test
        $isTestAccount = false;
        if ($entry->participant && $entry->participant->email) {
            $isTestAccount = TestAccountHelper::isTestAccount($entry->participant->email);
            
            // Stocker dans la session pour l'affichage de la bannière
            if ($isTestAccount) {
                $companyName = TestAccountHelper::getCompanyName($entry->participant->email);
                $request->session()->put('is_test_account', true);
                $request->session()->put('test_account_company', $companyName);
            }
        }
        
        // Log détaillé pour le débogage du mode test et des informations d'entrée
        Log::debug('SpinController@result - Informations initiales', [
            'entry_id' => $entry->id,
            'participant_id' => $entry->participant->id ?? 'Non défini',
            'email' => $entry->participant->email ?? 'Non défini',
            'phone' => $entry->participant->phone ?? 'Non défini',
            'has_won' => $entry->has_won ? 'Oui' : 'Non',
            'is_test_account' => $isTestAccount ? 'Oui' : 'Non',
            'request_url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // CORRECTION: Toujours vérifier et décrémenter les stocks si l'entrée est gagnante
        if ($entry->has_won) {
            // Vérifier si un prix n'a pas encore été attribué
            if (!$entry->prize_id) {
                $prize = Prize::where('stock', '>', 0)
                    ->inRandomOrder()
                    ->first();
                
                // Si aucun prix n'est disponible en stock, vérifier les comptes de test spéciaux
                if (!$prize && $isTestAccount) {
                    $prize = Prize::first(); // Forcer l'attribution d'un prix pour les comptes test
                }
                
                if ($prize) {
                    // Enregistrer le prix dans l'entrée
                    $entry->prize_id = $prize->id;
                    $entry->save();
                    
                    // Décrémenter le stock
                    Log::info('Décrémentation du stock du prix pour une nouvelle entrée gagnante', [
                        'entry_id' => $entry->id,
                        'prize_id' => $prize->id,
                        'prize_name' => $prize->name,
                        'stock_before' => $prize->stock,
                        'is_test_account' => $isTestAccount ? 'Oui' : 'Non'
                    ]);
                    
                    $prize->stock--;
                    $prize->save();
                    
                    Log::info('Stock du prix décrémenté avec succès', [
                        'prize_id' => $prize->id,
                        'prize_name' => $prize->name,
                        'stock_after' => $prize->stock
                    ]);
                    
                    // AJOUT: Chercher et décrémenter aussi le stock dans la distribution correspondante
                    $distributions = \App\Models\PrizeDistribution::where('prize_id', $prize->id)
                        ->where('contest_id', $entry->contest_id)
                        ->where('remaining', '>', 0)
                        ->get();
                        
                    if ($distributions->isNotEmpty()) {
                        // Prendre la première distribution avec du stock
                        $distribution = $distributions->first();
                        $oldRemaining = $distribution->remaining;
                        
                        if ($distribution->decrementRemaining()) {
                            Log::info('Stock de distribution décrémenté avec succès pour une nouvelle entrée', [
                                'distribution_id' => $distribution->id,
                                'prize_id' => $prize->id,
                                'remaining_before' => $oldRemaining,
                                'remaining_after' => $distribution->remaining
                            ]);
                        }
                    } else {
                        Log::warning('Aucune distribution avec stock disponible pour ce prix', [
                            'prize_id' => $prize->id,
                            'contest_id' => $entry->contest_id
                        ]);
                    }
                }
            } 
            // Si un prix a déjà été attribué mais que le stock n'a pas été décrémenté
            else {
                // Vérifier si le prix existe
                $prize = Prize::find($entry->prize_id);
                
                if ($prize) {
                    // Vérifie si la session indique que le stock a déjà été décrémenté
                    if ($request->session()->get('decrement_stock_' . $entry->id, false)) {
                        Log::info('Pas de décrémentation du stock (déjà fait)', [
                            'entry_id' => $entry->id,
                            'prize_id' => $prize->id,
                            'prize_name' => $prize->name,
                            'current_stock' => $prize->stock,
                            'is_test_account' => $isTestAccount ? 'Oui' : 'Non'
                        ]);
                    } else {
                        // Vérification supplémentaire dans la base de données
                        $stockDecremented = \DB::table('stock_decremented_logs')
                            ->where('entry_id', $entry->id)
                            ->exists();
                            
                        if ($stockDecremented) {
                            Log::info('Pas de décrémentation du stock (déjà fait selon la base de données)', [
                                'entry_id' => $entry->id,
                                'prize_id' => $prize->id,
                                'prize_name' => $prize->name
                            ]);
                            
                            // Mettre à jour la session également
                            $request->session()->put('decrement_stock_' . $entry->id, true);
                        } else {
                            // Stock pas encore décrémenté pour cette entrée
                            Log::info('Décrémentation automatique du stock pour une entrée existante', [
                                'entry_id' => $entry->id,
                                'prize_id' => $prize->id,
                                'prize_name' => $prize->name,
                                'stock_before' => $prize->stock,
                                'is_test_account' => $isTestAccount ? 'Oui' : 'Non'
                            ]);
                            
                            $prize->stock--;
                            $prize->save();
                            
                            // Marquer dans la session que le stock a été décrémenté pour cette entrée
                            $request->session()->put('decrement_stock_' . $entry->id, true);
                            
                            Log::info('Stock du prix décrémenté avec succès (première visite après attribution)', [
                                'prize_id' => $prize->id,
                                'prize_name' => $prize->name,
                                'stock_after' => $prize->stock
                            ]);
                            
                            // AJOUT: Chercher et décrémenter aussi le stock dans la distribution correspondante
                            $distributions = \App\Models\PrizeDistribution::where('prize_id', $prize->id)
                                ->where('contest_id', $entry->contest_id)
                                ->where('remaining', '>', 0)
                                ->get();
                                
                            if ($distributions->isNotEmpty()) {
                                // Prendre la première distribution avec du stock
                                $distribution = $distributions->first();
                                $oldRemaining = $distribution->remaining;
                                
                                if ($distribution->decrementRemaining()) {
                                    Log::info('Stock de distribution décrémenté avec succès (première visite après attribution)', [
                                        'distribution_id' => $distribution->id,
                                        'prize_id' => $prize->id, 
                                        'remaining_before' => $oldRemaining,
                                        'remaining_after' => $distribution->remaining
                                    ]);
                                }
                            } else {
                                Log::warning('Aucune distribution avec stock disponible pour ce prix (première visite après attribution)', [
                                    'prize_id' => $prize->id,
                                    'contest_id' => $entry->contest_id
                                ]);
                            }
                            
                            // Enregistrer dans la base de données que le stock a été décrémenté
                            \DB::table('stock_decremented_logs')->insert([
                                'entry_id' => $entry->id,
                                'prize_id' => $prize->id,
                                'decremented_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                } else {
                    Log::warning('Prix non trouvé pour l\'entrée gagnante', [
                        'entry_id' => $entry->id,
                        'prize_id' => $entry->prize_id
                    ]);
                }
            }
        } else {
            Log::info('Entrée non gagnante, pas de décrémentation de stock', [
                'entry_id' => $entry->id,
                'has_won' => 'Non'
            ]);
        }

        // Récupérer ou créer le QR code
        $qrCode = $entry->qrCode;
        
        // Initialiser la variable $prize pour éviter l'erreur
        $prize = $entry->prize;
        
        // ENVOI WHATSAPP SI GAGNANT ET NUMÉRO DISPONIBLE
        $whatsappMsg = null;
        try {
            // Vérifier si participant existe avec un numéro de téléphone valide
            if ($entry->participant && $entry->participant->phone) {
                $formattedPhone = $this->formatPhoneDisplay($entry->participant->phone);
                
                // Envoyer des messages WhatsApp UNIQUEMENT aux gagnants
                // Même pour les comptes test, on respecte la règle d'envoyer uniquement en cas de victoire
                if ($entry->has_won) {
                    $qrCodeDir = storage_path('app/public/qrcodes');
                    // Créer le répertoire s'il n'existe pas
                    if (!file_exists($qrCodeDir)) {
                        mkdir($qrCodeDir, 0755, true);
                    }
                    
                    // S'assurer que le QR code existe
                    if (!$qrCode) {
                        $code = 'QR-' . \Illuminate\Support\Str::random(8);
                        $qrCode = \App\Models\QrCode::create([
                            'code' => $code,
                            'entry_id' => $entry->id
                        ]);
                        
                        Log::info('Nouveau QR code généré pour envoi WhatsApp', [
                            'code' => $code,
                            'entry_id' => $entry->id,
                            'is_test_account' => $isTestAccount ? 'Oui' : 'Non'
                        ]);
                    }
                    
                    $qrCodePath = $qrCodeDir . '/qrcode-' . $qrCode->code . '.png';
                    // Génère le QR code s'il n'existe pas déjà
                    if (!file_exists($qrCodePath)) {
                        QrCode::format('png')
                            ->size(300)
                            ->margin(1)
                            ->generate(route('qrcode.result', ['code' => $qrCode->code]), $qrCodePath);
                    }
                    
                    // Logs détaillés avant tentative d'envoi
                    Log::debug('Avant envoi WhatsApp à '.$formattedPhone, [
                        'entry_id' => $entry->id,
                        'participant_id' => $entry->participant->id,
                        'phone_raw' => $entry->participant->phone,
                        'has_won' => 'Oui',
                        'is_test_account' => $isTestAccount ? 'Oui' : 'Non',
                        'qr_code' => $qrCode->code,
                        'qr_path' => $qrCodePath,
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    
                    // Utiliser Green API pour l'envoi WhatsApp
                    $greenWhatsAppService = new GreenWhatsAppService();
                    
                    // Message pour les gagnants
                    $prizeText = $entry->prize ? $entry->prize->name : "un prix";
                    $customMessage = "Félicitations ! Vous avez gagné {$prizeText}. Voici votre QR code pour récupérer votre gain. Conservez-le précieusement !";
                    
                    // Envoyer le message à chaque affichage/rafraîchissement
                    $result = $greenWhatsAppService->sendQrCodeToWinner($entry->participant->phone, $qrCodePath, $customMessage);
                    
                    Log::info('Résultat envoi WhatsApp via Green API', [
                        'phone' => $formattedPhone, 
                        'result' => is_string($result) ? $result : ($result ? 'Succès' : 'Échec'),
                        'is_test_account' => $isTestAccount ? 'Oui' : 'Non',
                        'has_won' => 'Oui',
                        'page' => 'spin/result',
                        'timestamp' => now()->toDateTimeString()
                    ]);
                    
                    // Message pour l'interface utilisateur
                    $whatsappMsg = is_string($result) 
                        ? "Échec d'envoi WhatsApp à {$formattedPhone} : {$result}" 
                        : ($result ? "Succès: message WhatsApp envoyé à {$formattedPhone}." : "Échec: message WhatsApp non envoyé à {$formattedPhone}.");
                } else {
                    // Utilisateur qui n'a pas gagné - Pas d'envoi WhatsApp
                    Log::info('Pas d\'envoi WhatsApp - utilisateur non gagnant', [
                        'entry_id' => $entry->id,
                        'phone' => $formattedPhone,
                        'has_won' => 'Non',
                        'is_test_account' => $isTestAccount ? 'Oui' : 'Non'
                    ]);
                }
            } else {
                // Cas où le participant ou son numéro n'existe pas
                Log::warning('Impossible d\'envoyer un message WhatsApp - participant ou numéro manquant', [
                    'entry_id' => $entry->id,
                    'participant_exists' => $entry->participant ? 'Oui' : 'Non',
                    'phone_exists' => ($entry->participant && $entry->participant->phone) ? 'Oui' : 'Non'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi WhatsApp', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'entry_id' => $entry->id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        
        if ($whatsappMsg) {
            session()->flash('whatsapp_result', $whatsappMsg);
        }

        // Générer une clé unique pour le localStorage (à des fins de vérification côté client)
        $localStorageKey = 'contest_played_' . $entry->contest_id;
        $request->session()->put('localStorageKey', $localStorageKey);

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
