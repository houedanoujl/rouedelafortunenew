<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Card;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PromotionalHoursSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static string $view = 'filament.pages.promotional-hours-settings';

    protected static ?string $title = 'Heures de gain promotionnelles';

    protected static ?string $navigationLabel = 'Heures promotionnelles';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 6;

    use InteractsWithForms;

    public ?array $data = [];

    // Chemin du fichier de config
    protected $configFile = 'promotional_hours_settings.json';

    public function mount(): void
    {
        // Charger la configuration depuis le fichier JSON
        $settings = $this->loadSettings();

        $this->form->fill([
            'enabled' => $settings['enabled'] ?? true,
            'promotional_rate' => $settings['promotional_rate'] ?? 50,
            'time_slots' => $settings['time_slots'] ?? [
                [
                    'name' => 'Midi',
                    'start_time' => '12:00',
                    'end_time' => '14:00',
                    'enabled' => true,
                ],
                [
                    'name' => 'Soirée',
                    'start_time' => '18:00',
                    'end_time' => '20:00',
                    'enabled' => true,
                ],
            ],
        ]);
    }

    // Charger les paramètres depuis le fichier de config
    protected function loadSettings()
    {
        if (Storage::exists($this->configFile)) {
            $content = Storage::get($this->configFile);
            return json_decode($content, true) ?: [];
        }

        return []; // Valeurs par défaut si le fichier n'existe pas
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuration des heures promotionnelles')
                    ->description('Définissez les plages horaires avec une probabilité de gain augmentée')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Activer les heures promotionnelles')
                            ->helperText('Activez ou désactivez complètement les heures promotionnelles')
                            ->default(true),

                        TextInput::make('promotional_rate')
                            ->label('Taux de gain pendant les heures promotionnelles (%)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(50)
                            ->helperText('Pourcentage de chance de gain pendant les plages horaires promotionnelles (par défaut: 50%)'),

                        Repeater::make('time_slots')
                            ->label('Plages horaires promotionnelles')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom de la plage')
                                    ->required(),
                                Grid::make()
                                    ->schema([
                                        TimePicker::make('start_time')
                                            ->label('Heure de début')
                                            ->seconds(false)
                                            ->required(),
                                        TimePicker::make('end_time')
                                            ->label('Heure de fin')
                                            ->seconds(false)
                                            ->required(),
                                    ]),
                                Toggle::make('enabled')
                                    ->label('Activer cette plage')
                                    ->default(true),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->collapsible()
                            ->defaultItems(2)
                            ->minItems(1)
                            ->maxItems(5)
                            ->helperText('Toutes les heures sont en GMT/UTC'),
                    ]),

                Section::make('Information')
                    ->description('Notes importantes')
                    ->schema([
                        Placeholder::make('impact')
                            ->label('Impact')
                            ->content('En dehors de ces périodes, le taux standard de 1% est appliqué.'),
                        Placeholder::make('fuseau')
                            ->label('Fuseau horaire')
                            ->content('Les heures sont en GMT/UTC. Pensez à ajuster en fonction du fuseau horaire local.'),
                        Placeholder::make('recommandation')
                            ->label('Recommandation')
                            ->content('Un taux de 50% pendant les heures promotionnelles est recommandé.'),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->extraAttributes(['class' => 'bg-primary-50 border border-primary-200'])
                    ->aside()
                    ->columnSpan(1),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Sauvegarder dans le fichier JSON
        Storage::put($this->configFile, json_encode($data, JSON_PRETTY_PRINT));

        Notification::make()  
            ->title('Heures promotionnelles sauvegardées')
            ->body("Les modifications ont été enregistrées avec succès.")
            ->success()
            ->send();
    }
}
