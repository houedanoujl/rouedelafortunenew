<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrizeResource\Pages;
use App\Models\Prize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrizeResource extends Resource
{
    protected static ?string $model = Prize::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    
    protected static ?string $navigationLabel = 'Prix';

    protected static ?string $modelLabel = 'Prix';

    protected static ?string $pluralModelLabel = 'Prix';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'product' => 'Produit',
                        'voucher' => 'Bon d\'achat',
                        'service' => 'Service',
                    ])
                    ->default('product')
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Valeur')
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->default(0),
                Forms\Components\FileUpload::make('image_url')
                    ->label('Image')
                    ->image()
                    ->directory('prizes')
                    ->maxSize(5120) // 5MB
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'product' => 'Produit',
                        'voucher' => 'Bon d\'achat',
                        'service' => 'Service',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'product' => 'success',
                        'voucher' => 'warning',
                        'service' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'product' => 'Produit',
                        'voucher' => 'Bon d\'achat',
                        'service' => 'Service',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
