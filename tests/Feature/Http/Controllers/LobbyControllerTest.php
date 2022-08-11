<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\Feature\AbstractFeatureTest;

class LobbyControllerTest extends AbstractFeatureTest
{
    public function testCreateLobby(): void
    {
        $this->postJson('/api/v1/user/create-user', ['name' => 'Bob']);

        $response = $this->postJson('/api/v1/lobby/create-lobby');

        $response->assertStatus(200);
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
