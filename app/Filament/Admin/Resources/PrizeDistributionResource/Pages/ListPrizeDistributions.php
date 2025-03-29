<?php

namespace App\Filament\Admin\Resources\PrizeDistributionResource\Pages;

use App\Filament\Admin\Resources\PrizeDistributionResource;
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
