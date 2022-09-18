<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lobby;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lobby>
 */
class LobbyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
        ];
    }
}
