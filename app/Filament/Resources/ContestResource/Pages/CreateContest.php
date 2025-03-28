<?php

namespace App\Filament\Resources\ContestResource\Pages;

use App\Filament\Resources\ContestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContest extends CreateRecord
{
    protected static string $resource = ContestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
