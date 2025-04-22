<?php

namespace App\Http\Controllers;

use App\Models\QrCode as QrCodeModel;
use App\Models\Prize;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function show($code)
    {
        $qrCode = QrCodeModel::where('code', $code)->firstOrFail();
        $entry = $qrCode->entry;
        
        if (!$entry || !$entry->has_won) {
            abort(404);
        }

        // Utiliser le prix déjà associé à l'entrée ou en attribuer un nouveau si nécessaire
        if ($entry->has_won && !$entry->prize_id) {
            // Première visite: attribution d'un prix
            $prize = Prize::where('stock', '>', 0)
                ->inRandomOrder()
                ->first();

            if ($prize) {
                // Enregistrer le prix dans l'entrée pour les visites futures
                $entry->prize_id = $prize->id;
                $entry->save();
                
                // Décrémenter le stock
                $prize->stock--;
                $prize->save();
            }
        } else {
            // Récupérer le prix déjà attribué lors d'une visite précédente
            $prize = $entry->prize;
        }

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
                    \SimpleSoftwareIO\QrCode::format('png')
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

        return view('qrcode-result', [
            'qrCode' => $qrCode,
            'entry' => $entry,
            'prize' => $prize
        ]);
    }
    
    public function downloadPdf($code)
    {
        $qrCode = QrCodeModel::where('code', $code)->firstOrFail();
        $entry = $qrCode->entry;
        
        if (!$entry || !$entry->has_won) {
            abort(404);
        }
        
        $qrcodeUrl = route('qrcode.result', ['code' => $qrCode->code]);
        $qrcodeImage = base64_encode(QrCode::format('png')->size(300)->generate($qrcodeUrl));
        
        $pdf = PDF::loadView('qrcodes.pdf', [
            'qrCode' => $qrCode,
            'entry' => $entry,
            'prize' => $entry->prize,
            'qrcodeImage' => $qrcodeImage
        ]);
        
        return $pdf->download('qrcode-' . $code . '.pdf');
    }
    
    public function downloadJpg($code)
    {
        $qrCode = QrCodeModel::where('code', $code)->firstOrFail();
        $entry = $qrCode->entry;
        
        if (!$entry || !$entry->has_won) {
            abort(404);
        }
        
        $qrcodeImage = QrCode::format('png')
                        ->size(300)
                        ->margin(1)
                        ->generate(route('qrcode.result', ['code' => $qrCode->code]));
                        
        $headers = [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="qrcode-' . $code . '.png"',
        ];
        
        return response($qrcodeImage)->withHeaders($headers);
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
