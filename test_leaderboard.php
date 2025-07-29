<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\GameScore;

echo "Testing leaderboard functionality...\n";

// Test leaderboard for game ID 1 (Memory Test)
$leaderboard = GameScore::getLeaderboard(1, 'all', 10);

echo "Leaderboard entries: " . $leaderboard->count() . "\n";

echo "Top players:\n";
foreach ($leaderboard as $index => $entry) {
    echo ($index + 1) . ". " . $entry->user->name . " - Score: " . $entry->score . "\n";
}

echo "\nTotal scores in database: " . GameScore::where('game_id', 1)->count() . "\n";
echo "Unique users: " . GameScore::where('game_id', 1)->distinct('user_id')->count() . "\n";
