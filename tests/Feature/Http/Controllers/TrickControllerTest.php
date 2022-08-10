<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\AbstractFeatureTest;

class TrickControllerTest extends AbstractFeatureTest
{
    public function testPlayCard(): void
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
        $gameId = $gameResponse->json('uuid');

        $leadingPlayerIndex = null;
        $successfulResponses = [];

        while (count($successfulResponses) < $numPlayers) {
            foreach ($userIds as $playerIndex => $userId) {
                if (isset($successfulResponses[$userId])) {
                    continue;
                }

                $response = $this->postJson('/api/v1/admin/round/perform-bet-as-user', [
                    'auth_user_id' => $userId,
                    'game_id' => $gameResponse->json('uuid'),
                    'bet' => 1,
                ]);

                if ($response->status() === 200) {
                    $leadingPlayerIndex = $leadingPlayerIndex ?? $playerIndex;
                    $successfulResponses[$userId] = true;
                }
            }
        }

        $playerIndex = $leadingPlayerIndex;

        do {
            $card = $this->getCardFromHand($gameId, $userIds[$playerIndex]);

            $api = $playerIndex === 0 ? '/api/v1/trick/play-card' : '/api/v1/admin/trick/play-card-as-user';

            $response = $this->postJson($api, array_merge([
                'game_id' => $gameId,
                'card_suit' => $card['suit'],
                'card_value' => $card['value'],
            ], $playerIndex !== 0 ? ['auth_user_id' => $userIds[$playerIndex]] : []));

            $response->assertStatus(200);

            $playerIndex = $this->getNextPlayerIndex($numPlayers, $playerIndex);
        } while ($playerIndex !== $leadingPlayerIndex);
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

    protected function getCardFromHand(string $gameId, string $userId): array
    {
        $handResponse = $this->getJson(sprintf('/api/v1/admin/player/get-hand-as-user?game_id=%s&auth_user_id=%s', $gameId, $userId));

        return $handResponse->json()[0];
    }

    protected function getNextPlayerIndex(int $numPlayers, int $playerIndex): int
    {
        $playerIndex++;

        if ($playerIndex === $numPlayers) {
            $playerIndex = 0;
        }

        return $playerIndex;
    }
}
