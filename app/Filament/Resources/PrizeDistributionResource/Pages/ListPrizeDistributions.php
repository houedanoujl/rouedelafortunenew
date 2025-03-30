<?php

namespace App\Filament\Resources\PrizeDistributionResource\Pages;

use App\Filament\Resources\PrizeDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrizeDistributions extends ListRecords
{
    protected static string $resource = PrizeDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
