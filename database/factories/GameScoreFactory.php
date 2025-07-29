<?php

namespace Database\Factories;

use App\Models\GameScore;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameScore>
 */
class GameScoreFactory extends Factory
{
    protected $model = GameScore::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        
        return [
            'user_id' => $user->id,
            'game_id' => Game::factory()->create()->id,
            'score' => fake()->numberBetween(100, 5000),
            'level_reached' => fake()->numberBetween(1, 15),
            'time_played_seconds' => fake()->numberBetween(30, 600), // 30 seconds to 10 minutes
            'game_data' => [
                'moves' => fake()->numberBetween(20, 100),
                'perfect_matches' => fake()->numberBetween(0, 10),
                'hints_used' => fake()->numberBetween(0, 3),
                'cards_matched' => fake()->numberBetween(6, 24),
                'total_cards' => fake()->randomElement([12, 16, 20, 24]),
                'difficulty_multiplier' => fake()->randomFloat(2, 1.0, 2.5),
                'bonus_points' => fake()->numberBetween(0, 500),
            ],
            'completed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the score is for an anonymous user.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'user_name' => 'Anonymous',
        ]);
    }

    /**
     * Indicate that the score is a high score.
     */
    public function highScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(3000, 10000),
            'level_reached' => fake()->numberBetween(10, 20),
        ]);
    }
}
