<?php

namespace App\Filament\Pages;

use App\Models\PrizeDistribution;
use App\Models\Contest;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;

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

    public function mount()
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

        // Charger les distributions de prix
        $this->events = PrizeDistribution::with(['contest', 'prize'])
            ->get()
            ->map(function ($distribution) {
                return [
                    'title' => $distribution->prize->name,
                    'start' => $distribution->created_at->toDateString(),
                    'contest_id' => $distribution->contest_id,
                    'contest_name' => $distribution->contest ? $distribution->contest->name : '',
                    'prize' => $distribution->prize->name,
                    'color' => $this->contestColors[$distribution->contest_id] ?? 'primary',
                ];
            });
    }
}
