<x-filament::page>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between mb-4">
            <h2 class="text-2xl font-bold dark:text-white">{{ $monthName }}</h2>
            
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <button wire:click="previousMonth" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition dark:text-white">
                    <x-heroicon-o-chevron-left class="h-5 w-5" />
                </button>
                
                <div class="flex items-center space-x-2">
                    <select wire:model="selectedMonth" wire:change="changeMonth" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($months as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    
                    <select wire:model="selectedYear" wire:change="changeMonth" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($years as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <button wire:click="nextMonth" class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition dark:text-white">
                    <x-heroicon-o-chevron-right class="h-5 w-5" />
                </button>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="p-3 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100 rounded-lg">
                <span class="font-semibold">Rappel:</span> La limite est fixée à {{ $dailyLimit }} gagnants maximum par jour.
            </div>
        </div>
        
        <!-- Jours de la semaine -->
        <div class="grid grid-cols-7 gap-1 mb-2 font-semibold text-center dark:text-gray-200">
            <div class="p-2">Lundi</div>
            <div class="p-2">Mardi</div>
            <div class="p-2">Mercredi</div>
            <div class="p-2">Jeudi</div>
            <div class="p-2">Vendredi</div>
            <div class="p-2">Samedi</div>
            <div class="p-2">Dimanche</div>
        </div>
        
        <!-- Calendrier -->
        <div class="grid grid-cols-7 gap-1">
            @php
                $firstDayOfMonth = \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1);
                $startingDayOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 (Lundi) à 7 (Dimanche)
                
                // Ajouter des cellules vides pour les jours avant le début du mois
                $emptyCellsStart = $startingDayOfWeek - 1;
            @endphp
            
            <!-- Cellules vides pour aligner le premier jour -->
            @for ($i = 0; $i < $emptyCellsStart; $i++)
                <div class="p-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900"></div>
            @endfor
            
            <!-- Jours du mois -->
            @foreach($calendar as $date => $data)
                <div class="p-2 border border-gray-200 dark:border-gray-700 min-h-24 {{ $data['is_today'] ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-300 dark:border-blue-700' : 'dark:bg-gray-800' }}" 
                    x-data="{ showDetails: false }">
                    
                    <div class="flex justify-between items-start">
                        <span class="font-semibold {{ $data['is_today'] ? 'text-blue-700 dark:text-blue-300' : 'dark:text-white' }}">{{ $data['day'] }}</span>
                        
                        <div class="px-2 py-0.5 rounded-full text-xs {{ $data['count'] >= $data['max'] ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                            {{ $data['count'] }}/{{ $data['max'] }}
                        </div>
                    </div>
                    
                    @if($data['count'] > 0)
                        <div class="mt-2">
                            <button 
                                @click="showDetails = !showDetails" 
                                class="text-xs px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition">
                                {{ count($data['winners']) }} gagnant(s)
                            </button>
                            
                            <div x-show="showDetails" class="mt-2 text-xs dark:text-gray-300">
                                @foreach($data['winners'] as $winner)
                                    <div class="py-1 border-b border-dashed border-gray-200 dark:border-gray-700 last:border-0">
                                        <div class="font-medium">{{ $winner->participant->first_name ?? '' }} {{ $winner->participant->last_name ?? '' }}</div>
                                        <div class="text-gray-600 dark:text-gray-400">{{ $winner->prize->name ?? 'Pas de prix' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">Aucun gagnant</div>
                    @endif
                </div>
            @endforeach
            
            @php
                // Calculer le nombre de cellules supplémentaires pour compléter la grille
                $totalCells = $emptyCellsStart + count($calendar);
                $remainder = $totalCells % 7;
                $emptyCellsEnd = $remainder == 0 ? 0 : 7 - $remainder;
            @endphp
            
            <!-- Cellules vides pour terminer la dernière semaine -->
            @for ($i = 0; $i < $emptyCellsEnd; $i++)
                <div class="p-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900"></div>
            @endfor
        </div>
    </div>
    
    <!-- Statistiques de la semaine en cours -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Statistiques des 7 derniers jours</h3>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 bg-gray-50 dark:bg-gray-900 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-2 bg-gray-50 dark:bg-gray-900 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gagnants</th>
                        <th class="px-4 py-2 bg-gray-50 dark:bg-gray-900 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Limite</th>
                        <th class="px-4 py-2 bg-gray-50 dark:bg-gray-900 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Détails</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $weeklyStats = app(\App\Services\WinLimitService::class)->getWeeklyWinningStats();
                    @endphp
                    
                    @foreach($weeklyStats as $date => $stats)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                {{ $stats['date_formatted'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $stats['count'] >= $stats['max'] ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                    {{ $stats['count'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                {{ $stats['max'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                @if(count($stats['winners']) > 0)
                                    <div x-data="{ open: false }">
                                        <button @click="open = !open" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                            Voir les gagnants
                                        </button>
                                        
                                        <div x-show="open" class="mt-2 bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                            @foreach($stats['winners'] as $winner)
                                                <div class="py-1 text-xs">
                                                    <span class="font-medium">{{ $winner->participant->first_name ?? '' }} {{ $winner->participant->last_name ?? '' }}</span>
                                                    - {{ $winner->prize->name ?? 'Pas de prix' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">Aucun gagnant</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
