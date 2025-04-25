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
        Schema::create('stock_decremented_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entry_id');
            $table->unsignedBigInteger('prize_id');
            $table->timestamp('decremented_at');
            $table->timestamps();
            
            $table->foreign('entry_id')->references('id')->on('entries')->onDelete('cascade');
            $table->foreign('prize_id')->references('id')->on('prizes')->onDelete('cascade');
            
            // Garantir qu'une entrée ne peut décrémenter qu'une seule fois
            $table->unique('entry_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_decremented_logs');
    }
};
