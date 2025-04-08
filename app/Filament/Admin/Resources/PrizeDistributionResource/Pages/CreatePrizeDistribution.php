<?php

namespace App\Filament\Admin\Resources\PrizeDistributionResource\Pages;

use App\Filament\Admin\Resources\PrizeDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePrizeDistribution extends CreateRecord
{
    protected static string $resource = PrizeDistributionResource::class;
    
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
