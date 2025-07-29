<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameSlug;
    public $userId;
    public $userName;
    public $sessionId;
    public $score;
    public $timePlayed;

    /**
     * Create a new event instance.
     */
    public function __construct($gameSlug, $userId = null, $userName = null, $sessionId = null, $score = null, $timePlayed = null)
    {
        $this->gameSlug = $gameSlug;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->sessionId = $sessionId;
        $this->score = $score;
        $this->timePlayed = $timePlayed;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("game.{$this->gameSlug}.players"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'game.ended';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'session_id' => $this->sessionId,
            'score' => $this->score,
            'time_played' => $this->timePlayed,
            'timestamp' => now()->toISOString(),
        ];
    }
}
