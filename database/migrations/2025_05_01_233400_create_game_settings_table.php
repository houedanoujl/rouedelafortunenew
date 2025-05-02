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
        Schema::create('game_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('win_probability')->default(20); // 20% par défaut
            $table->timestamps();
        });
        
        // Insérer les réglages par défaut
        DB::table('game_settings')->insert([
            'name' => 'default',
            'win_probability' => 20,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_settings');
    }
};
