<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\LatestParticipations;
use App\Filament\Widgets\GoogleAnalyticsWidget;
use App\Filament\Widgets\WinnersListWidget;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationGroup = null;
    
    protected static ?int $navigationSort = 1;
    
    protected function getHeaderWidgets(): array
    {
        return [
            GoogleAnalyticsWidget::class,
            StatsOverview::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            WinnersListWidget::class,
            LatestParticipations::class,
        ];
    }
}
