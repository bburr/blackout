<?php declare(strict_types=1);

namespace Tests\Unit\Jobs\Lobby;

use App\Jobs\Lobby\SetSessionCurrentLobby;
use App\Models\Lobby;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SetSessionCurrentLobbyTest extends TestCase
{
    public function testHandle(): void
    {
        $lobby = Lobby::factory()->makeOne();

        $subject = new SetSessionCurrentLobby($lobby);

        Session::shouldReceive('put')->with(Lobby::CACHE_KEY_CURRENT_LOBBY_ID, $lobby->getKey());

        $subject->handle();
    }
}
