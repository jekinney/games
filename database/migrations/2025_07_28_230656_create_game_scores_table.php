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
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->integer('score');
            $table->integer('level_reached')->nullable();
            $table->integer('time_played_seconds')->nullable(); // Duration of the game session
            $table->json('game_data')->nullable(); // Store additional game-specific data
            $table->timestamp('completed_at')->nullable(); // When the game was completed
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['game_id', 'score']);
            $table->index(['user_id', 'game_id']);
            $table->index(['game_id', 'created_at']);
            $table->index(['game_id', 'score', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
