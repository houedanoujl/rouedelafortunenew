<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use App\Services\GreenWhatsAppService;
use App\Helpers\WhatsAppLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class WhatsAppDocumentController extends Controller
{
    /**
     * Envoyer le QR code et le PDF au gagnant via WhatsApp
     */
    public function sendDocuments(Request $request, Entry $entry)
    {
        // Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }
        
        // Vérifier si l'entrée est gagnante
        if (!$entry->has_won) {
            return redirect()->back()->with('error', 'Cette participation n\'est pas gagnante');
        }
        
        // Vérifier si le participant a un numéro de téléphone
        if (!$entry->participant || !$entry->participant->phone) {
            return redirect()->back()->with('error', 'Le participant n\'a pas de numéro de téléphone');
        }
        
        try {
            // Service WhatsApp
            $whatsAppService = new GreenWhatsAppService();
            $participant = $entry->participant;
            
            // 1. Récupérer/Générer le QR code
            if (!$entry->qr_code) {
                $code = 'DNR70-' . strtoupper(substr(md5($entry->id . time()), 0, 8));
                $qrCodeModel = \App\Models\QrCode::create([
                    'entry_id' => $entry->id,
                    'code' => $code,
                ]);
                
                $entry->qr_code = $code;
                $entry->save();
            } else {
                $qrCodeModel = \App\Models\QrCode::where('code', $entry->qr_code)
                    ->orWhere('entry_id', $entry->id)
                    ->first();
                
                if (!$qrCodeModel) {
                    $code = $entry->qr_code;
                    $qrCodeModel = \App\Models\QrCode::create([
                        'entry_id' => $entry->id,
                        'code' => $code,
                    ]);
                }
            }
            
            // 2. Générer l'image du QR code
            $qrcodePath = storage_path('app/public/qrcodes/qrcode-' . $qrCodeModel->code . '.png');
            if (!file_exists($qrcodePath)) {
                $qrCodeImage = QrCode::size(300)
                    ->format('png')
                    ->generate($qrCodeModel->code);
                    
                if (!file_exists(dirname($qrcodePath))) {
                    mkdir(dirname($qrcodePath), 0755, true);
                }
                file_put_contents($qrcodePath, $qrCodeImage);
            }
            
            // 3. Générer le PDF
            $qrcodeUrl = route('qrcode.result', ['code' => $qrCodeModel->code]);
            $qrcodeImage = base64_encode(QrCode::format('png')->size(300)->generate($qrcodeUrl));
            
            // Chemin du logo
            $logoPath = public_path('images/logo.png');
            $logoBase64 = null;
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
            
            $pdf = PDF::loadView('qrcodes.pdf', [
                'qrCode' => $qrCodeModel,
                'entry' => $entry,
                'prize' => $entry->prize,
                'qrcodeImage' => $qrcodeImage,
                'logoPath' => $logoBase64
            ]);
            
            $pdfPath = storage_path('app/public/pdfs/qrcode-' . $qrCodeModel->code . '.pdf');
            if (!file_exists(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }
            $pdf->save($pdfPath);
            
            // 4. Envoyer le message avec QR code
            $prizeText = $entry->prize ? $entry->prize->name : "un lot";
            $message = "Félicitations {$participant->first_name}! Voici votre QR code pour récupérer votre gain : {$prizeText}.\n\n" . 
                     "Numéro du QR code : {$qrCodeModel->code}\n\n" .
                     "Pour le retrait de votre lot, contactez le 07 19 04 87 28";
            
            $qrCodeResult = $whatsAppService->sendQrCodeToWinner($participant->phone, $qrcodePath, $message);
            
            // 5. Envoyer le PDF
            $pdfResult = $this->sendPdfFile($whatsAppService, $participant->phone, $pdfPath, $qrCodeModel->code);
            
            // 6. Log dans la base
            WhatsAppLogger::success($participant->phone, "QR Code et PDF envoyés par l'administrateur", [
                'entry_id' => $entry->id,
                'qr_code' => $qrCodeModel->code,
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'Inconnu'
            ]);
            
            // 7. Retourner le résultat
            return redirect()->back()->with('success', 
                "QR code et PDF envoyés avec succès à {$participant->first_name} {$participant->last_name} ({$participant->phone})");
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des documents par WhatsApp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'entry_id' => $entry->id
            ]);
            
            return redirect()->back()->with('error', 
                "Erreur lors de l'envoi des documents: {$e->getMessage()}");
        }
    }
    
    /**
     * Envoyer un fichier PDF via WhatsApp
     */
    protected function sendPdfFile(GreenWhatsAppService $whatsAppService, string $phone, string $pdfPath, string $code)
    {
        // Construire un client HTTP pour faire la requête à l'API
        $client = new \GuzzleHttp\Client();
        
        // Récupérer les informations d'API depuis la configuration
        $idInstance = config('services.greenapi.id_instance');
        $apiTokenInstance = config('services.greenapi.api_token');
        $apiUrl = config('services.greenapi.api_url');
        
        if (!$idInstance || !$apiTokenInstance || !$apiUrl) {
            throw new \Exception('Configuration Green API incomplète');
        }
        
        // Formater le numéro de téléphone
        $phone = $whatsAppService->formatPhoneNumber($phone);
        $chatIdNumber = ltrim($phone, '+');
        $formattedChatId = $chatIdNumber . '@c.us';
        
        // URL pour envoyer un fichier
        $sendFileUrl = "{$apiUrl}/waInstance{$idInstance}/sendFileByUpload/{$apiTokenInstance}";
        
        // Lire le fichier PDF
        $fileContents = file_get_contents($pdfPath);
        $filename = "qrcode-{$code}.pdf";
        
        // Multipart request pour l'envoi du fichier
        $multipart = [
            [
                'name' => 'chatId',
                'contents' => $formattedChatId
            ],
            [
                'name' => 'caption',
                'contents' => 'Voici votre PDF de QR code'
            ],
            [
                'name' => 'file',
                'contents' => $fileContents,
                'filename' => $filename,
                'headers' => [
                    'Content-Type' => 'application/pdf',
                ]
            ]
        ];
        
        // Faire la requête
        $response = $client->post($sendFileUrl, [
            'multipart' => $multipart
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        if (!isset($result['idMessage'])) {
            throw new \Exception('Échec de l\'envoi du PDF via WhatsApp: ' . json_encode($result));
        }
        
        return true;
    }
}
