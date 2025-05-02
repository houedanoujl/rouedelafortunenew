<?php

namespace App\Filament\Widgets;

use App\Models\Entry;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestParticipations extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Entry::query()
                    ->with(['participant', 'contest', 'prize'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('participant.first_name')
                    ->label('Prénom')
                    ->searchable(),
                    
                TextColumn::make('participant.last_name')
                    ->label('Nom')
                    ->searchable(),
                    
                TextColumn::make('participant.phone')
                    ->label('Téléphone')
                    ->searchable(),
                    
                TextColumn::make('contest.name')
                    ->label('Concours')
                    ->searchable(),
                    
                TextColumn::make('has_won')
                    ->label('Résultat')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Gagné' : 'Perdu')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    
                TextColumn::make('prize.name')
                    ->label('Prix')
                    ->placeholder('-')
                    ->searchable(),
                    
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                // Aucune action nécessaire pour cette vue simplifiée
            ]);
    }
}
