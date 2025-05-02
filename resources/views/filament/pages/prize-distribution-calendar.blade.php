@php /** @var \Illuminate\Support\Collection $events */ @endphp
<x-filament::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold dark:text-white">Calendrier des distributions de prix</h2>
        
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 gap-2">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Ce calendrier s'actualise automatiquement toutes les {{ $this->refreshInterval }} secondes.<br>
                        <span id="countdown" class="font-medium">{{ $this->refreshInterval }}</span>
                        <span class="ml-4 font-semibold text-primary-700 dark:text-primary-200">
                            Nombre d'items : {{ $this->events->count() }}
                        </span>
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 mt-2 md:mt-0">
                    @php
                        $now = now();
                        $nbDistribue = $this->events->filter(function($event) use ($now) {
                            $end = \Carbon\Carbon::parse($event['end']);
                            return $end->isBefore($now->startOfDay());
                        })->count();
                        $nbEnCours = $this->events->filter(function($event) use ($now) {
                            $start = \Carbon\Carbon::parse($event['start']);
                            $end = \Carbon\Carbon::parse($event['end']);
                            return $start->isToday() || $end->isToday() || ($now->between($start, $end));
                        })->count();
                        $nbPlanifie = $this->events->filter(function($event) use ($now) {
                            $start = \Carbon\Carbon::parse($event['start']);
                            return $start->isAfter($now);
                        })->count();
                        $nbStockEpuise = $this->events->filter(function($event) use ($now) {
                            $isActive = $event['is_active'] ?? false;
                            $end = \Carbon\Carbon::parse($event['end']);
                            return !$isActive && !$end->isBefore($now->startOfDay());
                        })->count();
                    @endphp
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                        Distribué : {{ $nbDistribue }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        En cours : {{ $nbEnCours }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                        Planifié : {{ $nbPlanifie }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                        Stock épuisé : {{ $nbStockEpuise }}
                    </span>
                </div>
                <button type="button" 
                    class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 active:bg-primary-600 disabled:opacity-25 transition"
                    wire:click="refreshData">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Actualiser maintenant
                </button>
            </div>
        </div>
        
        {{-- Regroupement par concours avec accordéons --}}
        <div class="space-y-4">
            @php
                // Regrouper les événements par concours
                $eventsByContest = $this->events->sortByDesc(fn($event) => \Carbon\Carbon::parse($event['start']))
                    ->groupBy('contest_id');
            @endphp

            @foreach ($eventsByContest as $contestId => $contestEvents)
                @php
                    // Obtenir les informations du concours à partir du premier événement
                    $firstEvent = $contestEvents->first();
                    $contestName = $firstEvent['contest_name'];
                    $contestColor = $firstEvent['color'];
                    $totalItems = $contestEvents->count();
                @endphp
                
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow overflow-hidden">
                    {{-- Entête de l'accordéon --}}
                    <button type="button" 
                            class="w-full flex items-center justify-between px-4 py-3 bg-{{ $contestColor }}-50 dark:bg-{{ $contestColor }}-900/20 focus:outline-none"
                            onclick="toggleAccordion('contest-{{ $contestId }}')">
                        <div class="flex items-center">
                            <span class="font-medium text-lg text-{{ $contestColor }}-700 dark:text-{{ $contestColor }}-400">
                                {{ $contestName }}
                            </span>
                            <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                                ({{ $totalItems }} distribution{{ $totalItems > 1 ? 's' : '' }})
                            </span>
                        </div>
                        <svg id="icon-contest-{{ $contestId }}" class="h-5 w-5 text-{{ $contestColor }}-600 dark:text-{{ $contestColor }}-400 transform transition-transform duration-200" 
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    {{-- Contenu de l'accordéon --}}
                    <div id="contest-{{ $contestId }}" class="overflow-hidden transition-all duration-300 max-h-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Date début</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Date fin</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Prix</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Quantité restante</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($contestEvents as $event)
                                        @php
                                            $startDate = \Carbon\Carbon::parse($event['start']);
                                            $endDate = \Carbon\Carbon::parse($event['end']);
                                            $isFuture = $startDate->isAfter(now());
                                            $isPast = $endDate->isBefore(now()->startOfDay());
                                            $isToday = $startDate->isToday() || $endDate->isToday() || (now()->between($startDate, $endDate));
                                            
                                            $rowClass = $isFuture ? 'bg-green-50 dark:bg-green-900/20' : '';
                                            $rowClass = $isToday ? 'bg-yellow-50 dark:bg-yellow-900/30' : $rowClass;
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $startDate->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $endDate->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $event['prize'] }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center">
                                                @if(!is_null($event['remaining']))
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $event['remaining'] > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                        {{ $event['remaining'] }}/{{ $event['quantity'] }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                                @if(isset($event['id']) && isset($event['prize_id']))
                                                <div class="text-xs text-gray-400 mt-1">
                                                    ID: {{ $event['id'] }} | Prix: {{ $event['prize_id'] }}
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            // Configuration pour le rafraîchissement automatique
            let refreshInterval = {{ $this->refreshInterval }};
            let countdownElement = document.getElementById('countdown');
            let timeLeft = refreshInterval;
            
            // Fonction de compte à rebours
            function updateCountdown() {
                timeLeft--;
                if (countdownElement) {
                    countdownElement.textContent = timeLeft;
                }
                
                if (timeLeft <= 0) {
                    timeLeft = refreshInterval;
                    // Rafraîchir les données via Livewire
                    @this.refreshData();
                }
            }
            
            // Démarrer le compte à rebours
            setInterval(updateCountdown, 1000);
            
            // Réinitialiser le compte à rebours lorsque les données sont rafraîchies
            Livewire.on('refreshed', () => {
                timeLeft = refreshInterval;
                if (countdownElement) {
                    countdownElement.textContent = timeLeft;
                }
            });
        });
        
        // Fonction pour gérer les accordéons
        function toggleAccordion(id) {
            const content = document.getElementById(id);
            const icon = document.getElementById(`icon-${id}`);
            
            if (content.style.maxHeight === '1000px') {
                content.style.maxHeight = '0';
                icon.classList.remove('rotate-180');
            } else {
                content.style.maxHeight = '1000px';
                icon.classList.add('rotate-180');
            }
        }
        
        // Ouvrir automatiquement le premier accordéon
        document.addEventListener('DOMContentLoaded', function() {
            const firstAccordionButton = document.querySelector('[onclick^="toggleAccordion"]');
            if (firstAccordionButton) {
                firstAccordionButton.click();
            }
        });
    </script>
    @endpush
</x-filament::page>
