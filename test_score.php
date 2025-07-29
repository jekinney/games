<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Game;
use App\Models\GameScore;

// Create a test user if none exists
$user = User::first();
if (!$user) {
    $user = User::create([
        'name' => 'Test Player',
        'email' => 'test@example.com',
        'password' => bcrypt('password')
    ]);
}

// Find the memory game
$game = Game::where('slug', 'memory-test-game')->first();
if (!$game) {
    echo "Memory game not found! Running seeder...\n";
    \Artisan::call('db:seed', ['--class' => 'GamesSeeder']);
    $game = Game::where('slug', 'memory-test-game')->first();
}

if ($game) {
    echo "Game found: {$game->name} (ID: {$game->id})\n";
    
    // Create a test score
    $score = GameScore::create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'score' => 1500,
        'level_reached' => 3,
        'time_played_seconds' => 120,
        'game_data' => json_encode([
            'total_cards' => 12,
            'moves' => 25,
            'perfect_matches' => 6
        ])
    ]);
    
    echo "Test score created successfully! Score ID: {$score->id}\n";
    echo "User: {$user->name}, Score: {$score->score}, Level: {$score->level_reached}\n";
} else {
    echo "Could not find or create memory game!\n";
}

echo "Total scores in database: " . GameScore::count() . "\n";
