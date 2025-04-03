<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la colonne existe déjà
        if (!Schema::hasColumn('entries', 'prize_id')) {
            Schema::table('entries', function (Blueprint $table) {
                $table->foreignId('prize_id')->nullable()->constrained();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne rien faire en down puisque nous vérifions l'existence de la colonne en up
        // Cela évite les erreurs lors des rollbacks si la colonne existait déjà
    }
};
