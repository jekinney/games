<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GameScore;

class TestLeaderboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:leaderboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the leaderboard functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing leaderboard functionality...');

        // Test leaderboard for game ID 1 (Memory Test)
        $leaderboard = GameScore::getLeaderboard(1, 'all', 10);

        $this->info("Leaderboard entries: " . $leaderboard->count());
        $this->info("Top players:");

        foreach ($leaderboard as $index => $entry) {
            $this->line(($index + 1) . ". " . $entry->user->name . " - Score: " . $entry->score);
        }

        $this->info("\nDatabase statistics:");
        $this->info("Total scores for Memory Test: " . GameScore::where('game_id', 1)->count());
        $this->info("Unique users with scores: " . GameScore::where('game_id', 1)->distinct('user_id')->count());

        return 0;
    }
}
