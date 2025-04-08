<?php

namespace App\Filament\Admin\Resources\PrizeDistributionResource\Pages;

use App\Filament\Admin\Resources\PrizeDistributionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrizeDistribution extends EditRecord
{
    protected static string $resource = PrizeDistributionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    /**
     * Rediriger vers la liste après sauvegarde
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    /**
     * Assure la redirection même si getRedirectUrl n'est pas appelé
     */
    protected function afterSave(): void
    {
        parent::afterSave();
        // Rediriger vers la liste après la sauvegarde
        $this->redirect($this->getResource()::getUrl('index'));
    }
}
