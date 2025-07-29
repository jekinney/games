<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Game;

class MemoryTestGameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Game::updateOrCreate(
            ['slug' => 'memory-test-game'],
            [
                'name' => 'Memory Test Game',
                'slug' => 'memory-test-game',
                'description' => 'A challenging memory game where you flip cards to find matching pairs. Test your memory skills across multiple levels with increasing difficulty.',
                'how_to_play' => 'Click cards to flip them over and reveal symbols. Find matching pairs of symbols to score points. Match all pairs to advance to the next level. Each level adds more cards and increases difficulty. You lose a life for each wrong match. The game ends when you run out of lives.',
                'category' => 'puzzle',
                'difficulty' => 'medium',
                'is_active' => true,
                'min_players' => 1,
                'max_players' => 1,
                'estimated_play_time' => 15,
                'thumbnail_url' => '/images/games/memory-test.svg',
                'image_url' => '/images/games/memory-test.svg',
                'game_file_url' => '/games/memory-test.html',
                'tags' => ['memory', 'puzzle', 'cards', 'matching', 'brain-training'],
                'controls' => ['mouse', 'touch'],
                'is_featured' => true,
            ]
        );
    }
}
