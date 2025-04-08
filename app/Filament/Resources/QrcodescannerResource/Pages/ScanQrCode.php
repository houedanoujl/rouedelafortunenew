<?php

namespace App\Filament\Resources\QrcodescannerResource\Pages;

use App\Filament\Resources\QrcodescannerResource;
use Filament\Resources\Pages\Page;

class ScanQrCode extends Page
{
    protected static string $resource = QrcodescannerResource::class;

    protected static string $view = 'filament.resources.qrcodescanner-resource.pages.scan-qr-code';
}
