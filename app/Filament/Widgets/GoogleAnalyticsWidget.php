<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Http;

class GoogleAnalyticsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '300s'; // 5 minutes
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // En attendant l'installation correcte du package, nous simulons des données
        // Ces valeurs seront remplacées par les vraies données une fois le package installé
        $pageViews = $this->simulatePageViews();
        $activeUsers = $this->simulateActiveUsers();
        $bounceRate = $this->simulateBounceRate();

        return [
            Stat::make('Pages vues aujourd\'hui', $pageViews['today'])
                ->description('Comparé à ' . $pageViews['yesterday'] . ' hier')
                ->descriptionIcon($pageViews['today'] > $pageViews['yesterday'] ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($pageViews['chart'])
                ->color($pageViews['today'] > $pageViews['yesterday'] ? 'success' : 'danger'),
                
            Stat::make('Utilisateurs actifs', $activeUsers['count'])
                ->description($activeUsers['percent'] . '% ' . ($activeUsers['percent'] > 0 ? 'de plus' : 'de moins') . ' qu\'hier')
                ->descriptionIcon($activeUsers['percent'] > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($activeUsers['chart'])
                ->color($activeUsers['percent'] > 0 ? 'success' : 'danger'),
                
            Stat::make('Taux de rebond', $bounceRate['rate'] . '%')
                ->description($bounceRate['change'] . '% ' . ($bounceRate['change'] > 0 ? 'de plus' : 'de moins') . ' qu\'hier')
                ->descriptionIcon($bounceRate['change'] < 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($bounceRate['chart'])
                ->color($bounceRate['change'] < 0 ? 'success' : 'danger'),
        ];
    }

    private function simulatePageViews(): array
    {
        // Simulation de données pour l'exemple
        $today = rand(500, 2000);
        $yesterday = rand(400, 1800);
        
        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'chart' => [
                rand(200, 400),
                rand(300, 500),
                rand(400, 600),
                rand(500, 700),
                rand(600, 800),
                rand(700, 900),
                $today,
            ],
        ];
    }
    
    private function simulateActiveUsers(): array
    {
        // Simulation de données pour l'exemple
        $count = rand(50, 300);
        $percent = rand(-20, 40);
        
        return [
            'count' => $count,
            'percent' => $percent,
            'chart' => [
                rand(20, 50),
                rand(30, 60),
                rand(40, 70),
                rand(50, 80),
                rand(60, 90),
                rand(70, 100),
                $count,
            ],
        ];
    }
    
    private function simulateBounceRate(): array
    {
        // Simulation de données pour l'exemple
        $rate = rand(30, 80);
        $change = rand(-15, 15);
        
        return [
            'rate' => $rate,
            'change' => $change,
            'chart' => [
                rand($rate - 10, $rate + 10),
                rand($rate - 10, $rate + 10),
                rand($rate - 10, $rate + 10),
                rand($rate - 10, $rate + 10),
                rand($rate - 10, $rate + 10),
                rand($rate - 10, $rate + 10),
                $rate,
            ],
        ];
    }
}
