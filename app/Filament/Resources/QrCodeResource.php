<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QrCodeResource\Pages;
use App\Filament\Resources\QrCodeResource\RelationManagers;
use App\Models\QrCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QrCodeResource extends Resource
{
    protected static ?string $model = QrCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $modelLabel = 'Code QR';
    
    protected static ?string $pluralModelLabel = 'Codes QR';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('entry_id')
                    ->relationship('entry', 'id')
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('scanned')
                    ->default(false),
                Forms\Components\DateTimePicker::make('scanned_at'),
                Forms\Components\TextInput::make('scanned_by')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry.participant.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entry.participant.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\IconColumn::make('scanned')
                    ->label('Scanné')
                    ->boolean(),
                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Scanné le')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scanned_by')
                    ->label('Scanné par')
                    ->searchable(),
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
            'index' => Pages\ListQrCodes::route('/'),
            'create' => Pages\CreateQrCode::route('/create'),
            'edit' => Pages\EditQrCode::route('/{record}/edit'),
        ];
    }
}
