<?php

namespace App\Filament\Pages;

use App\Models\Contest;
use App\Models\Entry;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WinnersList extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Liste des gagnants';
    protected static ?string $navigationGroup = 'Rapports';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Liste des gagnants';

    protected static string $view = 'filament.pages.winners-list';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Entry::query()
                    ->where('has_won', true)
                    ->with(['participant', 'contest', 'prize', 'qrCode'])
            )
            ->defaultGroup('contest_id')
            ->groups([
                Group::make('contest_id')
                    ->label('Concours')
                    ->getTitleFromRecordUsing(fn (Entry $record): string => $record->contest->name ?? 'Concours inconnu')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('contest.name')
                    ->label('Concours')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('participant.first_name')
                    ->label('Prénom')
                    ->searchable(),
                    
                TextColumn::make('participant.last_name')
                    ->label('Nom')
                    ->searchable(),
                    
                TextColumn::make('participant.phone')
                    ->label('Téléphone')
                    ->searchable(),
                    
                TextColumn::make('participant.email')
                    ->label('Email')
                    ->searchable(),
                    
                TextColumn::make('prize.name')
                    ->label('Lot gagné')
                    ->badge()
                    ->color('success')
                    ->searchable(),
                    
                TextColumn::make('prize.value')
                    ->label('Valeur')
                    ->money('EUR')
                    ->sortable(),
                    
                TextColumn::make('qrCode.code')
                    ->label('Code QR')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Code QR copié!'),
                    
                TextColumn::make('qrCode.scanned')
                    ->label('Scanné')
                    ->badge()
                    ->color(fn (?bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (?bool $state): string => $state ? 'Oui' : 'Non'),
                    
                TextColumn::make('claimed')
                    ->label('Réclamé')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non'),
                    
                TextColumn::make('created_at')
                    ->label('Date de gain')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contest_id')
                    ->label('Concours')
                    ->relationship('contest', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('claimed')
                    ->label('Réclamé')
                    ->query(fn (Builder $query): Builder => $query->where('claimed', true))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('not_claimed')
                    ->label('Non réclamé')
                    ->query(fn (Builder $query): Builder => $query->where('claimed', false))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('created_at')
                    ->label('Date de gain')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Depuis'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('voir_details')
                    ->label('Détails')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn (Entry $record) => view('filament.modals.entry-details', ['entry' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_csv')
                    ->label('Exporter en CSV')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (): string => route('admin.winners.export-csv'))
                    ->openUrlInNewTab(false)
            ]);
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Actualiser')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->refreshTable()),
        ];
    }
}
