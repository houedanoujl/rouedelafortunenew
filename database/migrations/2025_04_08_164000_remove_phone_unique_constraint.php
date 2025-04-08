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
        // Supprimer l'index unique sur le téléphone dans la table participants
        Schema::table('participants', function (Blueprint $table) {
            // Récupérer le nom de l'index unique pour le supprimer
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('participants');
            
            // Vérifier les index existants et supprimer celui qui concerne le téléphone
            foreach ($indexes as $name => $index) {
                if ($index->isUnique() && count($index->getColumns()) === 1 && in_array('phone', $index->getColumns())) {
                    $table->dropIndex($name);
                }
            }
        });
        
        // Ajouter un nouvel index non-unique
        Schema::table('participants', function (Blueprint $table) {
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->unique('phone');
        });
    }
};
