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
        // Approche robuste pour la suppression de l'index unique sur 'phone'
        
        // 1. Récupérer tous les index liés à la colonne 'phone'
        $indexes = DB::select("SHOW INDEXES FROM participants WHERE Column_name = 'phone'");
        
        // 2. Pour chaque index, tenter de le supprimer
        $processedKeys = [];
        foreach ($indexes as $index) {
            $keyName = $index->Key_name;
            
            // Éviter les doublons
            if (in_array($keyName, $processedKeys)) {
                continue;
            }
            
            try {
                DB::statement("ALTER TABLE participants DROP INDEX `{$keyName}`");
                $processedKeys[] = $keyName;
            } catch (\Exception $e) {
                // Ignorer si l'index n'existe pas ou ne peut pas être supprimé
                continue;
            }
        }
        
        // 3. Si aucun index n'a été trouvé, essayer les noms standard
        if (empty($processedKeys)) {
            $possibleIndexNames = [
                'participants_phone_unique',
                'phone',
                'participants_phone_index'
            ];
            
            foreach ($possibleIndexNames as $indexName) {
                try {
                    DB::statement("ALTER TABLE participants DROP INDEX `{$indexName}`");
                } catch (\Exception $e) {
                    // Ignorer si l'index n'existe pas
                    continue;
                }
            }
        }
        
        // 4. Créer un nouvel index non-unique
        $checkIndex = DB::select("SHOW INDEXES FROM participants WHERE Column_name = 'phone'");
        if (empty($checkIndex)) {
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
        try {
            Schema::table('participants', function (Blueprint $table) {
                $table->dropIndex(['phone']);
            });
        } catch (\Exception $e) {
            // Ignorer si l'index n'existe pas
        }
        
        // Rétablir l'unicité
        Schema::table('participants', function (Blueprint $table) {
            $table->unique('phone');
        });
    }
};
