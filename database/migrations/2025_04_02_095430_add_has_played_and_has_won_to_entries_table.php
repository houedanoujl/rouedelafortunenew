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
            $table->boolean('has_played')->default(false);
            $table->boolean('has_won')->default(false);
            $table->dropColumn(['result', 'played_at', 'wheel_config']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn(['has_played', 'has_won']);
            $table->string('result')->default('en attente');
            $table->timestamp('played_at')->nullable();
            $table->json('wheel_config')->nullable();
        });
    }
};
