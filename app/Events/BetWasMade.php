<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;

class BetWasMade extends BroadcastEvent
{
    use Dispatchable;

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
