<?php

namespace App\Filament\Admin\Resources\ContestResource\Pages;

use App\Filament\Admin\Resources\ContestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContests extends ListRecords
{
    protected static string $resource = ContestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
