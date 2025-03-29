<?php

namespace App\Filament\Admin\Resources\PrizeResource\Pages;

use App\Filament\Admin\Resources\PrizeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrize extends EditRecord
{
    protected static string $resource = PrizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
