<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\AbstractFeatureTest;

class RoundControllerTest extends AbstractFeatureTest
{
    public function testPerformBet(): void
    {
        $numPlayers = 3;

        $userIds = [];
        $userIds[] = $this->postJson('/api/v1/user/create-user', ['name' => 'Bob'])->json('uuid');

        $lobbyResponse = $this->postJson('/api/v1/lobby/create-lobby');
        $lobbyResponse->assertStatus(200);
        $lobbyId = $lobbyResponse->json('uuid');

        for ($i = 0; $i < $numPlayers - 1; $i++) {
            $userIds[] = $this->addUserToLobby($lobbyId);
        }

        $gameResponse = $this->postJson('/api/v1/game/start-game');

        $successfulResponses = [];

        while (count($successfulResponses) < $numPlayers) {
            foreach ($userIds as $userId) {
                if (isset($successfulResponses[$userId])) {
                    continue;
                }

                $response = $this->postJson('/api/v1/admin/round/perform-bet-as-user', [
                    'auth_user_id' => $userId,
                    'game_id' => $gameResponse->json('uuid'),
                    'bet' => 1,
                ]);

                if ($response->status() === 200) {
                    $successfulResponses[$userId] = true;
                }
            }
        }

        $response = $this->postJson('/api/v1/round/perform-bet', [
            'game_id' => $gameResponse->json('uuid'),
            'bet' => 1,
        ]);

        $response->assertStatus(400);
    }

    protected function addUserToLobby(string $lobbyId): string
    {
        $userResponse = $this->postJson('/api/v1/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $userId = $userResponse->json(('uuid'));

        $this->postJson('api/v1/admin/lobby/add-user-to-lobby', [
            'lobby_id' => $lobbyId,
            'user_id' => $userId,
        ]);

        return $userId;
    }
}
