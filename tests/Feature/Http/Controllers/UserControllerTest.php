<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateUser(): void
    {
        $response = $this->postJson('/api/user/create-user', [
            'name' => 'Bob',
        ]);

        $response->assertStatus(200);
    }

    public function testCreateOtherUser(): void
    {
        $response = $this->postJson('/api/admin/user/create-other-user', [
            'name' => 'Bob',
        ]);

        $response->assertStatus(200);
    }
}
