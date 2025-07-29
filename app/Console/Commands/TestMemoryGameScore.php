<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Console\Command;

class TestMemoryGameScore extends Command
{
    protected $signature = 'test:memory-score {user_email?}';
    protected $description = 'Test memory game score recording functionality';

    public function handle()
    {
        $this->info('🎮 Testing Memory Game Score Recording...');
        
        // Find or create a test user
        $email = $this->argument('user_email') ?? 'test@example.com';
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Test Player',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        
        // Find or create the memory game
        $game = Game::firstOrCreate(
            ['slug' => 'memory-test-game'],
            [
                'name' => 'Memory Test Game',
                'description' => 'Test your memory skills',
                'category' => 'puzzle',
                'difficulty' => 'easy',
                'is_active' => true,
                'image_url' => '/images/games/memory-test.svg',
                'game_file_url' => '/games/memory-test.html',
            ]
        );
        
        $this->info("✅ User: {$user->name} ({$user->email})");
        $this->info("✅ Game: {$game->name} (ID: {$game->id})");
        
        // Simulate a game score submission
        $scoreData = [
            'score' => rand(1000, 3000),
            'level_reached' => rand(3, 8),
            'time_played_seconds' => rand(90, 200),
            'game_data' => [
                'total_cards' => 16,
                'moves' => rand(20, 40),
                'perfect_matches' => rand(6, 16),
                'hints_used' => rand(0, 2),
            ]
        ];
        
        $this->info('🎯 Simulating score submission...');
        
        // Create the score directly
        $gameScore = GameScore::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'score' => $scoreData['score'],
            'level_reached' => $scoreData['level_reached'],
            'time_played_seconds' => $scoreData['time_played_seconds'],
            'game_data' => $scoreData['game_data'],
            'completed_at' => now(),
        ]);
        
        $this->info("✅ Score recorded! ID: {$gameScore->id}");
        $this->table([
            'Score', 'Level', 'Time (seconds)', 'Total Cards', 'Created'
        ], [
            [
                $gameScore->score,
                $gameScore->level_reached,
                $gameScore->time_played_seconds,
                $gameScore->game_data['total_cards'] ?? 'N/A',
                $gameScore->created_at->format('Y-m-d H:i:s')
            ]
        ]);
        
        // Check leaderboard
        $this->info('📊 Current leaderboard for Memory Test Game:');
        $topScores = GameScore::where('game_id', $game->id)
            ->with('user')
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get();
            
        if ($topScores->count() > 0) {
            $leaderboardData = $topScores->map(function ($score) {
                return [
                    $score->user->name ?? 'Unknown',
                    $score->score,
                    $score->level_reached ?? 'N/A',
                    $score->created_at->format('M j, Y')
                ];
            })->toArray();
            
            $this->table(['Player', 'Score', 'Level', 'Date'], $leaderboardData);
        } else {
            $this->warn('No scores found.');
        }
        
        $this->info('🎉 Memory game score recording test completed!');
        
        return 0;
    }
}
