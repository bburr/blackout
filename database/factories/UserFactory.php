<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->firstName,
        ];
    }
}
