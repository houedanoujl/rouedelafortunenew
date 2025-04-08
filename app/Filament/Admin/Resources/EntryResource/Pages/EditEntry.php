<?php

namespace App\Filament\Admin\Resources\EntryResource\Pages;

use App\Filament\Admin\Resources\EntryResource;
use App\Models\PrizeDistribution;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class EditEntry extends EditRecord
{
    protected static string $resource = EntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function afterSave(): void
    {
        // Récupérer les données du formulaire
        $data = $this->data;
        $entry = $this->record;
        $claimedChanged = $entry->isDirty('claimed');
        
        // Si le prix est réclamé, mettre à jour les dates si elles ne sont pas déjà définies
        if ($entry->claimed) {
            $updated = false;
            
            // Si le prix vient d'être réclamé, mettre à jour claimed_at
            if ($claimedChanged) {
                $entry->claimed_at = Carbon::now();
                $updated = true;
            }
            
            // Mettre à jour played_at si non défini
            if (empty($entry->played_at)) {
                $entry->played_at = Carbon::now();
                $updated = true;
            }
            
            // Mettre à jour won_date si non défini et qu'il y a un prix
            if (empty($entry->won_date) && $entry->prize_id) {
                $entry->won_date = Carbon::now();
                $updated = true;
            }
            
            // Si le prix vient d'être réclamé et qu'il y a un prix, mettre à jour le stock
            if ($claimedChanged && $entry->prize_id) {
                $this->updatePrizeStock($entry->contest_id, $entry->prize_id);
            }
            
            // Sauvegarder les modifications si nécessaire
            if ($updated) {
                $entry->saveQuietly(); // Sauvegarder sans déclencher les événements
            }
        }
    }
    
    /**
     * Mettre à jour le stock de prix disponibles
     */
    protected function updatePrizeStock(int $contestId, int $prizeId): void
    {
        // Trouver la distribution de prix correspondante
        $prizeDistribution = PrizeDistribution::where('contest_id', $contestId)
            ->where('prize_id', $prizeId)
            ->first();
            
        if ($prizeDistribution) {
            // Décrémenter le stock
            if ($prizeDistribution->decrementRemaining()) {
                Notification::make()
                    ->title('Stock mis à jour')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Attention: Stock épuisé!')
                    ->warning()
                    ->send();
            }
        }
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
