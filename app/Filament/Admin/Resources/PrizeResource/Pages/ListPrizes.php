<?php

namespace App\Filament\Admin\Resources\PrizeResource\Pages;

use App\Filament\Admin\Resources\PrizeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrizes extends ListRecords
{
    protected static string $resource = PrizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
