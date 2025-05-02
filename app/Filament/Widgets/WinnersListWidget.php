<?php

namespace App\Filament\Widgets;

use App\Models\Entry;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class WinnersListWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?string $heading = 'Derniers gagnants';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Entry::query()
                    ->where('has_won', true)
                    ->whereNotNull('prize_id')
                    ->with(['participant', 'contest', 'prize', 'qrCode'])
                    ->latest('won_date')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('participant.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('participant.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('contest.name')
                    ->label('Concours')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('prize.name')
                    ->label('Prix gagné')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('won_date')
                    ->label('Date de gain')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                TextColumn::make('qrCode.scanned')
                    ->label('QR Code scanné')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Scanné' : 'Non scanné')
                    ->color(fn ($state): string => $state ? 'success' : 'warning'),
                    
                TextColumn::make('claimed')
                    ->label('Réclamé')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Oui' : 'Non')
                    ->color(fn ($state): string => $state ? 'success' : 'danger'),
            ])
            ->filters([
                // Pas besoin de filtres pour ce widget simplifié
            ])
            ->actions([
                // Pas besoin d'actions pour ce widget simplifié
            ])
            ->bulkActions([
                // Pas besoin d'actions groupées pour ce widget simplifié
            ]);
    }
}
