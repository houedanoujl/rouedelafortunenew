<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class WheelSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.pages.wheel-settings';
    
    protected static ?string $title = 'Réglages de la roue';
    
    protected static ?string $navigationGroup = 'Configuration';
    
    protected static ?int $navigationSort = 5;

    use InteractsWithForms;

    public ?array $data = [];
    
    // Chemin du fichier de config
    protected $configFile = 'wheel_settings.json';

    public function mount(): void
    {
        // Charger la configuration depuis le fichier JSON
        $probability = $this->loadProbability();
        
        $this->form->fill([
            'win_probability' => $probability,
        ]);
    }
    
    // Charger la probabilité depuis le fichier de config
    protected function loadProbability()
    {
        if (Storage::exists($this->configFile)) {
            $content = Storage::get($this->configFile);
            $settings = json_decode($content, true);
            return $settings['win_probability'] ?? 20;
        }
        
        return 20; // Valeur par défaut
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Probabilités de gain')
                    ->description('Configurez les chances de gagner à la roue')
                    ->schema([
                        TextInput::make('win_probability')
                            ->label('Probabilité de gain (%)')
                            ->required()
                            ->type('number')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(5)
                            ->default($this->loadProbability())
                            ->helperText('Pourcentage de chance qu\'un participant gagne à la roue. Ce réglage influence le tirage du résultat, mais le résultat reste caché jusqu\'au scan du QR code.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        // Sauvegarder dans le fichier JSON
        $settings = ['win_probability' => (int)$data['win_probability']];
        Storage::put($this->configFile, json_encode($settings));
            
        Notification::make()
            ->title('Réglages sauvegardés')
            ->body("Les joueurs ont maintenant {$data['win_probability']}% de chances de gagner.")
            ->success()
            ->send();
    }
}
