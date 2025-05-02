<x-filament::page>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold dark:text-white">{{ $this->getTitle() }}</h1>
        
        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
            <p class="mb-4 text-gray-700 dark:text-gray-300">
                Cette page présente l'historique complet des tours de roue, incluant les résultats, les participants, et les logs de console associés. 
                Ces informations sont particulièrement utiles pour vérifier que le système de QR code et l'expérience de révélation différée fonctionnent correctement.
            </p>
            
            {{ $this->table }}
        </div>
    </div>
</x-filament::page>
