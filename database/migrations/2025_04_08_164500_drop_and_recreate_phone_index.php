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
        // Supprimer tous les index sur la colonne phone
        Schema::table('participants', function (Blueprint $table) {
            // On essaie de supprimer différentes variantes possibles de l'index
            try { $table->dropUnique('participants_phone_unique'); } catch (\Exception $e) {}
            try { $table->dropUnique(['phone']); } catch (\Exception $e) {}
            try { $table->dropIndex('participants_phone_index'); } catch (\Exception $e) {}
            try { $table->dropIndex(['phone']); } catch (\Exception $e) {}
        });
        
        // Ajouter un nouvel index non-unique
        Schema::table('participants', function (Blueprint $table) {
            $table->index('phone', 'participants_phone_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('participants_phone_index');
            $table->unique('phone', 'participants_phone_unique');
        });
    }
};
