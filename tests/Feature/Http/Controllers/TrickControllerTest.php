<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\AbstractFeatureTest;

class TrickControllerTest extends AbstractFeatureTest
{
    public function testPlayCard(): void
    {
        $numPlayers = 3;

        $userIds = [];
        $userIds[] = $this->postJson('/api/user/create-user', ['name' => 'Bob'])->json('uuid');

        $lobbyResponse = $this->postJson('/api/lobby/create-lobby');
        $lobbyResponse->assertStatus(200);
        $lobbyId = $lobbyResponse->json('uuid');

        for ($i = 0; $i < $numPlayers - 1; $i++) {
            $userIds[] = $this->addUserToLobby($lobbyId);
        }

        $gameResponse = $this->postJson('/api/game/start-game');

        $successfulResponses = [];

        while (count($successfulResponses) < $numPlayers) {
            foreach ($userIds as $userId) {
                if (isset($successfulResponses[$userId])) {
                    continue;
                }

                $response = $this->postJson('/api/admin/round/perform-bet-as-user', [
                    'auth_user_id' => $userId,
                    'game_id' => $gameResponse->json('uuid'),
                    'bet' => 1,
                ]);

                if ($response->status() === 200) {
                    $successfulResponses[$userId] = true;
                }
            }
        }


    }

    protected function addUserToLobby(string $lobbyId): string
    {
        $userResponse = $this->postJson('/api/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $userId = $userResponse->json(('uuid'));

        $this->postJson('api/admin/lobby/add-user-to-lobby', [
            'lobby_id' => $lobbyId,
            'user_id' => $userId,
        ]);

        return $userId;
    }
}
