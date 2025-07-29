<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GameScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'score',
        'level_reached',
        'time_played_seconds',
        'game_data',
        'completed_at'
    ];

    protected $casts = [
        'game_data' => 'array',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the score
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the game that this score belongs to
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Scope for leaderboard queries within time frames
     */
    public function scopeWithinTimeframe($query, $days = null)
    {
        if ($days) {
            return $query->where('created_at', '>=', Carbon::now()->subDays($days));
        }
        return $query;
    }

    /**
     * Scope for getting top scores
     */
    public function scopeTopScores($query, $limit = 10)
    {
        return $query->orderBy('score', 'desc')->limit($limit);
    }

    /**
     * Get leaderboard for a specific game and timeframe (best score per user only)
     */
    public static function getLeaderboard($gameId, $timeframe = null, $limit = 10)
    {
        $query = static::with(['user'])
            ->where('game_id', $gameId);

        // Apply timeframe filter
        switch ($timeframe) {
            case '30d':
                $query->withinTimeframe(30);
                break;
            case '60d':
                $query->withinTimeframe(60);
                break;
            case '90d':
                $query->withinTimeframe(90);
                break;
            case '1y':
                $query->withinTimeframe(365);
                break;
            default:
                // All time - no filter
                break;
        }

        // Get only the best score per user using subquery
        $subquery = static::selectRaw('user_id, MAX(score) as max_score')
            ->where('game_id', $gameId);

        // Apply same timeframe filter to subquery
        switch ($timeframe) {
            case '30d':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(30));
                break;
            case '60d':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(60));
                break;
            case '90d':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(90));
                break;
            case '1y':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(365));
                break;
        }

        $subquery->groupBy('user_id');

        // Join with the subquery to get only best scores per user
        return $query->joinSub($subquery, 'best_scores', function ($join) {
                $join->on('game_scores.user_id', '=', 'best_scores.user_id')
                     ->on('game_scores.score', '=', 'best_scores.max_score');
            })
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's best score for a game
     */
    public static function getUserBestScore($userId, $gameId)
    {
        return static::where('user_id', $userId)
            ->where('game_id', $gameId)
            ->orderBy('score', 'desc')
            ->first();
    }

    /**
     * Get user's rank for a specific game and timeframe (based on best scores per user)
     */
    public static function getUserRank($userId, $gameId, $timeframe = null)
    {
        $userBestScore = static::getUserBestScore($userId, $gameId);
        
        if (!$userBestScore) {
            return null;
        }

        // Build subquery to get best score per user
        $subquery = static::selectRaw('user_id, MAX(score) as max_score')
            ->where('game_id', $gameId)
            ->groupBy('user_id');

        // Apply timeframe filter to subquery
        switch ($timeframe) {
            case '30d':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(30));
                break;
            case '60d':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(60));
                break;
            case '90d':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(90));
                break;
            case '1y':
                $subquery->where('created_at', '>=', Carbon::now()->subDays(365));
                break;
        }

        // Count how many users have a better best score
        $betterScoresCount = \DB::table(\DB::raw("({$subquery->toSql()}) as best_scores"))
            ->mergeBindings($subquery->getQuery())
            ->where('max_score', '>', $userBestScore->score)
            ->count();

        return $betterScoresCount + 1;
    }
}
