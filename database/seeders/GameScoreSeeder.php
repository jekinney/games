<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\User;
use App\Models\GameScore;
use Carbon\Carbon;

class GameScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Memory Test Game
        $memoryGame = Game::where('slug', 'memory-test-game')->first();
        
        if (!$memoryGame) {
            $this->command->info('Memory Test Game not found. Please make sure it exists in the database.');
            return;
        }

        // Get or create test users
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $users[] = User::firstOrCreate([
                'email' => "player{$i}@example.com"
            ], [
                'name' => "Player {$i}",
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Create sample scores for different timeframes
        $timeframes = [
            'recent' => Carbon::now()->subDays(rand(1, 7)), // Last week
            '30d' => Carbon::now()->subDays(rand(8, 30)), // Last 30 days
            '60d' => Carbon::now()->subDays(rand(31, 60)), // Last 60 days
            '90d' => Carbon::now()->subDays(rand(61, 90)), // Last 90 days
            '1y' => Carbon::now()->subDays(rand(91, 365)), // Last year
        ];

        $this->command->info('Creating sample game scores...');

        foreach ($users as $user) {
            // Create multiple scores per user across different timeframes
            $scoreCount = rand(3, 8);
            
            for ($i = 0; $i < $scoreCount; $i++) {
                $timeframe = array_rand($timeframes);
                $baseDate = $timeframes[$timeframe];
                
                // Add some randomness to the date
                $createdAt = $baseDate->copy()->addHours(rand(-24, 24));
                
                GameScore::create([
                    'user_id' => $user->id,
                    'game_id' => $memoryGame->id,
                    'score' => rand(50, 2000), // Random scores between 50-2000
                    'level_reached' => rand(1, 10), // Random levels 1-10
                    'time_played_seconds' => rand(30, 600), // 30 seconds to 10 minutes
                    'game_data' => [
                        'final_level' => rand(1, 10),
                        'cards_matched' => rand(4, 20),
                        'total_cards' => rand(8, 24)
                    ],
                    'completed_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->command->info('Sample game scores created successfully!');
        $this->command->info('Total scores created: ' . GameScore::count());
    }
}
