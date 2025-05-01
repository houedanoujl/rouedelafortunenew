<?php

namespace App\Filament\Pages;

use App\Services\WinLimitService;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

class WinnersCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.winners-calendar';
    
    protected static ?string $navigationLabel = 'Calendrier des gagnants';
    
    protected static ?string $title = 'Calendrier des gagnants';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationGroup = 'Rapports';
    
    public ?array $calendarData = null;
    
    public ?int $selectedMonth = null;
    
    public ?int $selectedYear = null;
    
    public ?string $monthName = null;
    
    private WinLimitService $winLimitService;
    
    public function __construct()
    {
        $this->winLimitService = app(WinLimitService::class);
        $this->selectedMonth = Carbon::now()->month;
        $this->selectedYear = Carbon::now()->year;
        $this->monthName = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->format('F Y');
    }
    
    public function mount()
    {
        $this->loadCalendarData();
    }
    
    public function loadCalendarData()
    {
        try {
            $this->calendarData = $this->winLimitService->getMonthlyWinningStats($this->selectedMonth, $this->selectedYear);
            $this->monthName = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->format('F Y');
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données du calendrier', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->subMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->loadCalendarData();
    }
    
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->addMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->loadCalendarData();
    }
    
    public function changeMonth()
    {
        $this->loadCalendarData();
    }
    
    protected function getViewData(): array
    {
        return [
            'calendar' => $this->calendarData,
            'monthName' => $this->monthName,
            'dailyLimit' => WinLimitService::MAX_WINNERS_PER_DAY,
            'months' => $this->getMonthsArray(),
            'years' => $this->getYearsArray(),
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
        ];
    }
    
    private function getMonthsArray(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::create(null, $i, 1)->format('F');
        }
        return $months;
    }
    
    private function getYearsArray(): array
    {
        $currentYear = Carbon::now()->year;
        $years = [];
        for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
            $years[$i] = $i;
        }
        return $years;
    }
}
