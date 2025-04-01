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
        Schema::table('entries', function (Blueprint $table) {
            // Vérifier si la colonne claimed n'existe pas déjà
            if (!Schema::hasColumn('entries', 'claimed')) {
                $table->boolean('claimed')->default(false)->comment('Indique si le prix a été réclamé');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            if (Schema::hasColumn('entries', 'claimed')) {
                $table->dropColumn('claimed');
            }
        });
    }
};
