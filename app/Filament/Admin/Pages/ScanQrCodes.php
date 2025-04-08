<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Facades\FilamentIcon;

class ScanQrCodes extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    
    protected static ?string $navigationLabel = 'Scanner QR Code';
    
    protected static ?string $title = 'Scanner des QR Codes';
    
    protected static ?string $navigationGroup = 'Gestion des QR Codes';
    
    protected static ?int $navigationSort = 90;
    
    protected static string $view = 'filament.admin.pages.scan-qr-codes';
    
    protected ?string $heading = 'Scanner des QR Codes';
    
    protected static ?string $slug = 'qr-codes/scanner';
    
    public static function getNavigationBadge(): ?string
    {
        return 'NEW';
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
