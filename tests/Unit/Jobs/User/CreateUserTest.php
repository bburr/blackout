<?php declare(strict_types=1);

namespace Tests\Unit\Jobs\User;

use App\Jobs\User\CreateUser;
use App\Jobs\User\StartUserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function testHandle(bool $startSession = false): void
    {
        $name = 'Name';
        $subject = new CreateUser($name, $startSession);

        $user = $subject->handle();

        $this->assertEquals($name, $user->getName());
    }

    public function testHandleStartSession(): void
    {
        $this->expectsJobs(StartUserSession::class);
        $this->testHandle(startSession: true);
    }
}
