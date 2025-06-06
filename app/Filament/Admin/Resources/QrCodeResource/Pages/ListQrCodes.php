<?php

namespace App\Filament\Admin\Resources\QrCodeResource\Pages;

use App\Filament\Admin\Resources\QrCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQrCodes extends ListRecords
{
    protected static string $resource = QrCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
