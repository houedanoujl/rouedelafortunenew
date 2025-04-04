<?php

namespace App\Http\Controllers;

use App\Models\QrCode as QrCodeModel;
use App\Models\Prize;
use Illuminate\Http\Request;
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
}
