@php /** @var \Illuminate\Support\Collection $events */ @endphp
<x-filament::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold dark:text-white">Calendrier des distributions de prix</h2>
        
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
            <div class="flex justify-between items-center mb-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ce calendrier s'actualise automatiquement toutes les {{ $this->refreshInterval }} secondes.
                    <span id="countdown" class="font-medium">{{ $this->refreshInterval }}</span>
                </p>
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
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900 rounded-lg shadow">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Date début</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Date fin</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Prix</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Concours</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($this->events as $event)
                        @php
                            $startDate = \Carbon\Carbon::parse($event['start']);
                            $endDate = \Carbon\Carbon::parse($event['end']);
                            $isFuture = $startDate->isAfter(now());
                            $isPast = $endDate->isBefore(now()->startOfDay());
                            $isToday = $startDate->isToday() || $endDate->isToday() || (now()->between($startDate, $endDate));
                            $isActive = $event['is_active'] ?? false;
                            
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
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-semibold" style="color: var(--filament-color-{{ $event['color'] }}-600)">
                                <span class="inline-block px-2 py-1 rounded bg-{{ $event['color'] }}-100 dark:bg-{{ $event['color'] }}-900">{{ $event['contest_name'] }}</span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm">
                                @if($isPast)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                        Distribué
                                    </span>
                                @elseif($isToday)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        En cours
                                    </span>
                                @elseif($isFuture)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Planifié
                                    </span>
                                @endif
                                
                                @if(!$isActive && !$isPast)
                                    <span class="ml-1 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Stock épuisé
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
    </script>
    @endpush
</x-filament::page>
