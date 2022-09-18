<?php declare(strict_types=1);

namespace Tests\Unit\Jobs\Lobby;

use App\Jobs\Lobby\CreateLobby;
use App\Jobs\Lobby\SetSessionCurrentLobby;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateLobbyTest extends TestCase
{
    use RefreshDatabase;

    public function testHandle(bool $setSession = false): void
    {
        /** @var User $user */
        $user = User::factory()->makeOne();
        $user->save();
        $subject = new CreateLobby($user->getKey(), $setSession);

        $lobby = $subject->handle();

        $users = $lobby->users()->get();
        $this->assertEquals(1, $users->count());
        $this->assertEquals($user->getKey(), $users->first()->getKey());
        $this->assertEquals(1, $users->first()->pivot->is_owner);
    }

    public function testHandleSetSession(): void
    {
        $this->expectsJobs(SetSessionCurrentLobby::class);
        $this->testHandle(setSession: true);
    }
}
