<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corriger les entrées gagnantes qui n'ont pas de won_date défini
        DB::table('entries')
            ->where('has_won', true)
            ->whereNull('won_date')
            ->update([
                'won_date' => DB::raw('updated_at') // Utiliser updated_at comme date de gain par défaut
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionnel: remettre won_date à null pour les entrées modifiées
        // Ne pas implémenter de rollback car cela supprimerait des données utiles
    }
};
