<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\Feature\AbstractFeatureTest;

class UserControllerTest extends AbstractFeatureTest
{
    public function testCreateUser(): void
    {
        $userData = [
            'name' => Str::uuid(),
        ];
        $response = $this->postJson('/api/v1/user/create-user', $userData);

        $response->assertStatus(200);
        $response->assertSessionHas(User::CACHE_KEY_USER_ID);
        $this->assertDatabaseHas(User::class, $userData);
    }

    public function testCreateOtherUser(): void
    {
        $userData = [
            'name' => Str::uuid(),
        ];

        $response = $this->postJson('/api/v1/admin/user/create-other-user', $userData);

        $response->assertStatus(200);
        $this->assertDatabaseHas(User::class, $userData);
    }
}
