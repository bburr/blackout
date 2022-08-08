<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\AbstractFeatureTest;

class GameControllerTest extends AbstractFeatureTest
{
    public function testStartGame(): void
    {
        $numPlayers = 3;

        $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);

        $lobbyResponse = $this->postJson('/api/v1/lobby/create-lobby');
        $lobbyId = $lobbyResponse->json('uuid');

        for ($i = 0; $i < $numPlayers - 1; $i++) {
            $this->addUserToLobby($lobbyId);
        }

        $response = $this->postJson('/api/v1/game/start-game');

        $response->assertStatus(200);
    }

    protected function addUserToLobby(string $lobbyId): void
    {
        $userResponse = $this->postJson('/api/v1/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $this->postJson('api/v1/admin/lobby/add-user-to-lobby', [
            'lobby_id' => $lobbyId,
            'user_id' => $userResponse->json('uuid'),
        ]);
    }
}
