<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'provider',
        'provider_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all game scores for this user
     */
    public function gameScores()
    {
        return $this->hasMany(GameScore::class);
    }

    /**
     * Get the user's best score for a specific game
     */
    public function getBestScoreForGame($gameId)
    {
        return $this->gameScores()
            ->where('game_id', $gameId)
            ->orderBy('score', 'desc')
            ->first();
    }

    /**
     * Get user's rank for a specific game
     */
    public function getRankForGame($gameId, $timeframe = null)
    {
        return GameScore::getUserRank($this->id, $gameId, $timeframe);
    }
}
