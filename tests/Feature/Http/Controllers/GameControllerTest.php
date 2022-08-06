<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStartGame(): void
    {
        $numPlayers = 3;

        $this->postJson('/api/user/create-user', ['name' => 'Bob']);

        $lobbyResponse = $this->postJson('/api/lobby/create-lobby');
        $lobbyId = $lobbyResponse->json()['uuid'];

        for ($i = 0; $i < $numPlayers - 1; $i++) {
            $this->addUserToLobby($lobbyId);
        }

        $response = $this->postJson('/api/game/start-game');

        $response->assertStatus(200);
    }

    protected function addUserToLobby(string $lobbyId): void
    {
        $userResponse = $this->postJson('/api/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $this->postJson('api/admin/lobby/add-user-to-lobby', [
            'lobby_id' => $lobbyId,
            'user_id' => $userResponse->json()['uuid'],
        ]);
    }
}
