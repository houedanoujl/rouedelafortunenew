<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrizeResource\Pages;
use App\Filament\Resources\PrizeResource\RelationManagers;
use App\Models\Prize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Spatie\MediaLibraryPro\Media;

class PrizeResource extends Resource
{
    protected static ?string $model = Prize::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $modelLabel = 'Prix';
    
    protected static ?string $pluralModelLabel = 'Prix';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('value')
                    ->required(),
                Forms\Components\TextInput::make('image_url')
                    ->label('Image du prix (URL)')
                    ->placeholder('/assets/prizes/image.jpg')
                    ->helperText('URL de l\'image du prix. <a href="/gestion-images-prix" target="_blank">Ouvrir le gestionnaire d\'images</a>')
                    ->url()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('openManager')
                            ->label('Gérer les images')
                            ->url('/gestion-images-prix', true)
                            ->icon('heroicon-m-photo')
                    ),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
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
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Exporter en CSV')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename(date('Y-m-d') . '-prizes')
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
            'index' => Pages\ListPrizes::route('/'),
            'create' => Pages\CreatePrize::route('/create'),
            'edit' => Pages\EditPrize::route('/{record}/edit'),
        ];
    }
}
