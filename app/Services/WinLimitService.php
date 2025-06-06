<?php

namespace App\Services;

use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WinLimitService
{
    /**
     * Nombre maximum de gagnants autorisés par jour
     */
    const MAX_WINNERS_PER_DAY = 2;
    
    /**
     * Période de rotation en jours
     */
    const ROTATION_PERIOD_DAYS = 7;
    
    /**
     * Clé de cache pour le nombre de gagnants du jour
     */
    const CACHE_KEY_PREFIX = 'daily_winners_count_';
    
    /**
     * Vérifie si un nouveau gagnant est autorisé aujourd'hui
     *
     * @return bool
     */
    public function canWinToday(): bool
    {
        $todayCount = $this->getTodayWinnersCount();
        
        Log::info('Vérification de la limite de gains quotidienne', [
            'date' => Carbon::now()->toDateString(),
            'gagnants_aujourd_hui' => $todayCount,
            'limite_quotidienne' => self::MAX_WINNERS_PER_DAY,
            'autorisation' => $todayCount < self::MAX_WINNERS_PER_DAY ? 'Autorisé' : 'Refusé'
        ]);
        
        return $todayCount < self::MAX_WINNERS_PER_DAY;
    }
    
    /**
     * Obtient le nombre de gagnants du jour en cours
     *
     * @return int
     */
    public function getTodayWinnersCount(): int
    {
        $today = Carbon::now()->toDateString();
        $cacheKey = self::CACHE_KEY_PREFIX . $today;
        
        // Si présent en cache, retourner cette valeur
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey, 0);
        }
        
        // Sinon, compter dans la base de données
        $count = $this->countWinnersInDatabase($today);
        
        // Stocker en cache pour la journée (expire à minuit)
        $secondsUntilEndOfDay = Carbon::now()->endOfDay()->diffInSeconds(Carbon::now());
        Cache::put($cacheKey, $count, $secondsUntilEndOfDay);
        
        return $count;
    }
    
    /**
     * Incrémente le compteur de gagnants pour aujourd'hui
     *
     * @return void
     */
    public function incrementTodayWinnersCount(): void
    {
        $today = Carbon::now()->toDateString();
        $cacheKey = self::CACHE_KEY_PREFIX . $today;
        
        $currentCount = $this->getTodayWinnersCount();
        $secondsUntilEndOfDay = Carbon::now()->endOfDay()->diffInSeconds(Carbon::now());
        
        // Mettre à jour le compteur en cache
        Cache::put($cacheKey, $currentCount + 1, $secondsUntilEndOfDay);
        
        Log::info('Nouveau gagnant aujourd\'hui', [
            'date' => $today,
            'nouveau_total' => $currentCount + 1,
            'limite_quotidienne' => self::MAX_WINNERS_PER_DAY
        ]);
    }
    
    /**
     * Compte le nombre de gagnants dans la base de données pour une date donnée
     *
     * @param string $date Format Y-m-d
     * @return int
     */
    private function countWinnersInDatabase(string $date): int
    {
        // Compter les entrées gagnantes pour cette date
        return Entry::where('has_won', true)
            ->whereDate('updated_at', $date)
            ->count();
    }
    
    /**
     * Récupère les entrées gagnantes pour une date donnée
     *
     * @param string $date Format Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWinnersForDate(string $date)
    {
        return Entry::where('has_won', true)
            ->whereDate('updated_at', $date)
            ->with(['participant', 'prize'])
            ->get();
    }
    
    /**
     * Génère des statistiques sur les gains des 7 derniers jours
     *
     * @return array
     */
    public function getWeeklyWinningStats(): array
    {
        $stats = [];
        $today = Carbon::now();
        
        // Calculer les statistiques pour les 7 derniers jours
        for ($i = 0; $i < self::ROTATION_PERIOD_DAYS; $i++) {
            $date = $today->copy()->subDays($i);
            $dateString = $date->toDateString();
            
            $stats[$dateString] = [
                'date' => $dateString,
                'date_formatted' => $date->format('d/m/Y'),
                'count' => $this->countWinnersInDatabase($dateString),
                'max' => self::MAX_WINNERS_PER_DAY,
                'winners' => $this->getWinnersForDate($dateString)
            ];
        }
        
        return $stats;
    }
    
    /**
     * Génère des statistiques pour un mois donné
     * 
     * @param int $month Le mois (1-12)
     * @param int $year L'année
     * @return array
     */
    public function getMonthlyWinningStats(int $month = null, int $year = null): array
    {
        if ($month === null) {
            $month = Carbon::now()->month;
        }
        
        if ($year === null) {
            $year = Carbon::now()->year;
        }
        
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $stats = [];
        $currentDate = $startOfMonth->copy();
        
        while ($currentDate->lte($endOfMonth)) {
            $dateString = $currentDate->toDateString();
            
            $stats[$dateString] = [
                'date' => $dateString,
                'date_formatted' => $currentDate->format('d/m/Y'),
                'day_name' => $currentDate->translatedFormat('l'),
                'day' => $currentDate->day,
                'count' => $this->countWinnersInDatabase($dateString),
                'max' => self::MAX_WINNERS_PER_DAY,
                'winners' => $this->getWinnersForDate($dateString),
                'is_today' => $currentDate->isToday()
            ];
            
            $currentDate->addDay();
        }
        
        return $stats;
    }
    
    /**
     * Purge les entrées de cache expirées
     */
    public function purgeCachedCounts(): void
    {
        $today = Carbon::now()->toDateString();
        
        // Supprimer toutes les clés plus anciennes que 7 jours
        for ($i = 8; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $cacheKey = self::CACHE_KEY_PREFIX . $date;
            
            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
            }
        }
    }
}
