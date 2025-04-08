<?php

namespace App\Filament\Admin\Resources\EntryResource\Pages;

use App\Filament\Admin\Resources\EntryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEntry extends CreateRecord
{
    protected static string $resource = EntryResource::class;
    
    /**
     * Rediriger vers la liste après création
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    /**
     * Assure la redirection même si getRedirectUrl n'est pas appelé
     */
    protected function afterCreate(): void
    {
        parent::afterCreate();
        // Rediriger vers la liste après la création
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
