<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ChangePassword extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Changer mot de passe';
    protected static ?string $title = 'Changer votre mot de passe';
    protected static ?int $navigationSort = 90; // Positionnement dans le menu

    protected static string $view = 'filament.pages.change-password';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('current_password')
                    ->label('Mot de passe actuel')
                    ->password()
                    ->required()
                    ->autocomplete('current-password'),
                    
                TextInput::make('new_password')
                    ->label('Nouveau mot de passe')
                    ->password()
                    ->required()
                    ->autocomplete('new-password')
                    ->rules([
                        Password::min(8)
                            ->letters()
                            ->mixedCase()
                            ->numbers()
                            ->symbols(),
                    ])
                    ->same('new_password_confirmation'),
                    
                TextInput::make('new_password_confirmation')
                    ->label('Confirmer le nouveau mot de passe')
                    ->password()
                    ->required()
                    ->autocomplete('new-password'),
            ])
            ->statePath('data');
    }

    public function changePassword(): void
    {
        $data = $this->form->getState();
        
        // Vérifier le mot de passe actuel
        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            Notification::make()
                ->title('Erreur')
                ->body('Le mot de passe actuel est incorrect.')
                ->danger()
                ->send();
                
            return;
        }
        
        // Mettre à jour le mot de passe
        $user = Auth::user();
        $user->password = Hash::make($data['new_password']);
        $user->save();
        
        // Réinitialiser le formulaire
        $this->form->fill();
        
        // Notifier l'utilisateur
        Notification::make()
            ->title('Succès')
            ->body('Votre mot de passe a été modifié avec succès.')
            ->success()
            ->send();
    }
}
