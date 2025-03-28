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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('prize_id')->nullable()->constrained()->nullOnDelete();
            $table->string('result')->default('pending');
            $table->timestamp('played_at')->useCurrent();
            $table->string('qr_code', 512)->nullable();
            $table->boolean('claimed')->default(false);
            $table->dateTime('won_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
