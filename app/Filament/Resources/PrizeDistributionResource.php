<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrizeDistributionResource\Pages;
use App\Filament\Resources\PrizeDistributionResource\RelationManagers;
use App\Models\PrizeDistribution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PrizeDistributionResource extends Resource
{
    protected static ?string $model = PrizeDistribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationLabel = 'Distributions de prix';
    protected static ?string $modelLabel = 'Distribution de prix';
    protected static ?string $pluralModelLabel = 'Distributions de prix';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations concours')
                    ->description('Les dates de distribution doivent être dans les limites du concours')
                    ->schema([
                        Forms\Components\Placeholder::make('contest_dates')
                            ->label('Dates du concours sélectionné')
                            ->content(function (callable $get) {
                                $contestId = $get('contest_id');
                                if (!$contestId) {
                                    return 'Sélectionnez un concours pour voir ses dates';
                                }
                                
                                $contest = \App\Models\Contest::find($contestId);
                                if (!$contest) {
                                    return 'Aucune information disponible';
                                }
                                
                                return sprintf(
                                    'Du %s au %s', 
                                    $contest->start_date->format('d/m/Y H:i'), 
                                    $contest->end_date->format('d/m/Y H:i')
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->persistCollapsed(false),
                Forms\Components\Select::make('contest_id')
                    ->relationship('contest', 'name')
                    ->label('Concours')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn () => $form->fill()),
                Forms\Components\Select::make('prize_id')
                    ->relationship('prize', 'name')
                    ->label('Prix')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantité')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\DateTimePicker::make('start_date')
                    ->label('Date de début')
                    ->required()
                    ->default(now())
                    ->reactive()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Si date de début est définie, automatiquement calculer fin à J+1 même heure
                        if ($state) {
                            $endDate = \Carbon\Carbon::parse($state)->addDay();
                            
                            // Vérifier si la date de fin calculée dépasse la fin du concours
                            $contestId = $get('contest_id');
                            if ($contestId) {
                                $contest = \App\Models\Contest::find($contestId);
                                if ($contest && $endDate->gt($contest->end_date)) {
                                    // Si la date calculée dépasse la fin du concours, utiliser la fin du concours
                                    $endDate = $contest->end_date;
                                }
                            }
                            
                            $set('end_date', $endDate);
                        }
                    })
                    ->rules([
                        function (Forms\Get $get, $state) {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $state) {
                                $contestId = $get('contest_id');
                                if (!$contestId || !$value) return;
                                
                                $contest = \App\Models\Contest::find($contestId);
                                if (!$contest) return;
                                
                                $startDate = \Carbon\Carbon::parse($value);
                                if ($startDate->lt($contest->start_date)) {
                                    $fail("La distribution ne peut pas commencer avant le début du concours ({$contest->start_date->format('d/m/Y H:i:s')}).");
                                }
                            };
                        },
                    ]),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('Date de fin (auto-calculée: début + 24h)')
                    ->required()
                    ->default(now()->addDay())
                    ->disabled() // Désactiver la modification manuelle
                    ->dehydrated(true) // Mais toujours envoyer la valeur au serveur
                    ->helperText('Cette valeur est calculée automatiquement (24h après la date de début)')
                    ->id('end_date'),
                Forms\Components\TextInput::make('remaining')
                    ->label('Quantité restante')
                    ->helperText('Laissez vide pour initialiser avec la quantité totale')
                    ->numeric(),
            ]);
        
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contest.name')
                    ->label('Concours')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prize.name')
                    ->label('Prix')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantité')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining')
                    ->label('Restant')
                    ->numeric()
                    ->sortable()
                    ->color(fn (PrizeDistribution $record): string => 
                        $record->remaining <= 0 ? 'danger' : 
                        ($record->remaining < $record->quantity * 0.2 ? 'warning' : 'success')
                    ),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->getStateUsing(fn (PrizeDistribution $record): bool => 
                        $record->remaining > 0 && 
                        $record->start_date <= now() && 
                        $record->end_date >= now()
                    )
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contest')
                    ->relationship('contest', 'name')
                    ->label('Concours'),
                Tables\Filters\Filter::make('is_active')
                    ->label('Actif')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('remaining', '>', 0)
                            ->where('start_date', '<=', now())
                            ->where('end_date', '>=', now())
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifier'),
                Tables\Actions\Action::make('reset')
                    ->label('Réinitialiser')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (PrizeDistribution $record) {
                        $record->remaining = $record->quantity;
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer'),
                    Tables\Actions\BulkAction::make('resetStock')
                        ->label('Réinitialiser les stocks')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->remaining = $record->quantity;
                                $record->save();
                            }
                        }),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exporter en CSV')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(date('Y-m-d') . '-distributions')
                            ->withWriterType(\Maatwebsite\Excel\Excel::CSV)
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrizeDistributions::route('/'),
            'create' => Pages\CreatePrizeDistribution::route('/create'),
            'edit' => Pages\EditPrizeDistribution::route('/{record}/edit'),
        ];
    }
}
