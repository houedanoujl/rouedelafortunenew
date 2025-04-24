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
                'password' => Hash::make('Rdf#2025_SecureAdmin!'),
            ]);
        }

        // Vérifier si un concours existe déjà
        if (Contest::count() === 0) {
            // Créer un concours
            $contest = Contest::create([
                'name' => 'Grand Jeu Dinor 70 ans',
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'status' => 'active',
                'description' => 'Participez à notre Grand Jeu Dinor 70 ans et tentez de gagner des lots exceptionnels !',
            ]);

            // Création de prix
            $prizes = [
                [
                    'name' => 'TV',
                    'description' => 'Téléviseur écran plat',
                    'type' => 'electronic',
                    'value' => 150000.00,
                    'image_url' => '/images/prizes/tv.jpg',
                    'stock' => 10,
                ],
                [
                    'name' => 'Téléphone portable',
                    'description' => 'Smartphone dernière génération',
                    'type' => 'electronic',
                    'value' => 100000.00,
                    'image_url' => '/images/prizes/phone.jpg',
                    'stock' => 10,
                ],
                [
                    'name' => 'Tablette',
                    'description' => 'Tablette tactile',
                    'type' => 'electronic',
                    'value' => 80000.00,
                    'image_url' => '/images/prizes/tablet.jpg',
                    'stock' => 10,
                ],
                [
                    'name' => 'Bons d\'achat LDF 50 000 F CFA',
                    'description' => 'Bon d\'achat utilisable dans les magasins LDF',
                    'type' => 'voucher',
                    'value' => 50000.00,
                    'image_url' => '/images/prizes/voucher_ldf.jpg',
                    'stock' => 15,
                ],
                [
                    'name' => 'Bons d\'achat Hyper U 50 000 F CFA',
                    'description' => 'Bon d\'achat utilisable dans les magasins Hyper U',
                    'type' => 'voucher',
                    'value' => 50000.00,
                    'image_url' => '/images/prizes/voucher_hyperu.jpg',
                    'stock' => 15,
                ],
                [
                    'name' => 'Friteuse air fryer',
                    'description' => 'Friteuse sans huile nouvelle génération',
                    'type' => 'household',
                    'value' => 45000.00,
                    'image_url' => '/images/prizes/airfryer.jpg',
                    'stock' => 10,
                ],
                [
                    'name' => 'Robot de cuisine',
                    'description' => 'Robot multifonction pour la cuisine',
                    'type' => 'household',
                    'value' => 70000.00,
                    'image_url' => '/images/prizes/kitchenrobot.jpg',
                    'stock' => 8,
                ],
                [
                    'name' => 'Micro onde',
                    'description' => 'Four à micro-ondes',
                    'type' => 'household',
                    'value' => 35000.00,
                    'image_url' => '/images/prizes/microwave.jpg',
                    'stock' => 10,
                ],
                [
                    'name' => 'Gazinière 4 feux four',
                    'description' => 'Cuisinière à gaz 4 feux avec four',
                    'type' => 'household',
                    'value' => 120000.00,
                    'image_url' => '/images/prizes/stove.jpg',
                    'stock' => 5,
                ],
                [
                    'name' => 'Lot de poêle',
                    'description' => 'Ensemble de poêles de cuisine',
                    'type' => 'household',
                    'value' => 25000.00,
                    'image_url' => '/images/prizes/pans.jpg',
                    'stock' => 15,
                ],
                [
                    'name' => 'Cartons de 5L Huile Dinor',
                    'description' => 'Carton contenant de l\'huile Dinor (5 litres)',
                    'type' => 'food',
                    'value' => 15000.00,
                    'image_url' => '/images/prizes/oil.jpg',
                    'stock' => 30,
                ],
                [
                    'name' => 'Sacs de riz 18KG Dinor',
                    'description' => 'Sac de riz Dinor de 18 kg',
                    'type' => 'food',
                    'value' => 12000.00,
                    'image_url' => '/images/prizes/rice.jpg',
                    'stock' => 30,
                ],
                [
                    'name' => 'Packs de mayonnaise de 430G Dinor',
                    'description' => 'Pack de mayonnaise Dinor (430g)',
                    'type' => 'food',
                    'value' => 5000.00,
                    'image_url' => '/images/prizes/mayonnaise.jpg',
                    'stock' => 40,
                ],
                [
                    'name' => 'Scooters électriques',
                    'description' => 'Scooter électrique urbain',
                    'type' => 'vehicle',
                    'value' => 350000.00,
                    'image_url' => '/images/prizes/scooter.jpg',
                    'stock' => 3,
                ],
                [
                    'name' => 'Voitures',
                    'description' => 'Voiture citadine neuve',
                    'type' => 'vehicle',
                    'value' => 5000000.00,
                    'image_url' => '/images/prizes/car.jpg',
                    'stock' => 1,
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
                    'prize_id' => 1, // TV
                    'quantity' => 10,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 10,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 2, // Téléphone portable
                    'quantity' => 10,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 10,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 3, // Tablette
                    'quantity' => 10,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 10,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 4, // Bons d'achat LDF
                    'quantity' => 15,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 15,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 5, // Bons d'achat Hyper U
                    'quantity' => 15,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 15,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 6, // Friteuse air fryer
                    'quantity' => 10,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 10,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 7, // Robot de cuisine
                    'quantity' => 8,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 8,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 8, // Micro onde
                    'quantity' => 10,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 10,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 9, // Gazinière 4 feux four
                    'quantity' => 5,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 5,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 10, // Lot de poêle
                    'quantity' => 15,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 15,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 11, // Cartons de 5L Huile Dinor
                    'quantity' => 30,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 30,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 12, // Sacs de riz 18KG Dinor
                    'quantity' => 30,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 30,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 13, // Packs de mayonnaise de 430G Dinor
                    'quantity' => 40,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 40,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 14, // Scooters électriques
                    'quantity' => 3,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 3,
                ],
                [
                    'contest_id' => 1,
                    'prize_id' => 15, // Voitures
                    'quantity' => 1,
                    'start_date' => '2025-03-01 00:00:00',
                    'end_date' => '2025-06-30 23:59:59',
                    'remaining' => 1,
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
                    'has_played' => rand(0, 1),
                    'has_won' => rand(0, 1),
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
