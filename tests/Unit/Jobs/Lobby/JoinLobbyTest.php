<?php declare(strict_types=1);

namespace Tests\Unit\Jobs\Lobby;

use App\Events\UserJoinedLobby;
use App\Jobs\Lobby\JoinLobby;
use App\Jobs\Lobby\SetSessionCurrentLobby;
use App\Models\Lobby;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JoinLobbyTest extends TestCase
{
    use RefreshDatabase;

    public function testHandle(
        bool $setSession = false,
        ?string $expectException = null,
        ?string $inviteCode = null,
        ?User $user = null,
        ?Lobby $lobby = null,
    ): void
    {
        if (! $user) {
            /** @var User $user */
            $user = User::factory()->makeOne();
            $user->save();
        }

        if (! $lobby) {
            /** @var Lobby $lobby */
            $lobby = Lobby::factory()->makeOne();
            $lobby->save();
        }

        $subject = new JoinLobby($user->getKey(), $inviteCode ?? $lobby->getInviteCode(), $setSession);

        if ($setSession) {
            $this->expectsJobs(SetSessionCurrentLobby::class);
        }
        else {
            $this->doesntExpectJobs(SetSessionCurrentLobby::class);
        }

        if ($expectException) {
            $this->expectExceptionMessage($expectException);
            $this->doesntExpectEvents(UserJoinedLobby::class);
        }
        else {
            $this->expectsEvents(UserJoinedLobby::class);
        }

        $returnedLobby = $subject->handle();

        if (! $expectException) {
            $this->assertEquals($lobby->getKey(), $returnedLobby->getKey());
            $users = $returnedLobby->users()->get();
            $this->assertEquals(1, $users->count());
            $this->assertEquals($user->getKey(), $users->first()->getKey());
        }
    }

    public function testHandleSetSession(): void
    {
        $this->expectsJobs(SetSessionCurrentLobby::class);
        $this->testHandle(setSession: true);
    }

    public function testHandleLobbyNotFound(): void
    {
        $this->testHandle(expectException: 'No lobby found for that code', inviteCode: 'asdf123');
    }

    public function testHandleAlreadyInLobby(): void
    {
        /** @var User $user */
        $user = User::factory()->makeOne();
        $user->save();

        /** @var Lobby $lobby */
        $lobby = Lobby::factory()->makeOne();
        $lobby->save();

        $lobby->users()->attach($user->getKey());

        $this->testHandle(expectException: 'You are already in that lobby', user: $user, lobby: $lobby);
    }
}
