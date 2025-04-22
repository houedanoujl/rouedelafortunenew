<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Prize;
use Illuminate\Http\Request;
use App\Helpers\TestAccountHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

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
            if ($entry->has_won && $entry->participant && $entry->participant->phone && $qrCode) {
                $qrCodeDir = storage_path('app/public/qrcodes');
                // Créer le répertoire s'il n'existe pas
                if (!file_exists($qrCodeDir)) {
                    mkdir($qrCodeDir, 0755, true);
                }
                
                $qrCodePath = $qrCodeDir . '/qrcode-' . $qrCode->code . '.png';
                // Génère le QR code s'il n'existe pas déjà
                if (!file_exists($qrCodePath)) {
                    QrCode::format('png')
                        ->size(300)
                        ->margin(1)
                        ->generate(route('qrcode.result', ['code' => $qrCode->code]), $qrCodePath);
                }
                $whatsappService = app(WhatsAppService::class);
                $formattedPhone = $this->formatPhoneDisplay($entry->participant->phone);
                $result = $whatsappService->sendQrCodeToWinner($entry->participant->phone, $qrCodePath);
                Log::info('Envoi WhatsApp tenté', ['phone' => $formattedPhone, 'result' => $result]);
                // Message détaillé pour l'alerte
                $whatsappMsg = is_string($result) ? "Échec d'envoi à $formattedPhone : $result" : ($result ? "Succès: message WhatsApp envoyé à $formattedPhone." : "Échec: message WhatsApp non envoyé à $formattedPhone.");
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi WhatsApp', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $whatsappMsg = 'Échec: ' . $e->getMessage();
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
