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
                Forms\Components\Select::make('contest_id')
                    ->relationship('contest', 'name')
                    ->label('Concours')
                    ->required(),
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
                    ->default(now()),
                Forms\Components\DateTimePicker::make('end_date')
                    ->label('Date de fin')
                    ->required()
                    ->default(now()->addMonth()),
                Forms\Components\TextInput::make('remaining')
                    ->label('Quantité restante')
                    ->helperText('Laissez vide pour initialiser avec la quantité totale')
                    ->numeric(),
            ])
            ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set) {
                if (is_null($get('remaining'))) {
                    $set('remaining', $get('quantity'));
                }
            });
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
