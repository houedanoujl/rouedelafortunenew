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

        // Récupérer le prix associé
        $prize = Prize::where('stock', '>', 0)
            ->inRandomOrder()
            ->first();

        if ($prize) {
            $prize->stock--;
            $prize->save();
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
