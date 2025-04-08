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
        // Utiliser DB::statement pour supprimer la contrainte unique directement avec SQL brut
        // Cette approche est compatible avec Laravel 12

        // D'abord vérifier si la contrainte existe pour éviter les erreurs
        $constraintExists = DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.table_constraints
            WHERE table_schema = DATABASE()
            AND table_name = 'participants'
            AND constraint_type = 'UNIQUE'
            AND constraint_name LIKE '%phone%'
        ");

        if ($constraintExists[0]->count > 0) {
            // Supprimer toutes les contraintes d'unicité qui contiennent 'phone'
            // Récupérer d'abord le nom exact de la contrainte
            $constraints = DB::select("
                SELECT constraint_name
                FROM information_schema.table_constraints
                WHERE table_schema = DATABASE()
                AND table_name = 'participants'
                AND constraint_type = 'UNIQUE'
                AND constraint_name LIKE '%phone%'
            ");

            // Avant de boucler sur les contraintes, ajoutez ce code pour déboguer
            $firstConstraint = $constraints[0] ?? null;
            if ($firstConstraint) {
                // Ceci affichera toutes les propriétés disponibles
                var_dump(get_object_vars($firstConstraint));
            }

            // Ensuite modifiez la boucle pour utiliser le bon nom de propriété
            foreach ($constraints as $constraint) {
                // Le nom de la propriété peut être 'Constraint_name' ou 'CONSTRAINT_NAME' selon la version de MySQL
                $constraintName = $constraint->Constraint_name ?? $constraint->CONSTRAINT_NAME ?? null;

                if ($constraintName) {
                    DB::statement("ALTER TABLE participants DROP INDEX `{$constraintName}`");
                }
            }
        }

        // Ajouter un nouvel index non-unique si nécessaire
        $indexExists = DB::select("
            SHOW INDEX FROM participants
            WHERE Key_name = 'participants_phone_index'
        ");

        if (count($indexExists) == 0) {
            Schema::table('participants', function (Blueprint $table) {
                $table->index('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression de l'index non-unique
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['phone']);
        });

        // Rétablir l'unicité
        Schema::table('participants', function (Blueprint $table) {
            $table->unique('phone');
        });
    }
};
