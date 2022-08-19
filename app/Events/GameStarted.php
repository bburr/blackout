<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Game;
use App\Models\Lobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;

class GameStarted extends BroadcastEvent
{
    use Dispatchable;

    public string $gameId;
    public string $lobbyId;

    public function __construct(Lobby $lobby, Game $game)
    {
        $this->lobbyId = $lobby->getKey();
        $this->gameId = $game->getKey();
    }

    public function broadcastOn(): Channel
    {
        // todo private/presence
        return new Channel('lobby.' . $this->lobbyId);
    }
}
