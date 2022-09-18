<?php declare(strict_types=1);

namespace App\Jobs\Lobby;

use App\Models\Lobby;
use Illuminate\Support\Facades\Session;

class SetSessionCurrentLobby
{
    public function __construct(protected Lobby $lobby)
    {
    }

    public function handle(): void
    {
        Session::put(Lobby::CACHE_KEY_CURRENT_LOBBY_ID, $this->lobby->getKey());
    }
}
