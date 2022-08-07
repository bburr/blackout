<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\AbstractFeatureTest;

class LobbyControllerTest extends AbstractFeatureTest
{
    public function testCreateLobby(): void
    {
        $this->postJson('/api/user/create-user', ['name' => 'Bob']);

        $response = $this->postJson('/api/lobby/create-lobby');

        $response->assertStatus(200);
    }

    public function testJoinLobby(): void
    {
        $this->postJson('/api/user/create-user', ['name' => 'Bob']);
        $lobbyResponse = $this->postJson('/api/lobby/create-lobby');

        $this->postJson('/api/user/create-user', ['name' => 'Jim']);

        $response = $this->postJson('/api/lobby/join-lobby', [
            'invite_code' => $lobbyResponse->json('invite_code'),
        ]);

        $response->assertStatus(200);
    }

    // todo test with invite code
    public function testAddUserToLobby(): void
    {
        $this->postJson('/api/user/create-user', ['name' => 'Bob']);

        $lobbyResponse = $this->postJson('/api/lobby/create-lobby');
        $userResponse = $this->postJson('/api/admin/user/create-other-user', [
            'name' => 'Jim',
        ]);

        $response = $this->postJson('api/admin/lobby/add-user-to-lobby', [
            'lobby_id' => $lobbyResponse->json('uuid'),
            'user_id' => $userResponse->json('uuid'),
        ]);

        $response->assertStatus(200);
    }
}
