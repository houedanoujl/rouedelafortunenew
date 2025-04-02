<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::where('email', 'houedanou@example.com')->first();
    
    if (!$user) {
        User::create([
            'name' => 'Houedanou',
            'email' => 'houedanou@example.com',
            'password' => Hash::make('nouveaumdp123')
        ]);
        echo "Utilisateur administrateur créé avec succès.\n";
    } else {
        echo "Utilisateur administrateur existe déjà.\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
