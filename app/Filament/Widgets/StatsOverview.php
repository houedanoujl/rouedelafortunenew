<?php

namespace App\Filament\Widgets;

use App\Models\Entry;
use App\Models\Participant;
use App\Models\Prize;
use App\Models\QrCode;
use App\Models\Contest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    
    protected function getStats(): array
    {
        // Statistiques de base
        $totalParticipants = Participant::count();
        $totalEntries = Entry::count();
        $totalWinners = Entry::where('has_won', true)->count();
        $winRate = $totalEntries > 0 ? round(($totalWinners / $totalEntries) * 100, 1) : 0;
        
        // Statistiques de la dernière semaine
        $lastWeekParticipants = Participant::where('created_at', '>=', now()->subDays(7))->count();
        $lastWeekEntries = Entry::where('created_at', '>=', now()->subDays(7))->count();
        
        // Statistiques des QR codes
        $qrCodesScanned = QrCode::where('scanned', true)->count();
        $qrCodesTotal = QrCode::count();
        $scanRate = $qrCodesTotal > 0 ? round(($qrCodesScanned / $qrCodesTotal) * 100, 1) : 0;
        
        // Statistiques des prix
        $prizesAwarded = Entry::where('has_won', true)->whereNotNull('prize_id')->count();
        $prizesUnclaimed = Entry::where('has_won', true)
            ->whereNotNull('prize_id')
            ->where('claimed', false)
            ->count();
        
        return [
            Stat::make('Total Participants', $totalParticipants)
                ->description($lastWeekParticipants . ' nouveaux cette semaine')
                ->descriptionIcon('heroicon-m-user-plus')
                ->chart([
                    Participant::where('created_at', '>=', now()->subDays(7))->where('created_at', '<', now()->subDays(6))->count(),
                    Participant::where('created_at', '>=', now()->subDays(6))->where('created_at', '<', now()->subDays(5))->count(),
                    Participant::where('created_at', '>=', now()->subDays(5))->where('created_at', '<', now()->subDays(4))->count(),
                    Participant::where('created_at', '>=', now()->subDays(4))->where('created_at', '<', now()->subDays(3))->count(),
                    Participant::where('created_at', '>=', now()->subDays(3))->where('created_at', '<', now()->subDays(2))->count(),
                    Participant::where('created_at', '>=', now()->subDays(2))->where('created_at', '<', now()->subDays(1))->count(),
                    Participant::where('created_at', '>=', now()->subDays(1))->count(),
                ])
                ->color('primary'),
                
            Stat::make('Total Participations', $totalEntries)
                ->description($lastWeekEntries . ' nouvelles cette semaine')
                ->descriptionIcon('heroicon-m-ticket')
                ->chart([
                    Entry::where('created_at', '>=', now()->subDays(7))->where('created_at', '<', now()->subDays(6))->count(),
                    Entry::where('created_at', '>=', now()->subDays(6))->where('created_at', '<', now()->subDays(5))->count(),
                    Entry::where('created_at', '>=', now()->subDays(5))->where('created_at', '<', now()->subDays(4))->count(),
                    Entry::where('created_at', '>=', now()->subDays(4))->where('created_at', '<', now()->subDays(3))->count(),
                    Entry::where('created_at', '>=', now()->subDays(3))->where('created_at', '<', now()->subDays(2))->count(),
                    Entry::where('created_at', '>=', now()->subDays(2))->where('created_at', '<', now()->subDays(1))->count(),
                    Entry::where('created_at', '>=', now()->subDays(1))->count(),
                ])
                ->color('success'),
                
            Stat::make('Gagnants', $totalWinners)
                ->description('Taux de gain: ' . $winRate . '%')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
                
            Stat::make('QR Codes scannés', $qrCodesScanned)
                ->description('Taux de scan: ' . $scanRate . '%')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('danger'),
                
            Stat::make('Prix attribués', $prizesAwarded)
                ->description($prizesUnclaimed . ' non réclamés')
                ->descriptionIcon('heroicon-m-gift')
                ->color('info'),
                
            Stat::make('Concours actifs', Contest::where('end_date', '>=', now())->count())
                ->description(Contest::where('start_date', '<=', now())->where('end_date', '>=', now())->count() . ' en cours')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
        ];
    }
}
