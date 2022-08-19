<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Lobby;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;

class UserJoinedLobby extends BroadcastEvent
{
    use Dispatchable;

    public string $lobbyId;
    public string $userId;
    public string $userName;

    public function __construct(Lobby $lobby, User $user)
    {
        $this->lobbyId = $lobby->getKey();
        $this->userId = $user->getKey();
        $this->userName = $user->getName();
    }

    public function broadcastOn(): Channel
    {
        // todo private/presence
        return new Channel('lobby.' . $this->lobbyId);
    }
}
