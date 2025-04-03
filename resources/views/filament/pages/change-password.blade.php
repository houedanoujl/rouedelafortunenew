<x-filament-panels::page>
    <x-filament-panels::form wire:submit="changePassword">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" color="primary">
                Changer le mot de passe
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
