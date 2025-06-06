<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Admin\Pages\ScanQrCodes;
use App\Filament\Pages\WinnersCalendar;
use App\Filament\Pages\WheelSettings;
use App\Filament\Pages\WinnersList;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('70 ans Dinor')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Pages\Dashboard::class,
                ScanQrCodes::class,
                WinnersCalendar::class,
                WheelSettings::class,
                WinnersList::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                \App\Filament\Widgets\GoogleAnalyticsWidget::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\WinnersListWidget::class,
                \App\Filament\Widgets\LatestParticipations::class,
            ])
            ->navigationItems([
                NavigationItem::make('Logs WhatsApp')
                    ->url('/admin/whatsapp-logs')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->group('Monitoring')
                    ->sort(3)
                    ->openUrlInNewTab(),
                NavigationItem::make('Logs tours de roue')
                    ->url(fn (): string => '/admin/spin-history-logs')
                    ->icon('heroicon-o-clock')
                    ->group('Rapports')
                    ->sort(3),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
