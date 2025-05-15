<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <x-filament::button
            type="submit"
            class="mt-6"
            icon="heroicon-o-check"
        >
            Enregistrer les modifications
        </x-filament::button>
    </form>

    <div class="mt-8 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg text-amber-800 dark:text-amber-300">
        <h3 class="text-lg font-semibold mb-2">Heure actuelle (GMT/UTC)</h3>
        <div class="text-3xl font-mono" id="currentTimeGMT"></div>

        <h3 class="text-lg font-semibold mt-4 mb-2">Statut actuel</h3>
        <div class="text-xl" id="promotionalStatus">Vérification en cours...</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Actualiser l'heure toutes les secondes
            setInterval(updateCurrentTime, 1000);
            updateCurrentTime();

            function updateCurrentTime() {
                const now = new Date();
                // Convertir en GMT/UTC
                const utcTimeStr = now.toUTCString();
                // Récupérer l'heure UTC (format 24h)
                const utcHour = now.getUTCHours();
                const utcMinute = now.getUTCMinutes();

                document.getElementById('currentTimeGMT').textContent = utcTimeStr;

                // Vérifier si nous sommes dans une période promotionnelle
                // Note: ceci est une logique simplifiée qui reflète la logique backend
                const isPromotionalTime = (utcHour >= 12 && utcHour < 14) ||
                                          (utcHour >= 18 && utcHour < 20);

                const statusElement = document.getElementById('promotionalStatus');

                if (isPromotionalTime) {
                    statusElement.textContent = 'Période promotionnelle active! (50% de chances de gain)';
                    statusElement.classList.add('text-green-600');
                    statusElement.classList.remove('text-gray-600');
                } else {
                    statusElement.textContent = 'Période standard (1% de chances de gain)';
                    statusElement.classList.add('text-gray-600');
                    statusElement.classList.remove('text-green-600');
                }
            }
        });
    </script>
</x-filament::page>
