<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialiser le mot de passe d\'un utilisateur par email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Aucun utilisateur trouvé avec l'email: {$email}");
            return 1;
        }
        
        $user->password = Hash::make($password);
        $user->save();
        
        $this->info("Le mot de passe de l'utilisateur {$email} a été réinitialisé avec succès.");
        
        return 0;
    }
}
