<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\QrCodeResource\Pages;
use App\Filament\Admin\Resources\QrCodeResource\RelationManagers;
use App\Models\QrCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class QrCodeResource extends Resource
{
    protected static ?string $model = QrCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    
    protected static ?string $navigationLabel = 'QR Codes';
    
    protected static ?string $navigationGroup = 'Gestion des participants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('entry_id')
                    ->relationship('entry', 'id')
                    ->label('Participation')
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\TextInput::make('code')
                    ->label('Code QR')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Toggle::make('scanned')
                    ->label('Scanné')
                    ->default(false),
                    
                Forms\Components\DateTimePicker::make('scanned_at')
                    ->label('Scanné le')
                    ->nullable(),
                    
                Forms\Components\TextInput::make('scanned_by')
                    ->label('Scanné par')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Sous-requête pour obtenir les IDs des QR codes les plus récents pour chaque participation
                $latestQrCodesSubquery = DB::table('qr_codes')
                    ->select('entry_id', DB::raw('MAX(id) as latest_id'))
                    ->groupBy('entry_id');
                
                // Utiliser la sous-requête pour ne récupérer que les derniers QR codes
                return $query->joinSub(
                    $latestQrCodesSubquery,
                    'latest_qr_codes',
                    function ($join) {
                        $join->on('qr_codes.id', '=', 'latest_qr_codes.latest_id');
                    }
                );
            })
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code QR')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Code QR copié !'),
                    
                Tables\Columns\ViewColumn::make('qr_code_image')
                    ->label('QR Code')
                    ->view('filament.tables.columns.qr-code'),
                
                Tables\Columns\TextColumn::make('entry.participant.first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('entry.participant.last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('entry.participant.phone')
                    ->label('Téléphone')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('entry.prize.name')
                    ->label('Lot gagné')
                    ->description(fn (QrCode $record): ?string => $record->entry?->prize?->description)
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('entry.prize.value')
                    ->label('Valeur')
                    ->money('EUR')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('scanned')
                    ->label('Scanné')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Scanné le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('entry.contest.name')
                    ->label('Concours')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scanned')
                    ->label('Statut')
                    ->options([
                        true => 'Scanné',
                        false => 'Non scanné',
                    ]),
                    
                Tables\Filters\SelectFilter::make('entry.contest_id')
                    ->label('Concours')
                    ->relationship('entry.contest', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('entry.prize_id')
                    ->label('Lot gagné')
                    ->relationship('entry.prize', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Créé depuis'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Créé jusqu\'à'),
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
                    
                Tables\Filters\Filter::make('prize_value')
                    ->label('Valeur du lot')
                    ->form([
                        Forms\Components\TextInput::make('min_value')
                            ->label('Valeur minimale')
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('max_value')
                            ->label('Valeur maximale')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_value'],
                                fn (Builder $query, $value): Builder => $query->whereHas('entry.prize', fn ($q) => $q->where('value', '>=', $value))
                            )
                            ->when(
                                $data['max_value'],
                                fn (Builder $query, $value): Builder => $query->whereHas('entry.prize', fn ($q) => $q->where('value', '<=', $value))
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('clean_duplicates')
                    ->label('Supprimer les doubles')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (QrCode $record) {
                        // Supprime tous les autres QR codes associés à la même participation sauf celui-ci
                        QrCode::where('entry_id', $record->entry_id)
                            ->where('id', '!=', $record->id)
                            ->delete();
                            
                        Notification::make()
                            ->title('QR codes dupliqués supprimés')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('clean_all_duplicates')
                    ->label('Nettoyer tous les doublons')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        Artisan::call('qrcodes:clean-duplicates');
                        
                        Notification::make()
                            ->title('Tous les QR codes dupliqués ont été supprimés')
                            ->success()
                            ->send();
                    }),
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
                Infolists\Components\TextEntry::make('code')
                    ->label('Code QR'),
                
                Infolists\Components\ViewEntry::make('qr_code_image')
                    ->label('QR Code Visuel')
                    ->view('filament.infolists.components.qr-code'),
                
                Infolists\Components\TextEntry::make('entry.id')
                    ->label('ID de la participation'),
                
                Infolists\Components\TextEntry::make('entry.participant.first_name')
                    ->label('Prénom du participant'),
                
                Infolists\Components\TextEntry::make('entry.participant.last_name')
                    ->label('Nom du participant'),
                
                Infolists\Components\IconEntry::make('scanned')
                    ->label('Scanné')
                    ->boolean(),
                
                Infolists\Components\TextEntry::make('scanned_at')
                    ->label('Scanné le')
                    ->dateTime(),
                
                Infolists\Components\TextEntry::make('scanned_by')
                    ->label('Scanné par'),
                
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
            'index' => Pages\ListQrCodes::route('/'),
            'create' => Pages\CreateQrCode::route('/create'),
            'view' => Pages\ViewQrCode::route('/{record}'),
            'edit' => Pages\EditQrCode::route('/{record}/edit'),
        ];
    }
}
