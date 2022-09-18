<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Lobby;
use Tests\Feature\AbstractFeatureTest;

class LobbyControllerTest extends AbstractFeatureTest
{
    public function testCreateLobby(): void
    {
        $userResposne = $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);

        $response = $this->postJson('/api/v1/lobby/create-lobby');

        $response->assertStatus(200);
        $response->assertSessionHas(Lobby::CACHE_KEY_CURRENT_LOBBY_ID);
        $this->assertDatabaseHas(Lobby::class, ['uuid' => $response->json('uuid')]);
        $this->assertDatabaseHas('lobby_user', [
            'lobby_uuid' => $response->json('uuid'),
            'user_uuid' => $userResposne->json('uuid'),
            'is_owner' => 1,
        ]);
    }

    public function testJoinLobby(): void
    {
        $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);
        $lobbyResponse = $this->postJson('/api/v1/lobby/create-lobby');

        $this->postJson('/api/v1/user/create-user', ['name' => 'Jim']);

        $response = $this->postJson('/api/v1/lobby/join-lobby', [
            'invite_code' => $lobbyResponse->json('invite_code'),
        ]);

        $response->assertStatus(200);
    }

    public function testAddUserToLobby(): void
    {
        $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);

        $lobbyResponse = $this->postJson('/api/v1/lobby/create-lobby');
        $userResponse = $this->postJson('/api/v1/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $response = $this->postJson('api/v1/admin/lobby/add-user-to-lobby', [
            'lobby_id' => $lobbyResponse->json('uuid'),
            'user_id' => $userResponse->json('uuid'),
        ]);

        $response->assertStatus(200);
    }

    public function testAddUserToLobbyInviteCode(): void
    {
        $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);

        $lobbyResponse = $this->postJson('/api/v1/lobby/create-lobby');
        $userResponse = $this->postJson('/api/v1/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $response = $this->postJson('api/v1/admin/lobby/add-user-to-lobby', [
            'invite_code' => $lobbyResponse->json('invite_code'),
            'user_id' => $userResponse->json('uuid'),
        ]);

        $response->assertStatus(200);
    }
}
