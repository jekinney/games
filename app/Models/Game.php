<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'how_to_play',
        'image_url',
        'thumbnail_url',
        'game_file_url',
        'category',
        'min_players',
        'max_players',
        'estimated_play_time',
        'difficulty',
        'is_active',
        'is_featured',
        'play_count',
        'average_rating',
        'tags',
        'controls',
        'developer_notes',
    ];

    protected $casts = [
        'tags' => 'array',
        'controls' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'average_rating' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name
        static::creating(function ($game) {
            if (empty($game->slug)) {
                $game->slug = Str::slug($game->name);
            }
        });

        static::updating(function ($game) {
            if ($game->isDirty('name') && empty($game->slug)) {
                $game->slug = Str::slug($game->name);
            }
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Accessors
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getDifficultyBadgeColorAttribute()
    {
        return match($this->difficulty) {
            'easy' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'hard' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    public function getCategoryBadgeColorAttribute()
    {
        return match($this->category) {
            'action' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'puzzle' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'strategy' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'arcade' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'simulation' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'sports' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    // Helper methods
    public function incrementPlayCount()
    {
        $this->increment('play_count');
    }

    public function updateRating($newRating)
    {
        // This would typically involve a ratings table, but for simplicity we'll just update directly
        $this->update(['average_rating' => $newRating]);
    }

    /**
     * Get games formatted for public display
     */
    public static function getPublicGames()
    {
        return static::where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function ($game) {
                // Map database slugs to actual file names
                $fileMapping = [
                    'memory-test' => 'memory-test-game.html',
                    // Add more mappings as needed when more games are created
                ];
                
                $filename = $fileMapping[$game->slug] ?? $game->slug . '.html';
                
                // Only include games that have actual files
                if (!file_exists(public_path("games/{$filename}"))) {
                    return null;
                }
                
                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'description' => $game->description,
                    'image' => $game->image_url ?: '/images/games/default-game.png',
                    'thumbnail' => $game->thumbnail_url ?: '/images/games/thumbnails/default.png',
                    'url' => '/games/' . $filename,
                    'category' => ucfirst($game->category),
                    'difficulty' => ucfirst($game->difficulty),
                    'playerCount' => $game->min_players === $game->max_players 
                        ? $game->min_players . ' Player' . ($game->min_players > 1 ? 's' : '')
                        : $game->min_players . '-' . $game->max_players . ' Players',
                    'isFeatured' => $game->is_featured,
                    'playCount' => $game->play_count,
                    'averageRating' => $game->average_rating,
                    'estimatedPlayTime' => $game->estimated_play_time,
                    'tags' => $game->tags,
                ];
            })
            ->filter() // Remove null values
            ->values(); // Reset array keys
    }

    // Relationships
    public function scores()
    {
        return $this->hasMany(GameScore::class);
    }

    public function topScores($limit = 10)
    {
        return $this->scores()
            ->with('user')
            ->orderBy('score', 'desc')
            ->limit($limit);
    }

    public function getLeaderboard($timeframe = null, $limit = 10)
    {
        return GameScore::getLeaderboard($this->id, $timeframe, $limit);
    }
}
