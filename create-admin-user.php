<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Vérifier si l'utilisateur existe déjà
    $user = User::where('email', 'houedanou@example.com')->first();
    
    if (!$user) {
        // Créer l'utilisateur administrateur
        User::create([
            'name' => 'houedanou',
            'email' => 'houedanou@example.com',
            'password' => Hash::make('nouveaumdp123')
        ]);
        echo "Utilisateur administrateur créé avec succès.\n";
    } else {
        echo "L'utilisateur administrateur existe déjà.\n";
    }
} catch (Exception $e) {
    echo "Erreur lors de la création de l'utilisateur administrateur: " . $e->getMessage() . "\n";
}
