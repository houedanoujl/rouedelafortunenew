@php /** @var \Illuminate\Support\Collection $events */ @endphp
<x-filament::page>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold dark:text-white">Calendrier des distributions de prix</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900 rounded-lg shadow">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Prix</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Concours</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-200">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($this->events as $event)
                        @php
                            $isFuture = \Carbon\Carbon::parse($event['start'])->isAfter(now());
                            $isPast = \Carbon\Carbon::parse($event['start'])->isBefore(now()->startOfDay());
                            $isToday = \Carbon\Carbon::parse($event['start'])->isToday();
                            
                            $rowClass = $isFuture ? 'bg-green-50 dark:bg-green-900/20' : '';
                            $rowClass = $isToday ? 'bg-yellow-50 dark:bg-yellow-900/30' : $rowClass;
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ \Carbon\Carbon::parse($event['start'])->format('d/m/Y') }}
                                @if($isToday)
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Aujourd'hui
                                    </span>
                                @elseif($isFuture)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                        À venir
                                    </span>
                                @endif
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
