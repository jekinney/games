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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('how_to_play')->nullable();
            $table->string('image_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('game_file_url'); // URL to the JavaScript file containing the game code
            $table->string('category')->default('action');
            $table->integer('min_players')->default(1);
            $table->integer('max_players')->default(1);
            $table->integer('estimated_play_time')->nullable(); // in minutes
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('play_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->json('tags')->nullable(); // Store tags as JSON array
            $table->json('controls')->nullable(); // Store control instructions as JSON
            $table->text('developer_notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['is_active', 'is_featured']);
            $table->index('category');
            $table->index('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
