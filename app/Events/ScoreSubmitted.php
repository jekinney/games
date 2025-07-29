<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameSlug;
    public $userId;
    public $userName;
    public $score;
    public $rank;
    public $gameData;

    /**
     * Create a new event instance.
     */
    public function __construct($gameSlug, $userId, $userName, $score, $rank, $gameData = null)
    {
        $this->gameSlug = $gameSlug;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->score = $score;
        $this->rank = $rank;
        $this->gameData = $gameData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("game.{$this->gameSlug}.leaderboard"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'score.submitted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'score' => $this->score,
            'rank' => $this->rank,
            'game_data' => $this->gameData,
            'timestamp' => now()->toISOString(),
        ];
    }
}
