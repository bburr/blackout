<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;

class CardWasPlayed extends BroadcastEvent
{
    public string $gameId;

    public function __construct(Game $game)
    {
        $this->gameId = $game->getKey();
    }

    public function broadcastOn()
    {
        // todo private/presence
        return new Channel('game.' . $this->gameId);
    }
}
