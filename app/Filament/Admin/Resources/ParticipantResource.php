<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ParticipantResource\Pages;
use App\Filament\Admin\Resources\ParticipantResource\RelationManagers;
use App\Models\Participant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Participants';
    
    protected static ?string $navigationGroup = 'Gestion des participants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->label('Prénom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Grouper par première lettre du nom de famille
            ->groups([
                Tables\Grouping\Group::make('last_name_first_letter')
                    ->getTitleFromRecordUsing(fn (Participant $record): string => strtoupper(substr($record->last_name, 0, 1)))
                    ->getDescriptionFromRecordUsing(fn (Participant $record): string => 'Participants dont le nom commence par ' . strtoupper(substr($record->last_name, 0, 1)))
                    ->collapsible(),
                Tables\Grouping\Group::make('created_at_month')
                    ->label('Date d\'inscription (mois)')
                    ->getTitleFromRecordUsing(fn (Participant $record): string => $record->created_at->format('F Y'))
                    ->getDescriptionFromRecordUsing(fn (Participant $record): string => 'Inscrits en ' . $record->created_at->format('F Y'))
                    ->collapsible(),
            ])
            ->defaultGroup('last_name_first_letter')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date d\'inscription')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('entries_count')
                    ->label('Participations')
                    ->counts('entries')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Inscrit depuis'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Inscrit jusqu\'à'),
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
                Tables\Filters\Filter::make('has_entries')
                    ->label('A participé')
                    ->query(fn (Builder $query): Builder => $query->has('entries')),
                Tables\Filters\Filter::make('no_entries')
                    ->label('N\'a pas participé')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('entries')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Exporter en CSV')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn (Collection $records) => /* Logique d'export */ null),
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
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }
}
