<?php

namespace App\Filament\Pages;

use App\Models\PrizeDistribution;
use App\Models\Contest;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class PrizeDistributionCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.prize-distribution-calendar';
    protected static ?string $navigationLabel = 'Calendrier des distributions';
    protected static ?string $title = 'Calendrier des distributions de prix';
    protected static ?string $navigationGroup = 'Rapports';
    protected static ?int $navigationSort = 2;

    public $events;
    public $contestColors;
    public $refreshInterval = 60; // Rafraîchir chaque minute par défaut
    
    // S'exécute au démarrage du composant
    public function mount()
    {
        $this->loadDistributions();
    }

    // Chargement des données des distributions
    public function loadDistributions()
    {
        // Générer une couleur unique par concours
        $contests = Contest::all();
        $palette = [
            'primary', 'success', 'danger', 'warning', 'info', 'gray', 'purple', 'pink', 'lime', 'teal', 'cyan', 'amber', 'fuchsia', 'rose', 'orange', 'green', 'blue', 'indigo', 'yellow', 'red',
        ];
        $paletteCount = count($palette);
        $this->contestColors = $contests->mapWithKeys(function ($contest, $i) use ($palette, $paletteCount) {
            return [$contest->id => $palette[$i % $paletteCount]];
        });

        // Charger les distributions de prix avec leurs dates spécifiques
        $this->events = PrizeDistribution::with(['contest', 'prize'])
            ->get()
            ->map(function ($distribution) {
                // Utiliser les champs start_date et end_date au lieu de created_at
                return [
                    'title' => $distribution->prize->name,
                    'start' => $distribution->start_date->toDateString(),
                    'end' => $distribution->end_date->toDateString(),
                    'contest_id' => $distribution->contest_id,
                    'contest_name' => $distribution->contest ? $distribution->contest->name : '',
                    'prize' => $distribution->prize->name,
                    'color' => $this->contestColors[$distribution->contest_id] ?? 'primary',
                    'is_active' => $distribution->remaining > 0 && $distribution->start_date <= now() && $distribution->end_date >= now(),
                ];
            });
    }

    // Actualisation automatique et manuelle
    public function refreshData()
    {
        $this->loadDistributions();
        
        // Notifier l'utilisateur de l'actualisation
        $this->dispatch('refreshed');
        
        Notification::make()
            ->title('Calendrier actualisé')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('refresh')
                ->label('Actualiser')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->refreshData()),
        ];
    }
}
