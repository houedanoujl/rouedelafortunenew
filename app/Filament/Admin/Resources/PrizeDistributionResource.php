<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PrizeDistributionResource\Pages;
use App\Filament\Admin\Resources\PrizeDistributionResource\RelationManagers;
use App\Models\PrizeDistribution;
use App\Models\Contest;
use App\Models\Prize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PrizeDistributionResource extends Resource
{
    protected static ?string $model = PrizeDistribution::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    
    protected static ?string $navigationLabel = 'Distributions de prix';
    
    protected static ?string $navigationGroup = 'Gestion des concours';

    public static function form(Form $form): Form
    {
        // Suppression du test fatal, ce fichier n'est pas utilisé.
        Log::info('[PRIZE FORM] Chargement du formulaire de distribution de prix');
        $contests = Contest::orderBy('created_at', 'desc')->get();
        $prizes = Prize::all();
        Log::info('[PRIZE FORM] Concours disponibles :', $contests->pluck('id', 'name')->toArray());
        Log::info('[PRIZE FORM] Prix disponibles :', $prizes->pluck('id', 'name')->toArray());
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la distribution')
                    ->schema([
                        Forms\Components\Radio::make('contest_id')
                            ->label('Concours')
                            ->options($contests->mapWithKeys(function ($contest) {
                                $label = $contest->name;
                                if ($contest->status === 'active') {
                                    $label .= ' [ACTIF]';
                                }
                                return [$contest->id => $label];
                            }))
                            ->default(function() use ($contests) {
                                $latest = $contests->first();
                                return $latest ? $latest->id : null;
                            })
                            ->required()
                            ->inline()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                Log::info('[PRIZE FORM] Sélection concours radio changé : ' . $state);
                                if ($state) {
                                    $contest = Contest::find($state);
                                    if ($contest) {
                                        $set('start_date', $contest->start_date);
                                        $set('end_date', $contest->end_date);
                                    }
                                }
                            }),
                        Forms\Components\Placeholder::make('contest_dates')
                            ->label('Dates du concours sélectionné')
                            ->content(function (Forms\Get $get) {
                                $contestId = $get('contest_id');
                                Log::info('[PRIZE FORM] Placeholder dates, concours sélectionné : ' . print_r($contestId, true));
                                if (!$contestId) {
                                    return 'Sélectionnez un concours pour voir ses dates';
                                }
                                $contest = Contest::find($contestId);
                                if (!$contest) {
                                    return 'Concours non trouvé';
                                }
                                return 'Du ' . ($contest->start_date ? $contest->start_date->format('d/m/Y H:i') : '?') . 
                                       ' au ' . ($contest->end_date ? $contest->end_date->format('d/m/Y H:i') : '?');
                            })
                            ->hidden(fn (Forms\Get $get) => ! $get('contest_id')),
                        Forms\Components\Radio::make('prize_id')
                            ->label('Prix')
                            ->options($prizes->mapWithKeys(function ($prize) {
                                $label = $prize->name;
                                $label .= ' (Stock: ' . $prize->stock . ')';
                                return [$prize->id => $label];
                            }))
                            ->descriptions(function () use ($prizes) {
                                $descriptions = [];
                                foreach ($prizes as $prize) {
                                    $descriptions[$prize->id] = $prize->description ?? 'Aucune description';
                                }
                                return $descriptions;
                            })
                            ->required()
                            ->inline(),
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantité totale')
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                    
                                Forms\Components\TextInput::make('remaining')
                                    ->label('Quantité restante')
                                    ->numeric()
                                    ->helperText('Si laissé vide, sera défini automatiquement à la quantité totale'),
                            ])
                            ->columns(2),
                            
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DateTimePicker::make('start_date')
                                    ->label('Date de début')
                                    ->required(),
                                    
                                Forms\Components\DateTimePicker::make('end_date')
                                    ->label('Date de fin')
                                    ->required()
                                    ->afterOrEqual('start_date'),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s') // Rafraîchir toutes les 10 secondes
            // Ajouter le groupement par concours avec affichage en accordéon
            ->groups([
                'contest.name',
            ])
            ->defaultGroup('contest.name')
            ->columns([
                // Informations générales
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                // Informations du concours
                Tables\Columns::make([
                    Tables\Columns\TextColumn::make('contest.name')
                        ->label('Concours')
                        ->sortable()
                        ->searchable()
                        ->weight(function (PrizeDistribution $record) {
                            // Mettre en gras les concours actifs
                            return $record->contest && $record->contest->status === 'active'
                                ? 'bold'
                                : 'normal';
                        }),
                        
                    Tables\Columns\IconColumn::make('contest.status')
                        ->label('Statut du concours')
                        ->options([
                            'active' => 'heroicon-o-play',
                            'scheduled' => 'heroicon-o-clock',
                            'completed' => 'heroicon-o-check',
                            'cancelled' => 'heroicon-o-x-mark',
                        ])
                        ->colors([
                            'active' => 'success',
                            'scheduled' => 'warning',
                            'completed' => 'gray',
                            'cancelled' => 'danger',
                        ])
                        ->tooltip(fn (PrizeDistribution $record): string => Str::title($record->contest->status ?? 'Inconnu')),
                ]),
                
                // Informations du prix
                Tables\Columns::make([
                    Tables\Columns\TextColumn::make('prize.name')
                        ->label('Prix')
                        ->sortable()
                        ->searchable(),
                        
                    Tables\Columns\TextColumn::make('prize.value')
                        ->label('Valeur')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
                
                // Informations de la distribution
                Tables\Columns::make([
                    Tables\Columns\TextColumn::make('quantity')
                        ->label('Quantité totale')
                        ->sortable(),
                        
                    Tables\Columns\TextColumn::make('remaining')
                        ->label('Restant')
                        ->sortable()
                        ->badge()
                        ->color(fn (PrizeDistribution $record): string => 
                            $record->remaining <= 0 
                                ? 'danger' 
                                : ($record->remaining < $record->quantity * 0.2 
                                    ? 'warning' 
                                    : 'success')
                        ),
                ]),
                
                // Dates
                Tables\Columns::make([
                    Tables\Columns\TextColumn::make('start_date')
                        ->label('Début')
                        ->date('d/m/Y')
                        ->sortable(),
                        
                    Tables\Columns\TextColumn::make('end_date')
                        ->label('Fin')
                        ->date('d/m/Y')
                        ->sortable(),
                ]),
                
                // Statut par rapport à la date actuelle
                Tables\Columns::make([
                    Tables\Columns\IconColumn::make('status')
                        ->label('Statut')
                        ->state(function (PrizeDistribution $record) {
                            $now = now();
                            if ($now->lt($record->start_date)) return 'upcoming';
                            if ($now->gt($record->end_date)) return 'expired';
                            if ($record->remaining <= 0) return 'out_of_stock';
                            return 'active';
                        })
                        ->options([
                            'active' => 'heroicon-o-check-circle',
                            'upcoming' => 'heroicon-o-clock',
                            'expired' => 'heroicon-o-x-circle',
                            'out_of_stock' => 'heroicon-o-exclamation-triangle',
                        ])
                        ->colors([
                            'active' => 'success',
                            'upcoming' => 'info',
                            'expired' => 'danger',
                            'out_of_stock' => 'warning',
                        ])
                ]),
            ])
            ->filters([
                Tables\Filters::make([
                    Tables\Filters\SelectFilter::make('contest_id')
                        ->label('Concours')
                        ->options(function () {
                            // Récupérer tous les concours avec un marqueur pour les actifs
                            return Contest::all()->mapWithKeys(function ($contest) {
                                $label = $contest->name;
                                if ($contest->status === 'active') {
                                    $label .= ' [ACTIF]';
                                }
                                return [$contest->id => $label];
                            });
                        }),
                        
                    Tables\Filters\SelectFilter::make('contest_status')
                        ->label('Statut du concours')
                        ->options([
                            'active' => 'Actif',
                            'scheduled' => 'Planifié',
                            'completed' => 'Terminé',
                            'cancelled' => 'Annulé',
                        ])
                        ->query(function (Builder $query, array $data) {
                            if (isset($data['value'])) {
                                $query->whereHas('contest', function ($q) use ($data) {
                                    $q->where('status', $data['value']);
                                });
                            }
                        }),
                        
                    Tables\Filters\SelectFilter::make('distribution_status')
                        ->label('Statut de la distribution')
                        ->options([
                            'active' => 'Active',
                            'upcoming' => 'A venir',
                            'expired' => 'Expirée',
                            'out_of_stock' => 'Épuisée',
                        ])
                        ->query(function (Builder $query, array $data) {
                            if (!isset($data['value'])) return $query;
                            
                            $now = now();
                            switch ($data['value']) {
                                case 'active':
                                    $query->where('start_date', '<=', $now)
                                          ->where('end_date', '>=', $now)
                                          ->where('remaining', '>', 0);
                                    break;
                                case 'upcoming':
                                    $query->where('start_date', '>', $now);
                                    break;
                                case 'expired':
                                    $query->where('end_date', '<', $now);
                                    break;
                                case 'out_of_stock':
                                    $query->where('remaining', '<=', 0);
                                    break;
                            }
                        }),
                ])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultGroup('contest.name')
            ->groups([
                Group::make('contest.name')
                    ->label('Concours')
                    ->getTitleFromRecordUsing(fn (PrizeDistribution $record): string => 
                        ($record->contest->status === 'active' ? '🟢 ' : '') . 
                        $record->contest->name . 
                        ($record->contest->status === 'active' ? ' [ACTIF]' : '')
                    ),
                Group::make('prize.name')
                    ->label('Prix'),
                Group::make('status')
                    ->label('Statut')
                    ->getTitleFromRecordUsing(function (PrizeDistribution $record): string {
                        $now = now();
                        if ($now->lt($record->start_date)) return '⏳ A venir';
                        if ($now->gt($record->end_date)) return '❌ Expiré';
                        if ($record->remaining <= 0) return '⚠️ Épuisé';
                        return '✅ Actif';
                    }),
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
