<?php declare(strict_types=1);

namespace Tests\Unit\Jobs\User;

use App\Jobs\User\StartUserSession;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class StartUserSessionTest extends TestCase
{
    public function testHandle(): void
    {
        $user = User::factory()->makeOne();

        $subject = new StartUserSession($user);

        Session::shouldReceive('migrate');
        Session::shouldReceive('put')->with(User::CACHE_KEY_USER_ID, $user->getKey());

        $subject->handle();
    }
}
