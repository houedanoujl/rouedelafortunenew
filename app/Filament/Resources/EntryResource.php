<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntryResource\Pages;
use App\Filament\Resources\EntryResource\RelationManagers;
use App\Models\Entry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class EntryResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $modelLabel = 'Participation';
    
    protected static ?string $pluralModelLabel = 'Participations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('participant_id')
                    ->relationship('participant', 'first_name')
                    ->required(),
                Forms\Components\Select::make('contest_id')
                    ->relationship('contest', 'name')
                    ->required(),
                Forms\Components\Select::make('prize_id')
                    ->relationship('prize', 'name'),
                Forms\Components\TextInput::make('result')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('played_at'),
                Forms\Components\TextInput::make('qr_code')
                    ->maxLength(255),
                Forms\Components\Toggle::make('claimed')
                    ->default(false),
                Forms\Components\DateTimePicker::make('won_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                Tables\Columns\TextColumn::make('prize.name')
                    ->label('Prix')
                    ->searchable(),
                Tables\Columns\TextColumn::make('result')
                    ->label('Résultat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('played_at')
                    ->label('Joué le')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('claimed')
                    ->label('Réclamé')
                    ->boolean(),
                Tables\Columns\TextColumn::make('won_date')
                    ->label('Date de gain')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_won')
                    ->label('Gagnant')
                    ->boolean(),
                Tables\Columns\TextColumn::make('participant.phone')
                    ->label('Téléphone')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('winners')
                    ->label('Seulement les gagnants')
                    ->query(fn (Builder $query): Builder => $query->where('has_won', true))
                    ->toggle()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('send_whatsapp')
                    ->label('Envoyer QR+PDF')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->url(fn (Entry $record): string => route('admin.whatsapp.send-documents', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Entry $record): bool => $record->has_won && $record->participant && $record->participant->phone)
                    ->tooltip('Envoyer le QR code et le PDF par WhatsApp')
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
                            ->withFilename(date('Y-m-d') . '-participations')
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
            'index' => Pages\ListEntries::route('/'),
            'create' => Pages\CreateEntry::route('/create'),
            'edit' => Pages\EditEntry::route('/{record}/edit'),
        ];
    }
}
