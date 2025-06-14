<?php

namespace App\Filament\Pages;

use App\Models\Contest;
use App\Models\Entry;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WinnersList extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Liste des gagnants';
    protected static ?string $navigationGroup = 'Rapports';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Liste des gagnants';

    protected static string $view = 'filament.pages.winners-list';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Entry::query()
                    ->where('has_won', true)
                    ->with(['participant', 'contest', 'prize', 'qrCode'])
            )
            ->defaultGroup('contest_id')
            ->groups([
                Group::make('contest_id')
                    ->label('Concours')
                    ->getTitleFromRecordUsing(fn (Entry $record): string => $record->contest->name ?? 'Concours inconnu')
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('contest.name')
                    ->label('Concours')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('participant.first_name')
                    ->label('Prénom')
                    ->searchable(),
                    
                TextColumn::make('participant.last_name')
                    ->label('Nom')
                    ->searchable(),
                    
                TextColumn::make('participant.phone')
                    ->label('Téléphone')
                    ->searchable(),
                    
                TextColumn::make('participant.email')
                    ->label('Email')
                    ->searchable(),
                    
                TextColumn::make('prize.name')
                    ->label('Lot gagné')
                    ->badge()
                    ->color('success')
                    ->searchable(),
                    
                TextColumn::make('prize.value')
                    ->label('Valeur')
                    ->money('EUR')
                    ->sortable(),
                    
                TextColumn::make('qrCode.code')
                    ->label('Code QR')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Code QR copié!'),
                    
                TextColumn::make('qrCode.scanned')
                    ->label('Scanné')
                    ->badge()
                    ->color(fn (?bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (?bool $state): string => $state ? 'Oui' : 'Non'),
                    
                TextColumn::make('claimed')
                    ->label('Réclamé')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Oui' : 'Non'),
                    
                TextColumn::make('created_at')
                    ->label('Date de gain')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contest_id')
                    ->label('Concours')
                    ->relationship('contest', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('claimed')
                    ->label('Réclamé')
                    ->query(fn (Builder $query): Builder => $query->where('claimed', true))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('not_claimed')
                    ->label('Non réclamé')
                    ->query(fn (Builder $query): Builder => $query->where('claimed', false))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('created_at')
                    ->label('Date de gain')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Depuis'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Jusqu\'à'),
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
            ])
            ->actions([
                Tables\Actions\Action::make('voir_details')
                    ->label('Détails')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(fn (Entry $record) => view('filament.modals.entry-details', ['entry' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
            ])
            ->headerActions([
                // Actions retirées - utilisation du bouton HTML direct dans la vue
            ]);
    }
    
    public function exportCsv()
    {
        try {
            // Augmenter les limites pour l'export
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', 300); // 5 minutes
            
            $filename = 'liste-gagnants-' . now()->format('Y-m-d-H-i') . '.csv';
            
            return response()->streamDownload(function() {
                $file = fopen('php://output', 'w');
                
                // Ajout du BOM UTF-8 pour Excel
                fwrite($file, "\xEF\xBB\xBF");
                
                // Entêtes
                $headers = [
                    'Concours',
                    'Prénom', 
                    'Nom',
                    'Téléphone',
                    'Email',
                    'Lot gagné',
                    'Valeur (EUR)',
                    'Code QR',
                    'Scanné',
                    'Réclamé',
                    'Date de gain'
                ];
                
                fputcsv($file, $headers, ';');
                
                // Traiter les données par chunks pour éviter les problèmes de mémoire
                Entry::query()
                    ->where('has_won', true)
                    ->with(['participant', 'contest', 'prize', 'qrCode'])
                    ->orderBy('created_at', 'desc')
                    ->chunk(50, function($winners) use ($file) {
                        foreach ($winners as $entry) {
                            $data = [
                                $entry->contest?->name ?? 'Non disponible',
                                $entry->participant?->first_name ?? 'Non disponible', 
                                $entry->participant?->last_name ?? 'Non disponible',
                                $entry->participant?->phone ?? 'Non disponible',
                                $entry->participant?->email ?? 'Non disponible',
                                $entry->prize?->name ?? 'Non disponible',
                                $entry->prize?->value ? number_format($entry->prize->value, 2, ',', ' ') : 'Non disponible',
                                $entry->qrCode?->code ?? 'Non disponible',
                                ($entry->qrCode && $entry->qrCode->scanned) ? 'Oui' : 'Non',
                                $entry->claimed ? 'Oui' : 'Non',
                                $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : 'Non disponible'
                            ];
                            
                            fputcsv($file, $data, ';');
                        }
                    });
                
                fclose($file);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
            
        } catch (\Exception $e) {
            // Log l'erreur pour debug
            \Log::error('Erreur export CSV: ' . $e->getMessage());
            
            // Retourner une notification d'erreur
            $this->notify('danger', 'Erreur lors de l\'export: ' . $e->getMessage());
            return null;
        }
    }
    
    public function exportToCsv()
    {
        // Récupérer tous les gagnants
        $winners = Entry::query()
            ->where('has_won', true)
            ->with(['participant', 'contest', 'prize', 'qrCode'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Créer le contenu CSV
        $csvContent = "\xEF\xBB\xBF"; // BOM UTF-8
        
        // Entêtes
        $headers = [
            'Concours',
            'Prénom',
            'Nom',
            'Téléphone',
            'Email',
            'Lot gagné',
            'Valeur (EUR)',
            'Code QR',
            'Scanné',
            'Réclamé',
            'Date de gain'
        ];
        
        $csvContent .= implode(';', $headers) . "\n";
        
        // Données
        foreach ($winners as $entry) {
            $data = [
                $entry->contest->name ?? 'Non disponible',
                $entry->participant->first_name ?? 'Non disponible',
                $entry->participant->last_name ?? 'Non disponible',
                $entry->participant->phone ?? 'Non disponible',
                $entry->participant->email ?? 'Non disponible',
                $entry->prize->name ?? 'Non disponible',
                $entry->prize->value ? number_format($entry->prize->value, 2, ',', ' ') : 'Non disponible',
                $entry->qrCode->code ?? 'Non disponible',
                ($entry->qrCode && $entry->qrCode->scanned) ? 'Oui' : 'Non',
                $entry->claimed ? 'Oui' : 'Non',
                $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : 'Non disponible'
            ];
            
            // Échapper les guillemets dans les données
            $escapedData = array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $data);
            
            $csvContent .= implode(';', $escapedData) . "\n";
        }
        
        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="liste-gagnants-' . now()->format('Y-m-d-H-i') . '.csv"')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', '0')
            ->header('Pragma', 'public');
    }
    
    // Méthodes d'actions supprimées pour éviter les erreurs 500
}
