<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\AbstractFeatureTest;

class PlayerControllerTest extends AbstractFeatureTest
{
    public function testGetHand(): void
    {
        $numPlayers = 3;

        $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);

        $lobbyResponse = $this->postJson('/api/v1/lobby/create-lobby');
        $lobbyId = $lobbyResponse->json('uuid');

        for ($i = 0; $i < $numPlayers - 1; $i++) {
            $this->addUserToLobby($lobbyId);
        }

        $response = $this->postJson('/api/v1/game/start-game');
        $gameId = $response->json('uuid');

        $response = $this->getJson('/api/v1/player/get-hand?game_id=' . $gameId);
        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    public function testGetHandAsUser(): void
    {
        $numPlayers = 3;

        $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);

        $lobbyResponse = $this->postJson('/api/v1/lobby/create-lobby');
        $lobbyId = $lobbyResponse->json('uuid');

        for ($i = 0; $i < $numPlayers - 1; $i++) {
            $userId = $this->addUserToLobby($lobbyId);
        }

        $response = $this->postJson('/api/v1/game/start-game');
        $gameId = $response->json('uuid');

        $response = $this->getJson(sprintf('/api/v1/admin/player/get-hand-as-user?game_id=%s&auth_user_id=%s', $gameId, $userId));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    protected function addUserToLobby(string $lobbyId): string
    {
        $userResponse = $this->postJson('/api/v1/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $this->postJson('api/v1/admin/lobby/add-user-to-lobby', [
            'lobby_id' => $lobbyId,
            'user_id' => $userResponse->json('uuid'),
        ]);

        return $userResponse->json('uuid');
    }
}
