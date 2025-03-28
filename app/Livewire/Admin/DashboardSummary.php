<?php

namespace App\Livewire\Admin;

use App\Models\Contest;
use App\Models\Entry;
use App\Models\Participant;
use App\Models\Prize;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardSummary extends Component
{
    public $totalParticipants = 0;
    public $totalEntries = 0;
    public $totalPrizes = 0;
    public $totalPrizesWon = 0;
    public $recentEntries = [];
    public $prizesDistribution = [];
    public $participantsPerDay = [];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Statistiques de base
        $this->totalParticipants = Participant::count();
        $this->totalEntries = Entry::count();
        $this->totalPrizes = Prize::count();
        $this->totalPrizesWon = Entry::where('result', 'win')->count();

        // Entrées récentes
        $this->recentEntries = Entry::with(['participant', 'prize'])
            ->orderBy('played_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'participant_name' => $entry->participant ? $entry->participant->full_name : 'Inconnu',
                    'prize_name' => $entry->prize ? $entry->prize->name : 'Aucun prix',
                    'result' => $entry->result,
                    'played_at' => $entry->played_at->format('d/m/Y H:i'),
                ];
            });

        // Distribution des prix
        $this->prizesDistribution = Entry::select('prizes.name', DB::raw('count(*) as count'))
            ->join('prizes', 'entries.prize_id', '=', 'prizes.id')
            ->where('entries.result', 'win')
            ->groupBy('prizes.name')
            ->get()
            ->toArray();

        // Participants par jour (30 derniers jours)
        $this->participantsPerDay = Entry::select(DB::raw('DATE(played_at) as date'), DB::raw('count(*) as count'))
            ->where('played_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.dashboard-summary');
    }
}
