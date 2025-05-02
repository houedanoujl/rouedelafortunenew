<?php

namespace App\Filament\Pages;

use App\Models\Entry;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SpinHistoryLogs extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament.pages.spin-history-logs';
    protected static ?string $navigationLabel = 'Historique des tours de roue';
    protected static ?string $title = 'Logs de tours de roue';
    protected static ?string $navigationGroup = 'Rapports';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;
    
    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->query(
                Entry::where('has_played', true)
            )
            ->modifyQueryUsing(function (Builder $query) {
                // On n'utilise pas vraiment cette requête 
                // car on affiche les données du JSON à la place
                return $query;
            })
            ->emptyStateHeading('Aucun tour de roue enregistré')
            ->columns([
                TextColumn::make('data.timestamp')
                    ->label('Date et heure')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable()
                    ->getStateUsing(function ($record, TextColumn $column) {
                        // Charger les données du fichier JSON
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData) return null;
                        
                        // Retourner la valeur timestamp
                        return Carbon::parse($spinData['timestamp']);
                    }),
                TextColumn::make('id')
                    ->label('ID Participation')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('data.participant.name')
                    ->label('Participant')
                    ->searchable()
                    ->getStateUsing(function ($record, TextColumn $column) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData || !isset($spinData['participant'])) return 'Inconnu';
                        
                        return $spinData['participant']['name'] ?? 'Inconnu';
                    }),
                TextColumn::make('data.participant.email')
                    ->label('Email')
                    ->searchable()
                    ->getStateUsing(function ($record, TextColumn $column) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData || !isset($spinData['participant'])) return '-';
                        
                        return $spinData['participant']['email'] ?? '-';
                    }),
                TextColumn::make('data.angle')
                    ->label('Angle')
                    ->numeric()
                    ->getStateUsing(function ($record, TextColumn $column) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData) return null;
                        
                        return $spinData['angle'] ?? null;
                    }),
                TextColumn::make('data.has_won_in_db')
                    ->label('Victoire BDD')
                    ->badge()
                    ->color(function ($record): string {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData) return 'gray';
                        
                        return isset($spinData['has_won_in_db']) && $spinData['has_won_in_db'] ? 'success' : 'danger';
                    })
                    ->formatStateUsing(function ($record) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData || !isset($spinData['has_won_in_db'])) return '-';
                        
                        return $spinData['has_won_in_db'] ? 'Oui' : 'Non';
                    }),
                TextColumn::make('result_json')
                    ->label('Résultat')
                    ->badge()
                    ->color(function ($record) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData || !isset($spinData['result'])) return 'gray';
                        return $spinData['result'] === 'win' ? 'success' : 'danger';
                    })
                    ->formatStateUsing(function ($record) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData || !isset($spinData['result'])) return '-';
                        return $spinData['result'] === 'win' ? 'GAGNÉ' : 'PERDU';
                    }),
                TextColumn::make('debug')
                    ->label('Logs Console')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData || !isset($spinData['console_logs']) || empty($spinData['console_logs'])) {
                            return new HtmlString('<em class="text-gray-400">Aucun log</em>');
                        }
                        $html = '<div class="max-h-40 overflow-y-auto text-xs font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded">';
                        foreach ($spinData['console_logs'] as $log) {
                            $html .= '<div class="mb-1">' . e($log) . '</div>';
                        }
                        $html .= '</div>';
                        return new HtmlString($html);
                    }),
                TextColumn::make('debug_json')
                    ->label('Debug JSON')
                    ->formatStateUsing(function ($record) {
                        $spinData = $this->getSpinHistoryForEntry($record->id);
                        if (!$spinData) return 'Aucune donnée trouvée';
                        return json_encode($spinData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    })
                    ->limit(100),
            ])
            ->defaultSort('id', 'desc')
            // Pagination supprimée car source JSON externe
            ->striped()
            ->searchable();
    }
    
    private function getSpinHistoryCollection(): Collection
    {
        $path = storage_path('app/spin_history.json');
        
        if (!File::exists($path)) {
            return collect([]);
        }
        
        $json = File::get($path);
        $data = json_decode($json, true);
        
        if (!is_array($data)) {
            return collect([]);
        }
        
        return collect($data);
    }
    
    private function getSpinHistoryForEntry($entryId)
    {
        $history = $this->getSpinHistoryCollection();
        
        // Correction : comparer les IDs en tant qu'entiers
        return $history->first(function ($item) use ($entryId) {
            return (int)($item['entry_id'] ?? 0) === (int)$entryId;
        });
    }
}
