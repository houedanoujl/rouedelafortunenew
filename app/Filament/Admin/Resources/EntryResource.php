<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EntryResource\Pages;
use App\Filament\Admin\Resources\EntryResource\RelationManagers;
use App\Models\Entry;
use App\Models\Contest;
use App\Models\Prize;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EntryResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    
    protected static ?string $navigationLabel = 'Participations';
    
    protected static ?string $navigationGroup = 'Gestion des participants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('participant_id')
                    ->relationship('participant', 'id')
                    ->label('Participant')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\Select::make('contest_id')
                    ->label('Concours')
                    ->options(Contest::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                    
                Forms\Components\Select::make('prize_id')
                    ->label('Prix gagné')
                    ->options(Prize::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                    
                Forms\Components\TextInput::make('qr_code')
                    ->label('Code QR')
                    ->maxLength(255)
                    ->nullable(),
                    
                Forms\Components\Select::make('result')
                    ->label('Résultat')
                    ->options([
                        'en attente' => 'En attente',
                        'win' => 'Gagné',
                        'lose' => 'Perdu',
                    ])
                    ->default('en attente')
                    ->required(),
                    
                Forms\Components\DateTimePicker::make('played_at')
                    ->label('Joué le')
                    ->nullable(),
                    
                Forms\Components\DateTimePicker::make('won_date')
                    ->label('Gagné le')
                    ->nullable(),
                    
                Forms\Components\Toggle::make('claimed')
                    ->label('Réclamé')
                    ->default(false),
                    
                Forms\Components\DateTimePicker::make('claimed_at')
                    ->label('Réclamé le')
                    ->nullable(),
                    
                Forms\Components\Textarea::make('wheel_config')
                    ->label('Configuration de la roue')
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('participant.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('participant.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('contest.name')
                    ->label('Concours')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('result')
                    ->label('Résultat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'win' => 'success',
                        'lose' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('prize.name')
                    ->label('Prix gagné')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('qr_code')
                    ->label('Code QR')
                    ->searchable(),
                    
                Tables\Columns\ViewColumn::make('qr_code_image')
                    ->label('QR Code')
                    ->view('filament.tables.columns.qr-code')
                    ->visible(fn (Entry $record): bool => !empty($record->qr_code)),
                    
                Tables\Columns\IconColumn::make('claimed')
                    ->label('Réclamé')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('played_at')
                    ->label('Joué le')
                    ->dateTime()
                    ->sortable(),
                    
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
                Tables\Filters\SelectFilter::make('result')
                    ->label('Résultat')
                    ->options([
                        'en attente' => 'En attente',
                        'win' => 'Gagné',
                        'lose' => 'Perdu',
                    ]),
                    
                Tables\Filters\SelectFilter::make('claimed')
                    ->label('Réclamé')
                    ->options([
                        true => 'Oui',
                        false => 'Non',
                    ]),
                    
                Tables\Filters\SelectFilter::make('contest_id')
                    ->label('Concours')
                    ->relationship('contest', 'name'),
                
                Tables\Filters\Filter::make('played_at')
                    ->form([
                        Forms\Components\DatePicker::make('played_from')
                            ->label('Joué depuis'),
                        Forms\Components\DatePicker::make('played_until')
                            ->label('Joué jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['played_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('played_at', '>=', $date),
                            )
                            ->when(
                                $data['played_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('played_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('id')
                    ->label('ID'),
                
                Infolists\Components\TextEntry::make('participant.first_name')
                    ->label('Prénom du participant'),
                
                Infolists\Components\TextEntry::make('participant.last_name')
                    ->label('Nom du participant'),
                
                Infolists\Components\TextEntry::make('participant.phone')
                    ->label('Téléphone'),
                
                Infolists\Components\TextEntry::make('participant.email')
                    ->label('Email'),
                
                Infolists\Components\TextEntry::make('contest.name')
                    ->label('Concours'),
                
                Infolists\Components\TextEntry::make('result')
                    ->label('Résultat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'win' => 'success',
                        'lose' => 'danger',
                        default => 'warning',
                    }),
                
                Infolists\Components\TextEntry::make('prize.name')
                    ->label('Prix gagné')
                    ->visible(fn (Entry $record): bool => $record->prize_id !== null),
                
                Infolists\Components\TextEntry::make('prize.value')
                    ->label('Valeur du prix')
                    ->visible(fn (Entry $record): bool => $record->prize_id !== null),
                
                Infolists\Components\TextEntry::make('qr_code')
                    ->label('Code QR'),
                
                Infolists\Components\ViewEntry::make('qr_code_image')
                    ->label('QR Code')
                    ->view('filament.infolists.components.qr-code')
                    ->visible(fn (Entry $record): bool => !empty($record->qr_code)),
                
                Infolists\Components\IconEntry::make('claimed')
                    ->label('Réclamé')
                    ->boolean(),
                
                Infolists\Components\TextEntry::make('played_at')
                    ->label('Joué le')
                    ->dateTime(),
                
                Infolists\Components\TextEntry::make('won_date')
                    ->label('Gagné le')
                    ->dateTime()
                    ->visible(fn (Entry $record): bool => $record->result === 'win'),
                
                Infolists\Components\TextEntry::make('claimed_at')
                    ->label('Réclamé le')
                    ->dateTime()
                    ->visible(fn (Entry $record): bool => $record->claimed),
                
                Infolists\Components\TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime(),
                
                Infolists\Components\TextEntry::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime(),
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
            'index' => Pages\ListEntries::route('/'),
            'create' => Pages\CreateEntry::route('/create'),
            'view' => Pages\ViewEntry::route('/{record}'),
            'edit' => Pages\EditEntry::route('/{record}/edit'),
        ];
    }
}
