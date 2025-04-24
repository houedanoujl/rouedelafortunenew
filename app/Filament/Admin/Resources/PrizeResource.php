<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PrizeResource\Pages;
use App\Filament\Admin\Resources\PrizeResource\RelationManagers;
use App\Models\Prize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrizeResource extends Resource
{
    protected static ?string $model = Prize::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000),
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'physical' => 'Physique',
                                'virtual' => 'Virtuel',
                                'voucher' => 'Bon d\'achat',
                                'service' => 'Service',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->label('Valeur (€)')
                            ->numeric()
                            ->prefix('€')
                            ->inputMode('decimal'),
                    ])->columnSpan(['lg' => 2]),
                
                Forms\Components\Section::make('Stocks')
                    ->schema([
                        Forms\Components\TextInput::make('stock')
                            ->label('Stock Total')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->minValue(0)
                            ->step(1),
                        Forms\Components\Placeholder::make('available')
                            ->label('Disponibles (distributions)')
                            ->content(function (Prize $record) {
                                if ($record->exists) {
                                    $remaining = $record->prizeDistributions()->sum('remaining');
                                    return $remaining . ' sur ' . $record->prizeDistributions()->sum('quantity');
                                }
                                return '0 (nouveau prix)';
                            }),
                    ])->columnSpan(['lg' => 1]),

                Forms\Components\Section::make('Média')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->label('Image du prix (URL)')
                            ->placeholder('http://example.com/image.jpg')
                            ->url()
                            ->suffixIcon('heroicon-m-photo')
                            ->columnSpanFull()
                            ->helperText('Entrez l\'URL de l\'image du prix ou téléchargez-la manuellement dans le dossier public/prizes'),
                    ])->columnSpan(['lg' => 3]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prizeDistributions')
                    ->label('Disponibles')
                    ->getStateUsing(function (Prize $record) {
                        return $record->prizeDistributions()->sum('remaining');
                    })
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock Total')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPrizes::route('/'),
            'create' => Pages\CreatePrize::route('/create'),
            'edit' => Pages\EditPrize::route('/{record}/edit'),
        ];
    }
}
