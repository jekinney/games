<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->slug(),
            'description' => fake()->paragraph(),
            'how_to_play' => fake()->paragraph(),
            'image_url' => '/images/games/default-game.png',
            'thumbnail_url' => '/images/games/default-game.png',
            'game_file_url' => '/games/' . fake()->slug() . '.js',
            'category' => fake()->randomElement(['puzzle', 'arcade', 'strategy', 'action', 'adventure']),
            'min_players' => 1,
            'max_players' => fake()->randomElement([1, 2, 4]),
            'estimated_play_time' => fake()->numberBetween(5, 60),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
            'play_count' => fake()->numberBetween(0, 1000),
            'average_rating' => fake()->randomFloat(2, 0, 5),
            'tags' => fake()->words(3),
            'controls' => ['keyboard', 'mouse'],
            'developer_notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the game is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the game is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
