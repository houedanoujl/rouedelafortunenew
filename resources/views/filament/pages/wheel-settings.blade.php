<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}
        
        <div class="mt-6">
            <x-filament::button type="submit" class="mt-4">
                Sauvegarder les réglages
            </x-filament::button>
        </div>
        
        <div class="p-4 mt-6 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <h3 class="text-lg font-medium">Comment fonctionne le système de probabilités ?</h3>
            <p class="mt-2 text-gray-600 dark:text-gray-300">
                Quand un participant tourne la roue, le système effectue un tirage aléatoire basé sur la probabilité configurée ici. 
                Si le tirage est gagnant, la roue s'arrêtera sur un segment "GAGNÉ" et un QR code sera généré.
            </p>
            <p class="mt-2 text-gray-600 dark:text-gray-300">
                <strong>Important :</strong> Le résultat n'est pas affiché immédiatement à l'utilisateur. C'est seulement quand
                il scanne le QR code avec son téléphone que le résultat lui est révélé avec effets visuels et sonores.
                Cette approche crée un moment de suspense et permet de valider la présence physique de l'utilisateur lors de la réclamation d'un prix.
            </p>
        </div>
    </form>
</x-filament::page>
