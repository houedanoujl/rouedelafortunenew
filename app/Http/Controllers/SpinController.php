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
        
        // Vérifier si la page est bien celle d'une entrée gagnante avec QR Code
        if ($entry->has_won && !$entry->prize_id) {
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
                $prize->stock--;
                $prize->save();
            }
        } else {
            // Utiliser le prix déjà associé à l'entrée
            $prize = $entry->prize;
        }

        // Récupérer ou créer le QR code
        $qrCode = $entry->qrCode;
        
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
                    $prizeText = $prize ? $prize->name : "un prix";
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
