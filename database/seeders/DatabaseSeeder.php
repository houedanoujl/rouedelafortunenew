<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Contest;
use App\Models\Prize;
use App\Models\Participant;
use App\Models\Entry;
use App\Models\QrCode;
use App\Models\PrizeDistribution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utilisateur administrateur - vérifie d'abord si l'utilisateur existe
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]);
        }

        // Vérifier si un concours existe déjà
        if (Contest::count() === 0) {
            // Créer un concours
            $contest = Contest::create([
                'name' => 'Grand Jeu de Printemps',
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'status' => 'active',
                'description' => 'Participez à notre grand jeu de printemps et tentez de gagner des lots exceptionnels !',
            ]);

            // Création de prix
            $prizes = [
                [
                    'name' => 'Bon d\'achat 50€',
                    'description' => 'Bon d\'achat d\'une valeur de 50€',
                    'type' => 'voucher',
                    'value' => 50.00,
                    'image_url' => '/images/prizes/voucher50.jpg',
                    'stock' => 10,
                ],
                [
                    'name' => 'Tablette',
                    'description' => 'Tablette tactile dernière génération',
                    'type' => 'electronic',
                    'value' => 299.99,
                    'image_url' => '/images/prizes/tablet.jpg',
                    'stock' => 5,
                ],
                [
                    'name' => 'Casque audio',
                    'description' => 'Casque audio sans fil',
                    'type' => 'electronic',
                    'value' => 89.99,
                    'image_url' => '/images/prizes/headphones.jpg',
                    'stock' => 8,
                ],
                [
                    'name' => 'Bon d\'achat 20€',
                    'description' => 'Bon d\'achat d\'une valeur de 20€',
                    'type' => 'voucher',
                    'value' => 20.00,
                    'image_url' => '/images/prizes/voucher20.jpg',
                    'stock' => 15,
                ],
                [
                    'name' => 'Smartphone',
                    'description' => 'Smartphone haut de gamme',
                    'type' => 'electronic',
                    'value' => 499.99,
                    'image_url' => '/images/prizes/smartphone.jpg',
                    'stock' => 3,
                ],
                [
                    'name' => 'Week-end SPA',
                    'description' => 'Week-end pour deux personnes dans un spa de luxe',
                    'type' => 'experience',
                    'value' => 399.00,
                    'image_url' => '/images/prizes/spa.jpg',
                    'stock' => 2,
                ],
                [
                    'name' => 'TV 4K',
                    'description' => 'Téléviseur 4K Ultra HD 55 pouces',
                    'type' => 'electronic',
                    'value' => 649.99,
                    'image_url' => '/images/prizes/tv.jpg',
                    'stock' => 1,
                ],
                [
                    'name' => 'Cafetière',
                    'description' => 'Machine à café automatique',
                    'type' => 'household',
                    'value' => 159.99,
                    'image_url' => '/images/prizes/coffeemaker.jpg',
                    'stock' => 5,
                ],
            ];

            foreach ($prizes as $prizeData) {
                Prize::create($prizeData);
            }

            // Création des distributions de prix pour les concours
            $prizeDistributions = [
                // Concours "Jeu du Printemps" - Distribution équilibrée pour démonstration
                [
                    'contest_id' => 1,
                    'prize_id' => 1, // Bon d'achat 50€
                    'quantity' => 10,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 10,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 2, // Tablette
                    'quantity' => 2,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 2,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 3, // Casque audio
                    'quantity' => 5,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 5,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 4, // Bon d'achat 20€
                    'quantity' => 15,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 15,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 5, // Smartphone
                    'quantity' => 3,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 3,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 6, // Week-end SPA
                    'quantity' => 2,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 2,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 7, // TV 4K
                    'quantity' => 1,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 1,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 8, // Cafetière
                    'quantity' => 5,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 5,
                ],
            ];

            foreach ($prizeDistributions as $distributionData) {
                PrizeDistribution::create($distributionData);
            }

            // Créer quelques participants
            for ($i = 1; $i <= 5; $i++) {
                $participant = Participant::create([
                    'first_name' => "Prénom{$i}",
                    'last_name' => "Nom{$i}",
                    'phone' => "06" . str_pad($i, 8, '0', STR_PAD_LEFT),
                    'email' => "participant{$i}@example.com",
                ]);

                // Créer une participation pour chaque participant
                $entry = Entry::create([
                    'participant_id' => $participant->id,
                    'contest_id' => $contest->id,
                    'prize_id' => rand(1, 3),
                    'result' => 'en attente',
                    'played_at' => now()->subDays(rand(1, 5)),
                    'qr_code' => 'QR-' . Str::random(8),
                    'claimed' => false,
                    'won_date' => null,
                ]);

                // Créer un QR code pour chaque participation
                QrCode::create([
                    'entry_id' => $entry->id,
                    'code' => 'CODE-' . Str::random(8),
                    'scanned' => false,
                    'scanned_at' => null,
                    'scanned_by' => null,
                ]);
            }
        }
    }
}
