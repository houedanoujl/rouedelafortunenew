<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function form(Form ): Form
    {
        return 
            ->schema([
                TextInput::make('username')
                    ->label('Nom d\'utilisateur')
                    ->required()
                    ->autocomplete(),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required(),
                Checkbox::make('remember')
                    ->label('Se souvenir de moi'),
            ]);
    }
}
